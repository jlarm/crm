<script setup lang="ts">
import { type Dealership, createColumns } from '@/components/companies/columns';
import DataTable from '@/components/companies/DataTable.vue';
import DashboardPagination from '@/components/dashboard/DashboardPagination.vue';
import DashboardTaskStats from '@/components/dashboard/DashboardTaskStats.vue';
import DashboardTasksWidget from '@/components/dashboard/DashboardTasksWidget.vue';
import DealershipFilters from '@/components/DealershipFilters.vue';
import LoadingOverlay from '@/components/LoadingOverlay.vue';
import TaskFormModal from '@/components/tasks/TaskFormModal.vue';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { useTableFilters } from '@/composables/useTableFilters';
import type { FilterOption, Task } from '@/pages/Tasks/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface FilterOption2 {
    value: string;
    label: string;
}

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
        statuses: FilterOption2[];
        ratings: FilterOption2[];
        types: FilterOption2[];
    };
    taskStats: {
        incomplete: number;
        overdue: number;
        dueToday: number;
        completedThisWeek: number;
    };
    upcomingTasks: Task[];
    taskFormData: {
        allUsers: { id: number; name: string }[];
        allDealerships: { id: number; name: string }[];
        types: FilterOption[];
        priorities: FilterOption[];
    };
}

const props = defineProps<Props>();

const page = usePage();
const currentUserId = computed(() => (page.props.auth as { user: { id: number } }).user.id);

const isTaskFormOpen = ref(false);
const editingTask = ref<Task | null>(null);

function openTaskCreate(): void {
    editingTask.value = null;
    isTaskFormOpen.value = true;
}

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
            typeof props.filters.direction === 'string'
                ? props.filters.direction
                : 'asc',
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

const columns = createColumns(handleSort);
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

    <div class="space-y-5 p-6">
        <LoadingOverlay />

        <!-- Task stat cards -->
        <DashboardTaskStats :stats="taskStats" />

        <!-- Actions row -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex rounded-md border border-border bg-background p-1">
                    <Button
                        type="button"
                        size="sm"
                        :variant="filters.scope === 'all' ? 'ghost' : 'secondary'"
                        @click="filters.scope = 'mine'"
                    >
                        My dealerships
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        :variant="filters.scope === 'all' ? 'secondary' : 'ghost'"
                        @click="filters.scope = 'all'"
                    >
                        All dealerships
                    </Button>
                </div>

                <DealershipFilters
                    v-model="filters"
                    :statuses="filterOptions.statuses"
                    :ratings="filterOptions.ratings"
                    :types="filterOptions.types"
                    @reset="resetFilters"
                />
            </div>

            <div class="flex items-center gap-2">
                <Button type="button" variant="outline" @click="openTaskCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    New Task
                </Button>
                <Link href="/dealerships/create">
                    <Button type="button">New Dealership</Button>
                </Link>
            </div>
        </div>

        <!-- Two-column layout: dealerships + tasks widget -->
        <div class="flex items-start gap-5">
            <!-- Dealership table -->
            <div class="min-w-0 flex-1">
                <div v-if="isLoadingData" class="space-y-2">
                    <Skeleton class="h-10 w-full" />
                    <Skeleton v-for="i in 15" :key="i" class="h-12 w-full" />
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
            </div>

            <!-- Tasks widget -->
            <div class="w-72 shrink-0 xl:w-80">
                <DashboardTasksWidget
                    :tasks="upcomingTasks"
                    @create-task="openTaskCreate"
                />
            </div>
        </div>
    </div>
</template>
