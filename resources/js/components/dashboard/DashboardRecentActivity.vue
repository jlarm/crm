<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import { formatRelativeDay } from '@/components/dashboard/format';
import type { ActivityEntry } from '@/components/dashboard/types';

defineProps<{
    entries: ActivityEntry[];
}>();
</script>

<template>
    <DashboardPanel title="Recent activity" description="Latest contacts logged on your dealerships">
        <p v-if="entries.length === 0" class="px-5 py-10 text-center text-sm text-muted-foreground">
            Nothing logged yet.
        </p>

        <ol v-else class="divide-y divide-border">
            <li v-for="entry in entries" :key="entry.id" class="relative px-5 py-3.5">
                <div class="flex items-center justify-between gap-3">
                    <Link
                        v-if="entry.dealership"
                        :href="`/dealerships/${entry.dealership.id}`"
                        class="min-w-0 truncate text-sm font-medium text-foreground hover:underline"
                    >
                        {{ entry.dealership.name }}
                    </Link>
                    <time :datetime="entry.date" class="shrink-0 text-xs text-muted-foreground tabular-nums">
                        {{ formatRelativeDay(entry.date) }}
                    </time>
                </div>
                <p v-if="entry.details" class="mt-1 line-clamp-2 text-xs text-muted-foreground">
                    {{ entry.details }}
                </p>
                <p class="mt-1.5 flex items-center gap-1.5 text-[11px] text-muted-foreground">
                    <span
                        v-if="entry.category"
                        class="rounded border border-border bg-muted px-1.5 py-px text-foreground/80"
                    >
                        {{ entry.category }}
                    </span>
                    <span v-if="entry.author">by {{ entry.author }}</span>
                </p>
            </li>
        </ol>
    </DashboardPanel>
</template>
