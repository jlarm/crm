<script setup lang="ts">
import { computed } from 'vue';

interface Stage {
    stage: string;
    label: string;
    color: string;
    count: number;
    value: number;
}

const props = defineProps<{
    stages: Stage[];
}>();

const maxValue = computed(() =>
    Math.max(...props.stages.map((s) => s.value), 1)
);

function barWidth(value: number): string {
    return `${Math.round((value / maxValue.value) * 100)}%`;
}

function formatCurrency(value: number): string {
    if (value >= 1_000_000) return `$${(value / 1_000_000).toFixed(1)}M`;
    if (value >= 1_000) return `$${(value / 1_000).toFixed(0)}K`;
    return `$${value.toFixed(0)}`;
}

const colorMap: Record<string, string> = {
    gray: 'bg-slate-400',
    info: 'bg-cyan-500',
    primary: 'bg-blue-500',
    indigo: 'bg-indigo-500',
    warning: 'bg-amber-500',
    orange: 'bg-orange-500',
    success: 'bg-green-500',
    danger: 'bg-red-500',
};
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
        <h3 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-300">
            Pipeline Value by Stage
        </h3>

        <div class="space-y-3">
            <div
                v-for="stage in stages"
                :key="stage.stage"
                class="flex items-center gap-3"
            >
                <span class="w-24 shrink-0 text-right text-xs text-slate-500 dark:text-slate-400">
                    {{ stage.label }}
                </span>

                <div class="flex-1">
                    <div class="h-6 w-full overflow-hidden rounded-md bg-slate-100 dark:bg-slate-800">
                        <div
                            class="h-full rounded-md transition-all duration-500"
                            :class="colorMap[stage.color] ?? 'bg-slate-400'"
                            :style="{ width: stage.value > 0 ? barWidth(stage.value) : '2px' }"
                        />
                    </div>
                </div>

                <div class="w-28 shrink-0 text-right">
                    <span class="text-sm font-medium text-slate-900 dark:text-slate-100">
                        {{ formatCurrency(stage.value) }}
                    </span>
                    <span class="ml-1 text-xs text-slate-400">
                        ({{ stage.count }})
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
