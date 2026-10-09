<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import { formatCompactCurrency, formatRelativeDay } from '@/components/dashboard/format';
import type { WeeklyDigest, WeeklyDigestMove } from '@/components/dashboard/types';
import { Skeleton } from '@/components/ui/skeleton';
import { show } from '@/routes/dealerships';

const props = defineProps<{
    digest: WeeklyDigest;
    /** Undefined while the deferred AI summary loads; null when there is none. */
    summary: string | null | undefined;
}>();

const stats = computed(() => [
    { label: 'Deals moved forward', value: props.digest.counts.advanced },
    {
        label: 'Won',
        value: props.digest.counts.won,
        detail: props.digest.counts.wonValue > 0 ? formatCompactCurrency(props.digest.counts.wonValue) : null,
    },
    { label: 'New deals', value: props.digest.counts.newDeals },
    { label: 'Activities logged', value: props.digest.counts.activitiesLogged },
    {
        label: 'Tasks due this week',
        value: props.digest.counts.tasksDueThisWeek,
        detail: props.digest.counts.tasksOverdue > 0 ? `${props.digest.counts.tasksOverdue} overdue` : null,
        warn: props.digest.counts.tasksOverdue > 0,
    },
]);

const isQuietWeek = computed(
    () => props.digest.moves.length === 0 && props.digest.quietDeals.length === 0 && props.digest.counts.activitiesLogged === 0,
);

const directionLabel: Record<WeeklyDigestMove['direction'], string> = {
    new: 'New',
    advanced: 'Moved up',
    slipped: 'Moved back',
    won: 'Won',
    lost: 'Lost',
};

const directionClass: Record<WeeklyDigestMove['direction'], string> = {
    new: 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
    advanced: 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
    slipped: 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
    won: 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-200',
    lost: 'bg-destructive/10 text-destructive',
};

function quietLabel(days: number | null): string {
    return days === null ? 'No activity logged yet' : `No activity in ${days} days`;
}
</script>

<template>
    <DashboardPanel title="This week" description="Your last 7 days across your dealerships">
        <div class="space-y-5 px-5 py-4">
            <div v-if="summary === undefined" class="space-y-2" aria-label="Loading summary">
                <Skeleton class="h-4 w-full" />
                <Skeleton class="h-4 w-4/5" />
            </div>
            <p v-else-if="summary" class="flex gap-2.5 text-sm leading-relaxed text-foreground">
                <Sparkles class="mt-0.5 size-4 shrink-0 text-violet-500" aria-hidden="true" />
                <span>{{ summary }}</span>
            </p>

            <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <div v-for="stat in stats" :key="stat.label" class="rounded-lg border border-border px-3 py-2.5">
                    <dt class="text-xs text-muted-foreground">{{ stat.label }}</dt>
                    <dd class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-lg font-semibold tabular-nums text-foreground">{{ stat.value }}</span>
                        <span
                            v-if="stat.detail"
                            class="text-xs"
                            :class="stat.warn ? 'text-destructive' : 'text-muted-foreground'"
                        >
                            {{ stat.detail }}
                        </span>
                    </dd>
                </div>
            </dl>

            <p v-if="isQuietWeek" class="text-sm text-muted-foreground">
                No deal movement or logged activity this week.
            </p>

            <div v-else class="grid gap-5 lg:grid-cols-2">
                <div v-if="digest.moves.length > 0">
                    <h3 class="mb-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">What moved</h3>
                    <ul class="divide-y divide-border">
                        <li v-for="move in digest.moves" :key="move.id" class="flex items-center justify-between gap-3 py-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-foreground">{{ move.name }}</p>
                                <p class="truncate text-xs text-muted-foreground">
                                    <Link :href="show(move.dealership.id)" class="hover:text-foreground hover:underline">
                                        {{ move.dealership.name }}
                                    </Link>
                                    · {{ formatRelativeDay(move.changedAt) }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span v-if="move.from" class="hidden items-center gap-1 text-xs text-muted-foreground sm:inline-flex">
                                    {{ move.from.label }} <ArrowRight class="size-3" /> {{ move.to.label }}
                                </span>
                                <span class="rounded-md px-2 py-0.5 text-xs font-medium" :class="directionClass[move.direction]">
                                    {{ directionLabel[move.direction] }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div v-if="digest.quietDeals.length > 0">
                    <h3 class="mb-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">Needs attention</h3>
                    <ul class="divide-y divide-border">
                        <li v-for="deal in digest.quietDeals" :key="deal.id">
                            <Link
                                :href="show(deal.dealership.id)"
                                class="flex items-center justify-between gap-3 py-2 transition-colors hover:text-foreground"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-foreground">{{ deal.name }}</p>
                                    <p class="truncate text-xs text-muted-foreground">
                                        {{ deal.dealership.name }} · {{ deal.stageLabel }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-xs text-destructive">{{ quietLabel(deal.daysSinceActivity) }}</p>
                                    <p v-if="deal.estimatedValue > 0" class="text-xs text-muted-foreground tabular-nums">
                                        {{ formatCompactCurrency(deal.estimatedValue) }}
                                    </p>
                                </div>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </DashboardPanel>
</template>
