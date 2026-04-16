<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\OpportunityStage;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class SalesDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('SalesDashboard/Index', [
            'kpis' => $this->buildKpis(),
            'pipelineByStage' => $this->buildPipelineByStage(),
            'repPerformance' => $this->buildRepPerformance(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildKpis(): array
    {
        $dateDiffExpr = DB::connection()->getDriverName() === 'sqlite'
            ? 'julianday(closed_at) - julianday(created_at)'
            : 'DATEDIFF(closed_at, created_at)';

        $totals = Opportunity::query()
            ->selectRaw('
                SUM(CASE WHEN stage NOT IN (?, ?) THEN COALESCE(estimated_value, 0) ELSE 0 END) as pipeline_value,
                SUM(CASE WHEN stage NOT IN (?, ?) THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN stage = ? THEN 1 ELSE 0 END) as won_count,
                SUM(CASE WHEN stage IN (?, ?) THEN 1 ELSE 0 END) as closed_count,
                AVG(CASE WHEN stage = ? AND closed_at IS NOT NULL THEN '.$dateDiffExpr.' END) as avg_days,
                AVG(CASE WHEN stage = ? THEN actual_value END) as avg_deal_size
            ', [
                OpportunityStage::Won->value, OpportunityStage::Lost->value,
                OpportunityStage::Won->value, OpportunityStage::Lost->value,
                OpportunityStage::Won->value,
                OpportunityStage::Won->value, OpportunityStage::Lost->value,
                OpportunityStage::Won->value,
                OpportunityStage::Won->value,
            ])
            ->first();

        $closingThisMonth = Opportunity::closingThisMonth()
            ->selectRaw('COUNT(*) as count, SUM(COALESCE(estimated_value, 0)) as value')
            ->first();

        $wonLastMonth = Opportunity::wonLastMonth()->count();

        $closedCount = (int) ($totals->closed_count ?? 0);
        $wonCount = (int) ($totals->won_count ?? 0);

        return [
            'pipelineValue' => (float) ($totals->pipeline_value ?? 0),
            'openCount' => (int) ($totals->open_count ?? 0),
            'wonCount' => $wonCount,
            'closedCount' => $closedCount,
            'winRate' => $closedCount > 0 ? round(($wonCount / $closedCount) * 100) : 0,
            'avgDealSize' => (float) ($totals->avg_deal_size ?? 0),
            'avgDaysToClose' => (int) round((float) ($totals->avg_days ?? 0)),
            'closingThisMonthCount' => (int) ($closingThisMonth->count ?? 0),
            'closingThisMonthValue' => (float) ($closingThisMonth->value ?? 0),
            'wonLastMonthCount' => $wonLastMonth,
            'lastMonthLabel' => now()->subMonth()->format('M Y'),
        ];
    }

    /**
     * @return list<array{stage: string, label: string, count: int, value: float}>
     */
    private function buildPipelineByStage(): array
    {
        $rows = Opportunity::query()
            ->select('stage')
            ->selectRaw('COUNT(*) as count, SUM(COALESCE(estimated_value, 0)) as value')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        return array_map(function (OpportunityStage $stage) use ($rows): array {
            $row = $rows->get($stage->value);

            return [
                'stage' => $stage->value,
                'label' => $stage->getLabel(),
                'color' => $stage->getColor(),
                'count' => (int) ($row?->count ?? 0),
                'value' => (float) ($row?->value ?? 0),
            ];
        }, OpportunityStage::cases());
    }

    /**
     * @return list<array{name: string, total: int, won: int, lost: int, pipeline: float, winRate: int}>
     */
    private function buildRepPerformance(): array
    {
        return User::query()
            ->select(['users.id', 'users.name'])
            ->selectRaw('COUNT(o.id) as total_deals')
            ->selectRaw('SUM(CASE WHEN o.stage = ? THEN 1 ELSE 0 END) as won_count', [OpportunityStage::Won->value])
            ->selectRaw('SUM(CASE WHEN o.stage = ? THEN 1 ELSE 0 END) as lost_count', [OpportunityStage::Lost->value])
            ->selectRaw(
                'SUM(CASE WHEN o.stage NOT IN (?, ?) THEN COALESCE(o.estimated_value, 0) ELSE 0 END) as open_pipeline',
                [OpportunityStage::Won->value, OpportunityStage::Lost->value]
            )
            ->join('dealerships as d', 'd.user_id', '=', 'users.id')
            ->leftJoin('opportunities as o', 'o.dealership_id', '=', 'd.id')
            ->groupBy('users.id', 'users.name')
            ->havingRaw('COUNT(o.id) > 0')
            ->orderByDesc(DB::raw('COUNT(o.id)'))
            ->get()
            ->map(function (User $rep): array {
                $total = (int) $rep->total_deals;
                $won = (int) $rep->won_count;

                return [
                    'name' => $rep->name,
                    'total' => $total,
                    'won' => $won,
                    'lost' => (int) $rep->lost_count,
                    'pipeline' => (float) $rep->open_pipeline,
                    'winRate' => $total > 0 ? (int) round(($won / $total) * 100) : 0,
                ];
            })
            ->all();
    }
}
