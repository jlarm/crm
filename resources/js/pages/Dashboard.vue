<script setup lang="ts">
import { type Dealership, createColumns } from '@/components/companies/columns';
import DataTable from '@/components/companies/DataTable.vue';
import DashboardCardMenu from '@/components/dashboard/DashboardCardMenu.vue';
import DashboardGoingQuiet from '@/components/dashboard/DashboardGoingQuiet.vue';
import DashboardMetricRibbon from '@/components/dashboard/DashboardMetricRibbon.vue';
import DashboardPagination from '@/components/dashboard/DashboardPagination.vue';
import DashboardPipeline from '@/components/dashboard/DashboardPipeline.vue';
import DashboardRecentActivity from '@/components/dashboard/DashboardRecentActivity.vue';
import DashboardTaskBoard from '@/components/dashboard/DashboardTaskBoard.vue';
import type {
    ActivitySummary,
    BookSummary,
    PipelineSummary,
    QuietDealership,
    TaskStats,
} from '@/components/dashboard/types';
import DealershipFilters from '@/components/DealershipFilters.vue';
import LoadingOverlay from '@/components/LoadingOverlay.vue';
import TaskFormModal from '@/components/tasks/TaskFormModal.vue';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { useDashboardCards } from '@/composables/useDashboardCards';
import { useTableFilters } from '@/composables/useTableFilters';
import type { FilterOption, Task } from '@/pages/Tasks/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ListPlus, Plus, Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    dealerships: {
        data: Dealership[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
        status?: string;
        rating?: string;
        type?: string;
        scope?: string;
        include_imported?: string;
        sort?: string;
        direction?: string;
    };
    filterOptions: {
        statuses: FilterOption[];
        ratings: FilterOption[];
        types: FilterOption[];
    };
    taskStats: TaskStats;
    upcomingTasks: Task[];
    taskFormData: {
        allUsers: { id: number; name: string }[];
        allDealerships: { id: number; name: string }[];
        types: FilterOption[];
        priorities: FilterOption[];
    };
    bookSummary: BookSummary;
    goingQuiet: QuietDealership[];
    pipeline: PipelineSummary;
    activity: ActivitySummary;
}

const props = defineProps<Props>();

const page = usePage();
const currentUser = computed(
    () => (page.props.auth as { user: { id: number; name: string } }).user,
);
const currentUserId = computed(() => currentUser.value.id);

const isTaskFormOpen = ref(false);
const editingTask = ref<Task | null>(null);

function openTaskCreate(): void {
    editingTask.value = null;
    isTaskFormOpen.value = true;
}

function openTaskEdit(task: Task): void {
    editingTask.value = task;
    isTaskFormOpen.value = true;
}

const today = new Date();

const greeting = computed(() => {
    const hour = today.getHours();
    const partOfDay = hour < 12 ? 'morning' : hour < 18 ? 'afternoon' : 'evening';
    const firstName = currentUser.value.name.split(' ')[0];

    return `Good ${partOfDay}, ${firstName}`;
});

const todayLabel = computed(() =>
    today.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
    }),
);

function plural(count: number, singular: string): string {
    return `${count} ${singular}${count === 1 ? '' : 's'}`;
}

const statusLine = computed(() => {
    const { overdue, dueToday, incomplete } = props.taskStats;

    if (overdue > 0 && dueToday > 0) {
        return `${plural(overdue, 'task')} overdue and ${plural(dueToday, 'task')} due today.`;
    }
    if (overdue > 0) {
        return `${plural(overdue, 'task')} overdue. Nothing else is due today.`;
    }
    if (dueToday > 0) {
        return `${plural(dueToday, 'task')} due today. Nothing is overdue.`;
    }
    if (incomplete > 0) {
        return `Nothing is due today. ${plural(incomplete, 'task')} still open.`;
    }

    return 'Nothing is due today.';
});

const bookLine = computed(() => {
    const { hot, warm } = props.bookSummary;

    if (hot === 0 && warm === 0) {
        return null;
    }

    return `${hot} hot and ${warm} warm in your book`;
});

const { filters, isLoadingData, resetFilters } = useTableFilters({
    routeUrl: '/dashboard',
    initialFilters: {
        search: typeof props.filters.search === 'string' ? props.filters.search : '',
        status: typeof props.filters.status === 'string' ? props.filters.status : '',
        rating: typeof props.filters.rating === 'string' ? props.filters.rating : '',
        type: typeof props.filters.type === 'string' ? props.filters.type : '',
        scope:
            typeof props.filters.scope === 'string' &&
            ['mine', 'all'].includes(props.filters.scope)
                ? props.filters.scope
                : 'mine',
        include_imported:
            typeof props.filters.include_imported === 'string'
                ? props.filters.include_imported
                : '',
        sort: typeof props.filters.sort === 'string' ? props.filters.sort : '',
        direction:
            typeof props.filters.direction === 'string' ? props.filters.direction : 'asc',
    },
    debounceMs: 500,
    onlyProps: ['dealerships', 'filters'],
    storageKey: 'dashboard-dealership-filters',
    persistedKeys: [],
});

function handleSort(column: string): void {
    if (filters.value.sort === column) {
        filters.value.direction = filters.value.direction === 'asc' ? 'desc' : 'asc';
    } else {
        filters.value.sort = column;
        filters.value.direction = 'asc';
    }
}

