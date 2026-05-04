<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DealershipResource;
use App\Models\Dealership;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DealershipController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $dealerships = Dealership::query()
            ->with(['stores', 'contacts'])
            ->orderBy('name')
            ->paginate(50);

        return DealershipResource::collection($dealerships);
    }
}
