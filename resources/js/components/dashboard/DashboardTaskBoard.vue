<script setup lang="ts">
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import { Button } from '@/components/ui/button';
import type { Task } from '@/pages/Tasks/types';
import { Link, router } from '@inertiajs/vue3';
import { Check, Plus } from 'lucide-vue-next';

defineProps<{
    tasks: Task[];
}>();

const emit = defineEmits<{
    (event: 'createTask'): void;
    (event: 'editTask', task: Task): void;
}>();

const typeDot: Record<string, string> = {
    call: 'bg-info',
    email: 'bg-chart-1',
    demo: 'bg-success',
    follow_up: 'bg-warning',
    proposal: 'bg-destructive',
    other: 'bg-muted-foreground',
};

function toggleComplete(task: Task): void {
    router.patch(`/tasks/${task.id}/complete`, {}, { preserveScroll: true });
}

function startOfToday(): Date {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return today;
}

function dueLabel(task: Task): string {
    if (!task.dueDate) {
        return 'No due date';
    }

    const due = new Date(task.dueDate);
    due.setHours(0, 0, 0, 0);
    const today = startOfToday();
    const days = Math.round((due.getTime() - today.getTime()) / 86_400_000);

    if (days < 0) {
        return days === -1 ? 'Due yesterday' : `Due ${Math.abs(days)} days ago`;
    }
    if (days === 0) {
        return 'Due today';
    }
    if (days === 1) {
        return 'Due tomorrow';
    }
    if (days < 7) {
        return `Due ${due.toLocaleDateString('en-US', { weekday: 'long' })}`;
    }

    return `Due ${due.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
}

function isDueToday(task: Task): boolean {
    if (!task.dueDate || task.isOverdue) {
        return false;
    }

    const due = new Date(task.dueDate);
    due.setHours(0, 0, 0, 0);

    return due.getTime() === startOfToday().getTime();
}

function dueClass(task: Task): string {
    if (task.isOverdue) {
        return 'text-destructive';
    }
    if (isDueToday(task)) {
        return 'text-warning';
    }

    return 'text-muted-foreground';
}
</script>

<template>
    <DashboardPanel
        title="Today's work"
        :description="tasks.length ? `${tasks.length} open tasks, most urgent first` : 'Your upcoming tasks'"
    >
        <template #actions>
            <Link
                href="/tasks"
                class="text-xs text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
            >
                View all
            </Link>
            <Button type="button" variant="outline" size="sm" @click="emit('createTask')">
                <Plus class="size-4" />
                New task
            </Button>
        </template>

        <div
            v-if="tasks.length === 0"
            class="flex flex-col items-center gap-3 px-6 py-12 text-center"
        >
            <p class="text-sm text-muted-foreground">
                Nothing is scheduled. Add a task to plan your day.
            </p>
            <Button type="button" variant="outline" size="sm" @click="emit('createTask')">
                <Plus class="size-4" />
                New task
            </Button>
        </div>

        <ul v-else class="divide-y divide-border">
            <li
                v-for="task in tasks"
                :key="task.id"
                class="group flex items-center gap-3 px-5 py-3 transition-colors hover:bg-accent/40"
            >
                <button
                    type="button"
                    class="flex size-[18px] shrink-0 items-center justify-center rounded-md border border-input text-transparent transition-colors hover:border-success hover:bg-success/10 hover:text-success focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :aria-label="`Mark ${task.title} complete`"
                    @click="toggleComplete(task)"
                >
                    <Check class="size-3" />
                </button>

                <button
                    type="button"
                    class="grid min-w-0 flex-1 grid-cols-1 items-center gap-x-4 gap-y-0.5 text-left focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none sm:grid-cols-[minmax(0,1fr)_9rem_7.5rem]"
                    @click="emit('editTask', task)"
                >
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-foreground">
                            {{ task.title }}
                        </span>
                        <span
                            v-if="task.dealership"
                            class="block truncate text-xs text-muted-foreground"
                        >
                            {{ task.dealership.name }}
                        </span>
                    </span>
                    <span class="flex items-center gap-2 text-xs text-muted-foreground">
                        <span
                            class="size-1.5 shrink-0 rounded-full"
                            :class="typeDot[task.type] ?? typeDot.other"
                        />
                        {{ task.typeLabel }}
                    </span>
                    <span class="text-xs font-medium sm:text-right" :class="dueClass(task)">
                        {{ dueLabel(task) }}
                    </span>
                </button>
            </li>
        </ul>
    </DashboardPanel>
</template>
