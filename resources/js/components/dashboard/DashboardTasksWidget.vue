<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Task } from '@/pages/Tasks/types';
import { Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Circle,
    Clock,
    Plus,
} from 'lucide-vue-next';

defineProps<{
    tasks: Task[];
}>();

const emit = defineEmits<{
    (event: 'createTask'): void;
}>();

const typeColors: Record<string, string> = {
    call: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    email: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    demo: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    follow_up: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    proposal: 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
    other: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
};

function toggleComplete(task: Task): void {
    router.patch(`/tasks/${task.id}/complete`, {}, { preserveScroll: true });
}

function formatDueDate(date: string): string {
    const d = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);

    if (d < today) {
        return 'Overdue';
    }
    if (d.toDateString() === today.toDateString()) {
        return 'Today';
    }
    if (d.toDateString() === tomorrow.toDateString()) {
        return 'Tomorrow';
    }

    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-slate-800">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                My Tasks
            </h2>
            <div class="flex items-center gap-2">
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-6 w-6"
                    title="New task"
                    @click="emit('createTask')"
                >
                    <Plus class="h-3.5 w-3.5" />
                </Button>
                <Link
                    href="/tasks"
                    class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                >
                    View all
                </Link>
            </div>
        </div>

        <div v-if="tasks.length === 0" class="px-4 py-10 text-center">
            <CheckCircle2 class="mx-auto mb-2 h-8 w-8 text-slate-300 dark:text-slate-700" />
            <p class="text-sm text-slate-400">All caught up!</p>
            <Button
                variant="ghost"
                size="sm"
                class="mt-2 text-xs"
                @click="emit('createTask')"
            >
                Add a task
            </Button>
        </div>

        <div v-else class="divide-y divide-slate-100 dark:divide-slate-800/60">
            <div
                v-for="task in tasks"
                :key="task.id"
                class="group flex items-start gap-3 px-4 py-3 transition hover:bg-slate-50 dark:hover:bg-slate-900/50"
                :class="task.isOverdue ? 'bg-red-50/50 dark:bg-red-900/5' : ''"
            >
                <button
                    type="button"
                    class="mt-0.5 shrink-0 text-slate-300 transition hover:text-slate-400"
                    @click="toggleComplete(task)"
                >
                    <CheckCircle2 v-if="task.isCompleted" class="h-4 w-4 text-green-500" />
                    <Circle v-else class="h-4 w-4" />
                </button>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200">
                        {{ task.title }}
                    </p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center rounded-full px-1.5 py-0.5 text-xs font-medium"
                            :class="typeColors[task.type] ?? typeColors.other"
                        >
                            {{ task.typeLabel }}
                        </span>

                        <span
                            v-if="task.dueDate"
                            class="inline-flex items-center gap-1 text-xs"
                            :class="
                                task.isOverdue
                                    ? 'text-red-600 dark:text-red-400'
                                    : formatDueDate(task.dueDate) === 'Today'
                                      ? 'text-amber-600 dark:text-amber-400'
                                      : 'text-slate-400'
                            "
                        >
                            <AlertCircle v-if="task.isOverdue" class="h-3 w-3" />
                            <Clock
                                v-else-if="formatDueDate(task.dueDate) === 'Today'"
                                class="h-3 w-3"
                            />
                            <Calendar v-else class="h-3 w-3" />
                            {{ formatDueDate(task.dueDate) }}
                        </span>

                        <span
                            v-if="task.dealership"
                            class="truncate text-xs text-slate-400 dark:text-slate-500"
                        >
                            {{ task.dealership.name }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="tasks.length > 0"
            class="border-t border-slate-100 px-4 py-2 dark:border-slate-800/60"
        >
            <Link
                href="/tasks"
                class="block text-center text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
            >
                View all tasks →
            </Link>
        </div>
    </div>
</template>
