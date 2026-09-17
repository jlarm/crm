<?php

declare(strict_types=1);

namespace App\Actions\Tasks;

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Models\Dealership;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class BuildTaskFormOptions
{
    /**
     * @return array{allUsers: Collection<int, User>,
     *     allDealerships: Collection<int, Dealership>,
     *     types: array<int, array{value: string, label: string}>,
     *     priorities: array<int, array{value: string, label: string}>}
     */
    public function __invoke(): array
    {
        return [
            'allUsers' => User::query()->select('id', 'name')->orderBy('name')->get(),
            'allDealerships' => Dealership::query()
                ->select('id', 'name')
                ->whereNot('status', 'imported')
                ->orderBy('name')
                ->get(),
            'types' => TaskType::options(),
            'priorities' => TaskPriority::options(),
        ];
    }
}
