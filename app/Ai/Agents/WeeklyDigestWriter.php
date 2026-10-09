<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

/**
 * Writes the short "your week" paragraph at the top of the dashboard digest.
 */
#[Timeout(60)]
class WeeklyDigestWriter implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'TXT'
You write a short weekly recap for a sales rep who sells to dealerships. You are given the facts of their last seven days as JSON. Write two or three plain sentences, addressed to the rep as "you":
- what moved (name the deals and dealerships that matter most),
- what is slipping or going quiet,
- one specific thing to focus on next.

Use only the facts given. Don't invent numbers, names, or reasons. No greeting, no headings, no bullet points, no markdown. If very little happened, say so briefly and point to the most useful next step.
TXT;
    }
}
