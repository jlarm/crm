<script setup lang="ts">
import TaskCard from '@/components/tasks/TaskCard.vue';
import TaskFormModal from '@/components/tasks/TaskFormModal.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { FilterOption, Task } from './types';

interface User {
    id: number;
    name: string;
}

interface Props {
    tasks: {
        data: Task[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        filter: string;
        priority: string;
        type: string;
        dealership_id: string;
    };
    filterOptions: {
        types: FilterOption[];
        priorities: FilterOption[];
    };
    allUsers: User[];
    allDealerships: { id: number; name: string }[];
    summary: {
        incomplete: number;
        overdue: number;
        dueToday: number;
    };
}

const props = defineProps<Props>();

const page = usePage();
const currentUserId = computed(() => (page.props.auth as { user: { id: number } }).user.id);

const isFormOpen = ref(false);
const editingTask = ref<Task | null>(null);

function openCreate(): void {
    editingTask.value = null;
    isFormOpen.value = true;
}

function openEdit(task: Task): void {
    editingTask.value = task;
    isFormOpen.value = true;
}

const activeFilter = computed({
    get: () => props.filters.filter,
    set: (value) => applyFilter('filter', value),
});

const activePriority = computed({
    get: () => props.filters.priority,
    set: (value) => applyFilter('priority', value),
});

const activeType = computed({
    get: () => props.filters.type,
    set: (value) => applyFilter('type', value),
});

function applyFilter(key: string, value: string): void {
    router.get(
        '/tasks',
        {
            ...props.filters,
            [key]: value,
        },
        { preserveState: true, replace: true },
    );
}

function resetFilters(): void {
    router.get('/tasks', { filter: 'incomplete' }, { preserveState: false });
}

const hasActiveFilters = computed(
    () => props.filters.priority || props.filters.type,
);
</script>

<template>
    <Head title="Tasks" />

    <TaskFormModal
        v-model:open="isFormOpen"
        :task="editingTask"
        :all-users="allUsers"
        :all-dealerships="allDealerships"
        :types="filterOptions.types"
        :priorities="filterOptions.priorities"
        :current-user-id="currentUserId"
    />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">Tasks</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ summary.incomplete }} open ·
                    <span :class="summary.overdue > 0 ? 'text-red-600 font-medium' : ''">
                        {{ summary.overdue }} overdue
                    </span>
                    · {{ summary.dueToday }} due today
                </p>
            </div>
            <Button @click="openCreate">New Task</Button>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex rounded-md border border-border bg-background p-1">
                <Button
                    v-for="{ value, label } in [
                        { value: 'incomplete', label: 'Open' },
                        { value: 'overdue', label: 'Overdue' },
                        { value: 'completed', label: 'Completed' },
                    ]"
                    :key="value"
                    type="button"
                    size="sm"
                    :variant="activeFilter === value ? 'secondary' : 'ghost'"
                    @click="activeFilter = value"
                >
                    {{ label }}
                </Button>
            </div>

            <Select
                :model-value="activePriority || '_all'"
                @update:model-value="activePriority = $event === '_all' ? '' : $event"
            >
                <SelectTrigger class="w-36">
                    <SelectValue placeholder="Priority" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="_all">All priorities</SelectItem>
                    <SelectItem
                        v-for="option in filterOptions.priorities"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select
                :model-value="activeType || '_all'"
                @update:model-value="activeType = $event === '_all' ? '' : $event"
            >
                <SelectTrigger class="w-36">
                    <SelectValue placeholder="Type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="_all">All types</SelectItem>
                    <SelectItem
                        v-for="option in filterOptions.types"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Button
                v-if="hasActiveFilters"
                variant="ghost"
                size="sm"
                @click="resetFilters"
            >
                Reset
            </Button>
        </div>

        <div v-if="tasks.data.length === 0" class="py-16 text-center">
            <p class="text-sm text-slate-400">
                {{
                    activeFilter === 'completed'
                        ? 'No completed tasks.'
                        : activeFilter === 'overdue'
                          ? 'No overdue tasks.'
                          : 'No open tasks. Create one to get started.'
                }}
            </p>
            <Button v-if="activeFilter === 'incomplete'" class="mt-4" @click="openCreate">
                Create your first task
            </Button>
        </div>

        <div v-else class="space-y-2">
            <TaskCard
                v-for="task in tasks.data"
                :key="task.id"
                :task="task"
                @edit="openEdit"
            />
        </div>

        <div v-if="tasks.last_page > 1" class="flex items-center justify-between text-sm text-slate-500">
            <span>{{ tasks.from }}–{{ tasks.to }} of {{ tasks.total }}</span>
            <div class="flex gap-2">
                <Button
                    v-for="link in tasks.links"
                    :key="link.label"
                    variant="outline"
                    size="sm"
                    :disabled="!link.url"
                    :class="{ 'font-semibold': link.active }"
                    @click="link.url && router.get(link.url)"
                    v-html="link.label"
                />
            </div>
        </div>
    </div>
</template>
