<?php

declare(strict_types=1);

namespace App\Actions\Emails;

use App\Enum\EmailDirection;
use App\Models\DealershipEmail;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Email threads a rep started where the contact wrote last, so the rep owes a reply.
 */
final class ListRepliesAwaitingResponse
{
    /**
     * The newest reply in each such thread, newest first, plus how many threads are waiting.
     *
     * @return array{total: int, items: list<array{id: int, threadId: int, dealership: array{id: int, name: string}, from: string, subject: string, snippet: string, receivedAt: string, unread: bool}>}
     */
    public function __invoke(User $user, int $limit = 6): array
    {
        $query = DealershipEmail::query()
            ->from('dealership_emails as reply')
            ->where('reply.direction', EmailDirection::Inbound)
            ->where('reply.user_id', $user->id)
            // Nothing newer in the same thread: no answer from the rep and no later reply.
            ->whereNotExists(fn ($later) => $later
                ->selectRaw('1')
                ->from('dealership_emails as later')
                ->whereColumn('later.id', '>', 'reply.id')
                ->where(fn ($thread) => $thread
                    ->whereColumn('later.parent_id', 'reply.parent_id')
                    ->orWhereColumn('later.id', 'reply.parent_id')));

        $total = (clone $query)->count();

        $items = $query
            ->select('reply.*')
            ->with(['dealership:id,name', 'contact:id,name'])
            ->orderByDesc('reply.created_at')
            ->orderByDesc('reply.id')
            ->limit($limit)
            ->get()
            ->map(fn (DealershipEmail $reply): array => [
                'id' => $reply->id,
                'threadId' => $reply->parent_id ?? $reply->id,
                'dealership' => ['id' => $reply->dealership->id, 'name' => (string) $reply->dealership->name],
                'from' => $reply->contact->name ?? $reply->from_name ?? $reply->from_email,
                'subject' => $reply->subject,
                'snippet' => Str::limit((string) preg_replace('/\s+/u', ' ', $reply->body_text), 140),
                'receivedAt' => $reply->created_at->toIso8601String(),
                'unread' => $reply->read_at === null,
            ])
            ->all();

        return ['total' => $total, 'items' => $items];
    }
}
