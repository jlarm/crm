<?php

namespace App\Console\Commands;

use App\Models\Dealership;
use App\Models\User;
use Illuminate\Console\Command;

class RemoveUserFromDealershipsByStateCommand extends Command
{
    protected $signature = 'user:remove-dealerships {userId} {state} {--dry-run}';

    protected $description = 'Command description';

    public function handle(): void
    {
        $userId = $this->argument('userId');
        $states = $this->argument('states');
        $dryRun = $this->option('dry-run');

        $user = User::find($userId);

        if (!$user) {
            $this->error('User not found');
            return;
        }

        $dealerships = Dealership::whereIn('state', $states)->get();

        if ($dealerships->isEmpty()) {
            $this->error('No dealerships found for the given states');
            return;
        }

        foreach ($dealerships as $dealership) {
            if ($dryRun) {
                $this->line("Would remove user ID {$user->id} from dealership ID {$dealership->id} ({$dealership->name})");
            } else {
                $user->dealerships()->detach($dealership->id);
                $this->line("Removed user ID {$user->id} from dealership ID {$dealership->id} ({$dealership->name})");
            }
        }

        $this->info($dryRun ? 'Dry run complete. No changes made.' : 'User removal complete.');
    }
}
