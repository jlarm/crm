<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Ai\Agents\DealershipAssistant;
use App\Models\Dealership;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AiChatController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'conversation_id' => ['nullable', 'string', 'size:36'],
            'dealership_id' => ['nullable', 'integer', 'exists:dealerships,id'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $dealership = isset($data['dealership_id'])
            ? Dealership::query()->forUser($user)->whereKey($data['dealership_id'])->first()
            : null;

        $agent = new DealershipAssistant($dealership, $user);

        $response = isset($data['conversation_id'])
            ? $agent->continue($data['conversation_id'], as: $user)->prompt($data['message'])
            : $agent->forUser($user)->prompt($data['message']);

        return response()->json([
            'conversation_id' => $response->conversationId,
            'reply' => $response->text,
        ]);
    }
}
