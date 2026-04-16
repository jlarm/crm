<script setup lang="ts">
import TaskCard from '@/components/tasks/TaskCard.vue';
import TaskFormModal from '@/components/tasks/TaskFormModal.vue';
import { Button } from '@/components/ui/button';
import type { Dealership } from '@/pages/Dealership/types';
import type { FilterOption, Task } from '@/pages/Tasks/types';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface User {
    id: number;
    name: string;
}

const props = defineProps<{
    dealership: Dealership;
    tasks: Task[];
    allUsers: User[];
    taskFilterOptions: {
        types: FilterOption[];
        priorities: FilterOption[];
    };
}>();

const page = usePage();
const currentUserId = computed(() => (page.props.auth as { user: { id: number } }).user.id);

const isFormOpen = ref(false);
const editingTask = ref<Task | null>(null);
const showCompleted = ref(false);

const incompleteTasks = computed(() => props.tasks.filter((t) => !t.isCompleted));
const completedTasks = computed(() => props.tasks.filter((t) => t.isCompleted));

function openCreate(): void {
    editingTask.value = null;
    isFormOpen.value = true;
}

function openEdit(task: Task): void {
    editingTask.value = task;
    isFormOpen.value = true;
}
</script>

<template>
    <div class="mx-auto mt-6 w-full">
        <TaskFormModal
            v-model:open="isFormOpen"
            :task="editingTask"
            :all-users="allUsers"
            :types="taskFilterOptions.types"
            :priorities="taskFilterOptions.priorities"
            :dealership-id="dealership.id"
            :current-user-id="currentUserId"
        />

        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Tasks
                <span v-if="incompleteTasks.length > 0" class="ml-2 text-sm font-normal text-slate-400">
                    {{ incompleteTasks.length }} open
                </span>
            </h2>
            <Button @click="openCreate">Add Task</Button>
        </div>

        <div class="mt-6 space-y-2">
            <p
                v-if="incompleteTasks.length === 0 && completedTasks.length === 0"
                class="text-sm text-slate-400"
            >
                No tasks for this dealership yet.
            </p>

            <TaskCard
                v-for="task in incompleteTasks"
                :key="task.id"
                :task="task"
                @edit="openEdit"
            />

            <template v-if="completedTasks.length > 0">
                <button
                    type="button"
                    class="mt-4 flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600"
                    @click="showCompleted = !showCompleted"
                >
                    <span>{{ showCompleted ? '▾' : '▸' }}</span>
                    {{ completedTasks.length }} completed
                </button>

                <template v-if="showCompleted">
                    <TaskCard
                        v-for="task in completedTasks"
                        :key="task.id"
                        :task="task"
                        @edit="openEdit"
                    />
                </template>
            </template>
        </div>
    </div>
</template>
