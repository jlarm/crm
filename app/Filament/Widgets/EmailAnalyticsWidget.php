<?php

namespace App\Filament\Widgets;

use App\Models\EmailTrackingEvent;
use App\Models\SentEmail;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class EmailAnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Email Performance Analytics';

    protected function getStats(): array
    {
        $user = auth()->user();

        // Get email stats for the current user
        $totalSent = SentEmail::whereHas('dealership.users', fn($q) => $q->where('user_id', $user->id))
            ->count();

        $totalOpened = SentEmail::whereHas('dealership.users', fn($q) => $q->where('user_id', $user->id))
            ->whereHas('trackingEvents', fn($q) => $q->where('event_type', EmailTrackingEvent::EVENT_OPENED))
            ->distinct()
            ->count();

        $totalClicked = SentEmail::whereHas('dealership.users', fn($q) => $q->where('user_id', $user->id))
            ->whereHas('trackingEvents', fn($q) => $q->where('event_type', EmailTrackingEvent::EVENT_CLICKED))
            ->distinct()
            ->count();

        $totalBounced = SentEmail::whereHas('dealership.users', fn($q) => $q->where('user_id', $user->id))
            ->whereHas('trackingEvents', fn($q) => $q->where('event_type', EmailTrackingEvent::EVENT_BOUNCED))
            ->distinct()
            ->count();

        // Calculate rates
        $openRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;
        $bounceRate = $totalSent > 0 ? round(($totalBounced / $totalSent) * 100, 1) : 0;

        // Get this month's stats
        $thisMonthSent = SentEmail::whereHas('dealership.users', fn($q) => $q->where('user_id', $user->id))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthSent = SentEmail::whereHas('dealership.users', fn($q) => $q->where('user_id', $user->id))
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $monthlyTrend = $lastMonthSent > 0
            ? round((($thisMonthSent - $lastMonthSent) / $lastMonthSent) * 100, 1)
            : ($thisMonthSent > 0 ? 100 : 0);

        return [
            Stat::make('Emails Sent', $totalSent)
                ->description($thisMonthSent . ' this month')
                ->descriptionIcon($monthlyTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('primary'),

            Stat::make('Open Rate', $openRate . '%')
                ->description($totalOpened . ' of ' . $totalSent . ' emails opened')
                ->descriptionIcon('heroicon-m-envelope-open')
                ->color($openRate >= 20 ? 'success' : ($openRate >= 10 ? 'warning' : 'danger')),

            Stat::make('Click Rate', $clickRate . '%')
                ->description($totalClicked . ' of ' . $totalSent . ' emails clicked')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color($clickRate >= 3 ? 'success' : ($clickRate >= 1 ? 'warning' : 'danger')),

            Stat::make('Bounce Rate', $bounceRate . '%')
                ->description($totalBounced . ' of ' . $totalSent . ' emails bounced')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($bounceRate <= 2 ? 'success' : ($bounceRate <= 5 ? 'warning' : 'danger')),

            Stat::make('Engagement Score', $this->calculateEngagementScore($openRate, $clickRate, $bounceRate))
                ->description('Overall email performance')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color($this->getEngagementColor($openRate, $clickRate, $bounceRate)),

            Stat::make('Recent Activity', $this->getRecentActivityCount($user->id))
                ->description('Events in last 24 hours')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }

    private function calculateEngagementScore(float $openRate, float $clickRate, float $bounceRate): string
    {
        // Simple engagement scoring algorithm
        $score = ($openRate * 0.4) + ($clickRate * 2) - ($bounceRate * 0.5);
        $score = max(0, min(100, $score)); // Clamp between 0-100

        if ($score >= 75) return 'Excellent';
        if ($score >= 60) return 'Good';
        if ($score >= 40) return 'Average';
        if ($score >= 20) return 'Poor';
        return 'Very Poor';
    }

    private function getEngagementColor(float $openRate, float $clickRate, float $bounceRate): string
    {
        $score = ($openRate * 0.4) + ($clickRate * 2) - ($bounceRate * 0.5);
        $score = max(0, min(100, $score));

        if ($score >= 60) return 'success';
        if ($score >= 40) return 'warning';
        return 'danger';
    }

    private function getRecentActivityCount(int $userId): int
    {
        return EmailTrackingEvent::whereHas('sentEmail.dealership.users', fn($q) => $q->where('user_id', $userId))
            ->where('event_timestamp', '>=', now()->subDay())
            ->count();
    }
}