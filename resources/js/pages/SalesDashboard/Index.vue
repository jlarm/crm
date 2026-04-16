<script setup lang="ts">
import KpiCard from '@/components/sales/KpiCard.vue';
import PipelineStageChart from '@/components/sales/PipelineStageChart.vue';
import RepPerformanceTable from '@/components/sales/RepPerformanceTable.vue';
import { Head } from '@inertiajs/vue3';

interface Stage {
    stage: string;
    label: string;
    color: string;
    count: number;
    value: number;
}

interface Rep {
    name: string;
    total: number;
    won: number;
    lost: number;
    pipeline: number;
    winRate: number;
}

interface Kpis {
    pipelineValue: number;
    openCount: number;
    wonCount: number;
    closedCount: number;
    winRate: number;
    avgDealSize: number;
    avgDaysToClose: number;
    closingThisMonthCount: number;
    closingThisMonthValue: number;
    wonLastMonthCount: number;
    lastMonthLabel: string;
}

defineProps<{
    kpis: Kpis;
    pipelineByStage: Stage[];
    repPerformance: Rep[];
}>();

function formatCurrency(value: number): string {
    if (value >= 1_000_000) return `$${(value / 1_000_000).toFixed(1)}M`;
    if (value >= 1_000) return `$${(value / 1_000).toFixed(0)}K`;
    return `$${value.toFixed(0)}`;
}
</script>

<template>
    <Head title="Sales Dashboard" />

    <div class="space-y-6 p-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Sales Dashboard</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Pipeline health and rep performance</p>
            </div>

            <!-- KPI cards -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
                <KpiCard
                    label="Open Pipeline"
                    :value="formatCurrency(kpis.pipelineValue)"
                    :sub="`${kpis.openCount} open deals`"
                />
                <KpiCard
                    label="Win Rate"
                    :value="`${kpis.winRate}%`"
                    :sub="`${kpis.wonCount} won of ${kpis.closedCount} closed`"
                />
                <KpiCard
                    label="Avg Deal Size"
                    :value="kpis.avgDealSize > 0 ? formatCurrency(kpis.avgDealSize) : '—'"
                    sub="Won opportunities"
                />
                <KpiCard
                    label="Avg Days to Close"
                    :value="kpis.avgDaysToClose > 0 ? `${kpis.avgDaysToClose}d` : '—'"
                    sub="Creation to close"
                />
                <KpiCard
                    label="Closing This Month"
                    :value="`${kpis.closingThisMonthCount}`"
                    :sub="kpis.closingThisMonthValue > 0 ? formatCurrency(kpis.closingThisMonthValue) + ' potential' : 'No deals expected'"
                />
                <KpiCard
                    label="Won Last Month"
                    :value="`${kpis.wonLastMonthCount}`"
                    :sub="kpis.lastMonthLabel"
                />
            </div>

            <!-- Pipeline by stage chart -->
            <PipelineStageChart :stages="pipelineByStage" />

            <!-- Rep performance -->
            <RepPerformanceTable :reps="repPerformance" />
    </div>
</template>
