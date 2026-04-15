<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('telescope:prune')->daily();
Schedule::command('activitylog:clean')->daily();
Schedule::command('reminder:send')->dailyAt('06:00');
Schedule::command('dealer:send')->dailyAt('06:30');
