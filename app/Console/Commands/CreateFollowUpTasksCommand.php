<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Opportunities\CreateFollowUpTasksForQuietDeals;
use Illuminate\Console\Command;

class CreateFollowUpTasksCommand extends Command
{
    protected $signature = 'opportunities:follow-up-tasks {--days=14 : Days without activity before a deal gets a follow-up task}';

    protected $description = 'Create follow-up tasks for open deals with no recent activity';

    public function handle(CreateFollowUpTasksForQuietDeals $createFollowUpTasks): int
    {
        $created = $createFollowUpTasks((int) $this->option('days'));

        $this->info(sprintf('Created %d follow-up task(s).', $created));

        return self::SUCCESS;
    }
}
