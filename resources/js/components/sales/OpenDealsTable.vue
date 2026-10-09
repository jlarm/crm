<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import { computed } from 'vue';
import DashboardPagination from '@/components/dashboard/DashboardPagination.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTableFilters } from '@/composables/useTableFilters';
import { show } from '@/routes/dealerships';
import { index as salesIndex } from '@/routes/sales';

interface OpenDeal {
    id: number;
    name: string;
    stage: string;
    stageLabel: string;
    estimatedValue: number;
    probability: number | null;
    expectedCloseDate: string | null;
    nextAction: string | null;
    dealership: { id: number; name: string };
    repName: string | null;
    lastActivityAt: string | null;
    daysSinceActivity: number | null;
    recentActivityCount: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface FilterOption {
    value: string;
    label: string;
}

interface DealFilters {
    rep: string;
    stage: string;
    activity: string;
    closing: string;
}

const props = defineProps<{
    filters: DealFilters;
    repOptions: FilterOption[];
    stageOptions: FilterOption[];
    deals: {
        data: OpenDeal[];
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        links: PaginationLink[];
    };
}>();

/** Reka's Select cannot hold an empty string, so "any" stands in for no filter. */
const ANY = 'any';

const { filters, isLoadingData, hasActiveFilters } = useTableFilters({
    routeUrl: salesIndex.url(),
    initialFilters: { ...props.filters },
    onlyProps: ['openDeals', 'dealFilters'],
    debounceMs: 0,
});

function clearFilters(): void {
    filters.value = { rep: '', stage: '', activity: '', closing: '' };
}

function filterModel(key: keyof DealFilters) {
    return computed({
        get: () => filters.value[key] || ANY,
        set: (value: string) => {
            filters.value[key] = value === ANY ? '' : value;
        },
    });
}

const repFilter = filterModel('rep');
const stageFilter = filterModel('stage');
const activityFilter = filterModel('activity');
const closingFilter = filterModel('closing');

const stageBadgeClass: Record<string, string> = {
    prospect: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    contacted: 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
    qualified: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    demo: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300',
    proposal: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    negotiation: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
};

function formatCurrency(value: number): string {
    if (value >= 1_000_000) return `$${(value / 1_000_000).toFixed(1)}M`;
    if (value >= 1_000) return `$${(value / 1_000).toFixed(0)}K`;
    return `$${value.toFixed(0)}`;
}

function formatDate(value: string): string {
    return new Date(`${value}T00:00:00`).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function lastActivityLabel(days: number | null): string {
    if (days === null) return 'No activity';
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    return `${days}d ago`;
}

function lastActivityColor(days: number | null): string {
    if (days === null) return 'text-slate-400';
    if (days <= 7) return 'text-green-600 dark:text-green-400';
    if (days <= 21) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
}
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                Open Deals
            </h3>
            <p class="mt-0.5 text-xs text-slate-500">Most recently worked first</p>

            <div class="mt-3 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center">
                <Select v-model="repFilter">
                    <SelectTrigger class="h-8 w-full text-xs sm:w-40" aria-label="Rep">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="ANY">All reps</SelectItem>
                        <SelectItem v-for="option in repOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="stageFilter">
                    <SelectTrigger class="h-8 w-full text-xs sm:w-36" aria-label="Stage">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="ANY">All stages</SelectItem>
                        <SelectItem v-for="option in stageOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="activityFilter">
                    <SelectTrigger class="h-8 w-full text-xs sm:w-40" aria-label="Activity">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="ANY">Any activity</SelectItem>
                        <SelectItem value="quiet">Quiet 21+ days</SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="closingFilter">
                    <SelectTrigger class="h-8 w-full text-xs sm:w-40" aria-label="Close date">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="ANY">Any close date</SelectItem>
                        <SelectItem value="this_month">Closing this month</SelectItem>
                        <SelectItem value="overdue">Past close date</SelectItem>
                    </SelectContent>
                </Select>

                <Button
                    v-if="hasActiveFilters()"
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="h-8 text-xs"
                    @click="clearFilters"
                >
                    <X class="size-3.5" />
                    Clear
                </Button>
            </div>
        </div>

        <div v-if="deals.total === 0" class="px-5 py-8 text-center text-sm text-slate-400">
            {{ hasActiveFilters() ? 'No open deals match these filters.' : 'No open deals.' }}
        </div>

        <div v-else class="overflow-x-auto transition-opacity" :class="isLoadingData ? 'opacity-60' : ''">
            <table class="w-full min-w-[56rem] text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Deal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Rep</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Stage</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-slate-500">Value</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Close Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Last Activity</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Next Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="deal in deals.data"
                        :key="deal.id"
                        class="border-b border-slate-100 last:border-0 dark:border-slate-800"
                    >
                        <td class="px-5 py-3">
                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ deal.name }}</p>
                            <Link
                                :href="show(deal.dealership.id)"
                                class="text-xs text-slate-500 hover:text-slate-900 hover:underline dark:hover:text-slate-100"
                            >
                                {{ deal.dealership.name }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                            {{ deal.repName ?? 'Unassigned' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="stageBadgeClass[deal.stage] ?? stageBadgeClass.prospect"
                            >
                                {{ deal.stageLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-900 dark:text-slate-100">
                            {{ deal.estimatedValue > 0 ? formatCurrency(deal.estimatedValue) : '—' }}
                            <span v-if="deal.probability !== null" class="block text-xs text-slate-500">
                                {{ deal.probability }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                            {{ deal.expectedCloseDate ? formatDate(deal.expectedCloseDate) : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium" :class="lastActivityColor(deal.daysSinceActivity)">
                                {{ lastActivityLabel(deal.daysSinceActivity) }}
                            </span>
                            <span v-if="deal.recentActivityCount > 0" class="block text-xs text-slate-500">
                                {{ deal.recentActivityCount }} in last 30 days
                            </span>
                        </td>
                        <td class="max-w-56 truncate px-5 py-3 text-slate-600 dark:text-slate-400" :title="deal.nextAction ?? undefined">
                            {{ deal.nextAction ?? '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="deals.total > 0" class="border-t border-slate-100 px-5 pb-4 dark:border-slate-800">
            <DashboardPagination
                :current-page="deals.current_page"
                :last-page="deals.last_page"
                :from="deals.from"
                :to="deals.to"
                :total="deals.total"
                :links="deals.links"
                :only="['openDeals']"
                page-name="deals_page"
            />
        </div>
    </div>
</template>
