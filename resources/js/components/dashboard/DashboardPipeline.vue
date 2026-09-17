<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import { formatCompactCurrency } from '@/components/dashboard/format';
import type { PipelineSummary } from '@/components/dashboard/types';

const props = defineProps<{
    pipeline: PipelineSummary;
}>();

const largestStage = computed(() =>
    Math.max(1, ...props.pipeline.stages.map((stage) => stage.value)),
);
</script>

<template>
    <DashboardPanel title="Pipeline" description="Open deals across your dealerships">
        <template #actions>
            <Link
                href="/sales"
                class="text-xs text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
            >
                Sales report
            </Link>
        </template>

        <div v-if="pipeline.openCount === 0" class="px-5 py-10 text-center text-sm text-muted-foreground">
            No open deals yet.
        </div>

        <ul v-else class="space-y-3.5 px-5 py-5">
            <li v-for="stage in pipeline.stages" :key="stage.stage">
                <div class="mb-1.5 flex items-baseline justify-between gap-3 text-sm">
                    <span class="text-foreground">
                        {{ stage.label }}
                        <span class="ml-1 text-xs text-muted-foreground tabular-nums">{{ stage.count }}</span>
                    </span>
                    <span class="font-medium text-foreground tabular-nums">
                        {{ formatCompactCurrency(stage.value) }}
                    </span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full rounded-full bg-brand transition-[width] duration-500"
                        :style="{
                            width: `${(stage.value / largestStage) * 100}%`,
                            minWidth: stage.count > 0 ? '4px' : '0',
                        }"
                    />
                </div>
            </li>
        </ul>

        <dl class="grid grid-cols-2 border-t border-border text-sm">
            <div class="border-r border-border px-5 py-3">
                <dt class="text-xs text-muted-foreground">Weighted</dt>
                <dd class="mt-0.5 font-semibold text-foreground tabular-nums">
                    {{ formatCompactCurrency(pipeline.weightedValue) }}
                </dd>
            </div>
            <div class="px-5 py-3">
                <dt class="text-xs text-muted-foreground">Closing this month</dt>
                <dd class="mt-0.5 font-semibold text-foreground tabular-nums">
                    {{ pipeline.closingThisMonthCount }}
                </dd>
            </div>
        </dl>
    </DashboardPanel>
</template>
