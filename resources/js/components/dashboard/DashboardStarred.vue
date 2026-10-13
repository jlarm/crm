<script setup lang="ts">
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import type { StarredDealership } from '@/components/dashboard/types';
import { ratingClass } from '@/lib/utils';
import { show } from '@/routes/dealerships';
import { destroy as unstar } from '@/routes/dealerships/star';
import { Link, router } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';

defineProps<{
    dealerships: StarredDealership[];
}>();

function placeLabel(dealership: StarredDealership): string {
    return [dealership.city, dealership.state].filter(Boolean).join(', ');
}

function remove(dealership: StarredDealership): void {
    router.delete(unstar(dealership.id).url, {
        preserveScroll: true,
        preserveState: true,
        only: ['starredDealerships'],
        showProgress: false,
    });
}
</script>

<template>
    <DashboardPanel title="Starred" description="Dealerships you starred for quick access">
        <p v-if="dealerships.length === 0" class="flex items-center gap-3 px-5 py-6 text-sm text-muted-foreground">
            <Star class="size-5 shrink-0 text-amber-400" aria-hidden="true" />
            Star a dealership from its page to keep it here.
        </p>

        <ul v-else class="divide-y divide-border">
            <li v-for="dealership in dealerships" :key="dealership.id" class="group flex items-center gap-2 pr-3">
                <Link
                    :href="show(dealership.id)"
                    class="flex min-w-0 flex-1 items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-accent/40 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none focus-visible:ring-inset"
                >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-foreground">{{ dealership.name }}</p>
                        <p v-if="placeLabel(dealership)" class="truncate text-xs text-muted-foreground">{{ placeLabel(dealership) }}</p>
                    </div>
                    <span
                        v-if="dealership.rating"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium"
                        :class="ratingClass(dealership.rating)"
                    >
                        <span class="size-1.5 rounded-full bg-current" />
                        {{ dealership.ratingLabel }}
                    </span>
                </Link>
                <button
                    type="button"
                    class="shrink-0 rounded-md p-1.5 text-amber-400 transition-colors hover:bg-amber-50 hover:text-amber-500 dark:hover:bg-amber-950/40"
                    :aria-label="`Unstar ${dealership.name}`"
                    title="Unstar"
                    @click="remove(dealership)"
                >
                    <Star class="size-4 fill-current" />
                </button>
            </li>
        </ul>
    </DashboardPanel>
</template>
