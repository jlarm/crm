<script setup lang="ts">
interface StageConversion {
    stage: string;
    label: string;
    reached: number;
    advanced: number;
    conversionRate: number | null;
    avgDaysInStage: number | null;
}

defineProps<{
    stages: StageConversion[];
}>();

function conversionColor(rate: number | null): string {
    if (rate === null) return 'text-slate-400';
    if (rate >= 50) return 'text-green-600 dark:text-green-400';
    if (rate >= 25) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
}
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                Stage Conversion
            </h3>
            <p class="mt-0.5 text-xs text-slate-500">How many deals that reach each stage move on to the next</p>
        </div>

        <div v-if="stages.every((stage) => stage.reached === 0)" class="px-5 py-8 text-center text-sm text-slate-400">
            No stage history yet.
        </div>

        <div v-else class="overflow-x-auto">
            <table class="w-full min-w-[32rem] text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Stage</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-slate-500">Reached</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-slate-500">Moved On</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-slate-500">Conversion</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-slate-500">Avg Days in Stage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="stage in stages"
                        :key="stage.stage"
                        class="border-b border-slate-100 last:border-0 dark:border-slate-800"
                    >
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-slate-100">{{ stage.label }}</td>
                        <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400">{{ stage.reached }}</td>
                        <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400">{{ stage.advanced }}</td>
                        <td class="px-4 py-3 text-center font-semibold" :class="conversionColor(stage.conversionRate)">
                            {{ stage.conversionRate === null ? '—' : `${stage.conversionRate}%` }}
                        </td>
                        <td class="px-5 py-3 text-right text-slate-600 dark:text-slate-400">
                            {{ stage.avgDaysInStage === null ? '—' : `${stage.avgDaysInStage}d` }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
