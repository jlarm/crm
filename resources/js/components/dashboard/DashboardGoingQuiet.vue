<script setup lang="ts">
import type { QuietDealership } from '@/components/dashboard/types';
import { ratingClass } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';

defineProps<{
    dealerships: QuietDealership[];
}>();

function lastTouchLabel(dealership: QuietDealership): string {
    const days = dealership.daysSinceTouch;

    if (days === null) {
        return 'No contact logged';
    }
    if (days === 0) {
        return 'Last contact today';
    }
    if (days === 1) {
        return 'Last contact yesterday';
    }
    if (days < 60) {
        return `Last contact ${days} days ago`;
    }
    if (days < 365) {
        return `Last contact ${Math.round(days / 30)} months ago`;
    }

    return 'Last contact over a year ago';
}

function placeLabel(dealership: QuietDealership): string {
    return [dealership.city, dealership.state].filter(Boolean).join(', ');
}
</script>

<template>
    <DashboardPanel
        title="Going quiet"
        description="Hot and warm dealerships waiting the longest for a logged contact"
    >
        <ul class="grid divide-y divide-border sm:grid-cols-2 sm:divide-y-0">
            <li
                v-for="(dealership, index) in dealerships"
                :key="dealership.id"
                class="border-border sm:border-b"
                :class="index % 2 === 0 ? 'sm:border-r' : ''"
            >
                <Link
                    :href="`/dealerships/${dealership.id}`"
                    class="flex h-full items-center justify-between gap-3 px-5 py-3.5 transition-colors hover:bg-accent/40 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none focus-visible:ring-inset"
                >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-foreground">
                            {{ dealership.name }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            <span :class="dealership.daysSinceTouch === null ? 'text-destructive' : ''">
                                {{ lastTouchLabel(dealership) }}
                            </span>
                            <template v-if="placeLabel(dealership)"> · {{ placeLabel(dealership) }}</template>
                        </p>
                    </div>
                    <span
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium"
                        :class="ratingClass(dealership.rating)"
                    >
                        <span class="size-1.5 rounded-full bg-current" />
                        {{ dealership.ratingLabel }}
                    </span>
                </Link>
            </li>
        </ul>
    </DashboardPanel>
</template>
