<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp } from 'lucide-vue-next';
import { computed } from 'vue';
import type {
    ActivitySummary,
    BookSummary,
    PipelineSummary,
    TaskStats,
} from '@/components/dashboard/types';
import { formatCompactCurrency } from '@/components/dashboard/format';

const props = defineProps<{
    /** Metric keys to leave out, chosen by the viewer. */
    hiddenKeys?: string[];
    stats: TaskStats;
    book: BookSummary;
    pipeline: PipelineSummary;
    activity: ActivitySummary;
}>();

type Tone = 'up' | 'down' | 'alert' | 'neutral';

interface Metric {
    key: string;
    value: string;
    label: string;
    href?: string;
    badge?: { text: string; tone: Tone };
    caption: string;
}

const numberFormat = new Intl.NumberFormat('en-US');

const activityChange = computed<Metric['badge']>(() => {
    const { thisWeek, lastWeek } = props.activity;

    if (lastWeek === 0) {
        return thisWeek > 0 ? { text: 'New', tone: 'up' } : undefined;
    }

    const change = Math.round(((thisWeek - lastWeek) / lastWeek) * 100);

    return {
        text: `${Math.abs(change)}%`,
        tone: change > 0 ? 'up' : change < 0 ? 'down' : 'neutral',
    };
});

const allMetrics = computed<Metric[]>(() => [
    {
        key: 'activity',
        value: numberFormat.format(props.activity.thisWeek),
        label: 'Contacts logged',
        badge: activityChange.value,
        caption: 'Last 7 days',
    },
    {
        key: 'pipelineValue',
        value: formatCompactCurrency(props.pipeline.openValue),
        label: 'Open pipeline',
        href: '/sales',
        badge: { text: `${props.pipeline.openCount} deals`, tone: 'neutral' },
        caption: `${formatCompactCurrency(props.pipeline.weightedValue)} weighted`,
    },
    {
        key: 'won',
        value: numberFormat.format(props.pipeline.wonThisMonthCount),
        label: 'Won this month',
        href: '/sales',
        badge:
            props.pipeline.closingThisMonthCount > 0
                ? { text: `${props.pipeline.closingThisMonthCount} closing`, tone: 'neutral' }
                : undefined,
        caption: formatCompactCurrency(props.pipeline.wonThisMonthValue),
    },
    {
        key: 'tasks',
        value: numberFormat.format(props.stats.incomplete),
        label: 'Open tasks',
        href: '/tasks?filter=incomplete',
        badge:
            props.stats.overdue > 0
                ? { text: `${props.stats.overdue} overdue`, tone: 'alert' }
                : undefined,
        caption: `${props.stats.dueToday} due today · ${props.stats.completedThisWeek} done this week`,
    },
    {
        key: 'book',
        value: numberFormat.format(props.book.total),
        label: 'In your book',
        badge: props.book.hot > 0 ? { text: `${props.book.hot} hot`, tone: 'alert' } : undefined,
        caption: `${props.book.warm} warm · ${props.book.cold} cold`,
    },
]);

const metrics = computed<Metric[]>(() =>
    allMetrics.value.filter((metric) => !props.hiddenKeys?.includes(metric.key)),
);

const toneClass: Record<Tone, string> = {
    up: 'border-success/25 bg-success/10 text-success',
    down: 'border-destructive/25 bg-destructive/10 text-destructive',
    alert: 'border-destructive/25 bg-destructive/10 text-destructive',
    neutral: 'border-border bg-muted text-muted-foreground',
};
</script>

<template>
    <div v-if="metrics.length" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
        <component
            :is="metric.href ? Link : 'div'"
            v-for="metric in metrics"
            :key="metric.key"
            :href="metric.href"
            class="group flex min-w-0 flex-col justify-between gap-4 rounded-xl border border-border bg-card px-5 py-4 transition-colors"
            :class="metric.href ? 'hover:border-foreground/20 hover:bg-accent/40 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none' : ''"
        >
            <div class="flex items-start justify-between gap-3">
                <p class="text-[28px] leading-none font-semibold tracking-tight text-foreground tabular-nums">
                    {{ metric.value }}
                </p>
                <span
                    v-if="metric.badge"
                    class="inline-flex shrink-0 items-center gap-1 rounded-md border px-1.5 py-0.5 text-xs font-medium tabular-nums"
                    :class="toneClass[metric.badge.tone]"
                >
                    {{ metric.badge.text }}
                    <ArrowUp v-if="metric.badge.tone === 'up'" class="size-3" />
                    <ArrowDown v-else-if="metric.badge.tone === 'down'" class="size-3" />
                </span>
            </div>
            <div class="flex items-end justify-between gap-3">
                <p class="text-sm text-foreground">{{ metric.label }}</p>
                <p class="truncate text-right text-xs text-muted-foreground">{{ metric.caption }}</p>
            </div>
        </component>
    </div>
</template>
