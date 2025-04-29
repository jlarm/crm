<?php

namespace App\Console\Commands;

use App\Models\Dealership;
use App\Models\User;
use Illuminate\Console\Command;

class AssignUserToDealershipsByStateCommand extends Command
{
    protected $signature = 'user:assign-dealerships {userId} {states*} {--dry-run}';

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
                $this->line("Would assign user ID {$user->id} to dealership ID {$dealership->id} ({$dealership->name})");
            } else {
                $user->dealerships()->syncWithoutDetaching([$dealership->id]);
                $this->line("Assigned user ID {$user->id} to dealership ID {$dealership->id} ({$dealership->name})");
            }
        }

        $this->info($dryRun ? 'Dry run complete. No changes made.' : 'User assignment complete.');
    }
}
