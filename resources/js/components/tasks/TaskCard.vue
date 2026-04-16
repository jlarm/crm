<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Task } from '@/pages/Tasks/types';
import { router } from '@inertiajs/vue3';
import {
    Building2,
    Calendar,
    CheckCircle2,
    Circle,
    Clock,
    MoreVertical,
    User,
} from 'lucide-vue-next';

const props = defineProps<{
    task: Task;
}>();

const emit = defineEmits<{
    (event: 'edit', task: Task): void;
}>();

const priorityClasses: Record<string, string> = {
    high: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    low: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
};

const typeClasses: Record<string, string> = {
    call: 'bg-blue-100 text-blue-700',
    email: 'bg-purple-100 text-purple-700',
    demo: 'bg-green-100 text-green-700',
    follow_up: 'bg-orange-100 text-orange-700',
    proposal: 'bg-pink-100 text-pink-700',
    other: 'bg-slate-100 text-slate-600',
};

function toggleComplete(): void {
    router.patch(`/tasks/${props.task.id}/complete`, {}, { preserveScroll: true });
}

function deleteTask(): void {
    if (!window.confirm('Delete this task?')) {
        return;
    }
    router.delete(`/tasks/${props.task.id}`, { preserveScroll: true });
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <div
        class="group flex items-start gap-3 rounded-xl border p-4 transition-all"
        :class="[
            task.isCompleted
                ? 'border-slate-200 bg-slate-50 opacity-60 dark:border-slate-800 dark:bg-slate-900/50'
                : task.isOverdue
                  ? 'border-red-200 bg-red-50/50 dark:border-red-900/50 dark:bg-red-900/10'
                  : 'border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm dark:border-slate-800 dark:bg-slate-950',
        ]"
    >
        <button
            type="button"
            class="mt-0.5 shrink-0 transition-colors"
            :class="task.isCompleted ? 'text-green-500' : 'text-slate-300 hover:text-slate-400'"
            @click="toggleComplete"
        >
            <CheckCircle2 v-if="task.isCompleted" class="h-5 w-5" />
            <Circle v-else class="h-5 w-5" />
        </button>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
                <p
                    class="text-sm font-medium leading-snug"
                    :class="
                        task.isCompleted
                            ? 'text-slate-400 line-through dark:text-slate-600'
                            : 'text-slate-900 dark:text-slate-100'
                    "
                >
                    {{ task.title }}
                </p>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-6 w-6 shrink-0 opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <MoreVertical class="h-3.5 w-3.5" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="emit('edit', task)">Edit</DropdownMenuItem>
                        <DropdownMenuItem @click="toggleComplete">
                            {{ task.isCompleted ? 'Reopen' : 'Mark complete' }}
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            class="text-red-600 focus:text-red-600"
                            @click="deleteTask"
                        >
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <p
                v-if="task.description"
                class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400"
            >
                {{ task.description }}
            </p>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="typeClasses[task.type] ?? 'bg-slate-100 text-slate-600'"
                >
                    {{ task.typeLabel }}
                </span>
                <span
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="priorityClasses[task.priority] ?? 'bg-slate-100 text-slate-600'"
                >
                    {{ task.priorityLabel }}
                </span>

                <span
                    v-if="task.dueDate"
                    class="inline-flex items-center gap-1 text-xs"
                    :class="
                        task.isOverdue
                            ? 'text-red-600 dark:text-red-400'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                >
                    <Clock v-if="task.isOverdue" class="h-3 w-3" />
                    <Calendar v-else class="h-3 w-3" />
                    {{ task.isOverdue ? 'Overdue · ' : '' }}{{ formatDate(task.dueDate) }}
                </span>
            </div>

            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-400 dark:text-slate-500">
                <span v-if="task.assignedTo" class="flex items-center gap-1">
                    <User class="h-3 w-3" />
                    {{ task.assignedTo.name }}
                </span>
                <a
                    v-if="task.dealership"
                    :href="`/dealerships/${task.dealership.id}`"
                    class="flex items-center gap-1 hover:text-slate-600 dark:hover:text-slate-300"
                >
                    <Building2 class="h-3 w-3" />
                    {{ task.dealership.name }}
                </a>
            </div>
        </div>
    </div>
</template>