const currentSorting = computed(() => ({
    column: filters.value.sort || '',
    direction: (filters.value.direction || 'asc') as 'asc' | 'desc',
}));

const { hidden: hiddenCards, isVisible } = useDashboardCards();

const showsTaskBoard = computed(() => isVisible('taskBoard'));
const showsPipeline = computed(() => isVisible('pipeline'));
const showsGoingQuiet = computed(() => isVisible('goingQuiet') && props.goingQuiet.length > 0);
const showsRecentActivity = computed(() => isVisible('recentActivity'));

const scopeOptions = [
    { value: 'mine', label: 'Mine' },
    { value: 'all', label: 'All' },
] as const;

const columns = createColumns(handleSort, () => currentSorting.value);
</script>

<template>
    <Head title="Dashboard" />

    <TaskFormModal
        v-model:open="isTaskFormOpen"
        :task="editingTask"
        :all-users="taskFormData.allUsers"
        :all-dealerships="taskFormData.allDealerships"
        :types="taskFormData.types"
        :priorities="taskFormData.priorities"
        :current-user-id="currentUserId"
    />

    <div class="mx-auto max-w-[1680px] space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <LoadingOverlay />

        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium tracking-[0.08em] text-muted-foreground uppercase">
                    {{ todayLabel }}
                </p>
                <h1 class="mt-1.5 text-2xl font-semibold tracking-tight text-foreground">
                    {{ greeting }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ statusLine }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <DashboardCardMenu />
                <Button type="button" variant="outline" class="h-10" @click="openTaskCreate">
                    <ListPlus class="size-4" />
                    New task
                </Button>
                <Button as-child variant="outline" class="h-10">
                    <Link href="/dealerships/import">
                        <Upload class="size-4" />
                        Import
                    </Link>
                </Button>
                <Button as-child class="h-10 px-4">
                    <Link href="/dealerships/create">
                        Add dealership
                        <Plus class="size-4" />
                    </Link>
                </Button>
            </div>
        </header>

        <DashboardMetricRibbon
            :hidden-keys="hiddenCards"
            :stats="taskStats"
            :book="bookSummary"
            :pipeline="pipeline"
            :activity="activity"
        />

        <div v-if="showsTaskBoard || showsPipeline" class="grid gap-6 xl:grid-cols-3">
            <DashboardTaskBoard
                v-if="showsTaskBoard"
                :class="showsPipeline ? 'xl:col-span-2' : 'xl:col-span-3'"
                :tasks="upcomingTasks"
                @create-task="openTaskCreate"
                @edit-task="openTaskEdit"
            />
            <DashboardPipeline
                v-if="showsPipeline"
                :class="showsTaskBoard ? '' : 'xl:col-span-3'"
                :pipeline="pipeline"
            />
        </div>

        <div v-if="showsGoingQuiet || showsRecentActivity" class="grid items-start gap-6 xl:grid-cols-3">
            <DashboardGoingQuiet
                v-if="showsGoingQuiet"
                :class="showsRecentActivity ? 'xl:col-span-2' : 'xl:col-span-3'"
                :dealerships="goingQuiet"
            />
            <DashboardRecentActivity
                v-if="showsRecentActivity"
                :class="showsGoingQuiet ? '' : 'xl:col-span-3'"
                :entries="activity.recent"
            />
        </div>

        <section v-if="isVisible('dealerships')" id="dealerships" class="scroll-mt-20 pt-2">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold tracking-tight text-foreground">Dealerships</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ bookLine ?? 'Everything you are responsible for' }}
                    </p>
                </div>

                <div
                    role="radiogroup"
                    aria-label="Dealership scope"
                    class="inline-flex rounded-lg border border-border bg-card p-0.5"
                >
                    <button
                        v-for="option in scopeOptions"
                        :key="option.value"
                        type="button"
                        role="radio"
                        :aria-checked="filters.scope === option.value"
                        class="h-8 rounded-md px-3.5 text-sm transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        :class="
                            filters.scope === option.value
                                ? 'bg-primary font-medium text-primary-foreground'
                                : 'text-muted-foreground hover:text-foreground'
                        "
                        @click="filters.scope = option.value"
                    >
                        {{ option.label }}
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <DealershipFilters
                    v-model="filters"
                    :statuses="filterOptions.statuses"
                    :ratings="filterOptions.ratings"
                    :types="filterOptions.types"
                    @reset="resetFilters"
                />
            </div>

            <div v-if="isLoadingData" class="space-y-2">
                <Skeleton class="h-11 w-full rounded-xl" />
                <Skeleton v-for="i in 10" :key="i" class="h-12 w-full" />
            </div>

            <template v-else>
                <DataTable
                    :columns="columns"
                    :data="dealerships.data"
                    :sorting="currentSorting"
                    :row-href="(d) => `/dealerships/${d.id}`"
                />

                <DashboardPagination
                    :current-page="dealerships.current_page"
                    :last-page="dealerships.last_page"
                    :from="dealerships.from"
                    :to="dealerships.to"
                    :total="dealerships.total"
                    :links="dealerships.links"
                />
            </template>
        </section>
    </div>
</template>
