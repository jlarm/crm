<script setup lang="ts">
interface Rep {
    name: string;
    total: number;
    won: number;
    lost: number;
    pipeline: number;
    winRate: number;
}

defineProps<{
    reps: Rep[];
}>();

function formatCurrency(value: number): string {
    if (value >= 1_000_000) return `$${(value / 1_000_000).toFixed(1)}M`;
    if (value >= 1_000) return `$${(value / 1_000).toFixed(0)}K`;
    return `$${value.toFixed(0)}`;
}

function winRateColor(rate: number): string {
    if (rate >= 30) return 'text-green-600 dark:text-green-400';
    if (rate >= 15) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
}
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                Rep Performance
            </h3>
        </div>

        <div v-if="reps.length === 0" class="px-5 py-8 text-center text-sm text-slate-400">
            No rep data yet.
        </div>

        <table v-else class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800">
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Rep</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-slate-500">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-slate-500">Won</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-slate-500">Lost</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-slate-500">Open Pipeline</th>
                    <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wide text-slate-500">Win Rate</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="rep in reps"
                    :key="rep.name"
                    class="border-b border-slate-100 last:border-0 dark:border-slate-800"
                >
                    <td class="px-5 py-3 font-medium text-slate-900 dark:text-slate-100">
                        {{ rep.name }}
                    </td>
                    <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400">
                        {{ rep.total }}
                    </td>
                    <td class="px-4 py-3 text-center text-green-600 dark:text-green-400">
                        {{ rep.won }}
                    </td>
                    <td class="px-4 py-3 text-center text-red-500 dark:text-red-400">
                        {{ rep.lost }}
                    </td>
                    <td class="px-4 py-3 text-right text-slate-900 dark:text-slate-100">
                        {{ formatCurrency(rep.pipeline) }}
                    </td>
                    <td class="px-5 py-3 text-center font-semibold" :class="winRateColor(rep.winRate)">
                        {{ rep.winRate }}%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
