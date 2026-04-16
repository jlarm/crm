<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, Circle, Clock } from 'lucide-vue-next';

defineProps<{
    stats: {
        incomplete: number;
        overdue: number;
        dueToday: number;
        completedThisWeek: number;
    };
}>();
</script>

<template>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <Link
            href="/tasks?filter=incomplete"
            class="group flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:shadow-sm dark:border-slate-800 dark:bg-slate-950"
        >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                <Circle class="h-5 w-5 text-slate-500 dark:text-slate-400" />
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                    {{ stats.incomplete }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Open tasks</p>
            </div>
        </Link>

        <Link
            href="/tasks?filter=overdue"
            class="group flex items-center gap-4 rounded-xl border p-4 transition hover:shadow-sm"
            :class="
                stats.overdue > 0
                    ? 'border-red-200 bg-red-50 hover:border-red-300 dark:border-red-900/50 dark:bg-red-900/10'
                    : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950'
            "
        >
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                :class="
                    stats.overdue > 0
                        ? 'bg-red-100 dark:bg-red-900/30'
                        : 'bg-slate-100 dark:bg-slate-800'
                "
            >
                <AlertCircle
                    class="h-5 w-5"
                    :class="
                        stats.overdue > 0
                            ? 'text-red-600 dark:text-red-400'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                />
            </div>
            <div>
                <p
                    class="text-2xl font-bold"
                    :class="
                        stats.overdue > 0
                            ? 'text-red-700 dark:text-red-400'
                            : 'text-slate-900 dark:text-slate-100'
                    "
                >
                    {{ stats.overdue }}
                </p>
                <p
                    class="text-xs"
                    :class="
                        stats.overdue > 0
                            ? 'text-red-500 dark:text-red-500'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                >
                    Overdue
                </p>
            </div>
        </Link>

        <Link
            href="/tasks?filter=incomplete"
            class="group flex items-center gap-4 rounded-xl border p-4 transition hover:shadow-sm"
            :class="
                stats.dueToday > 0
                    ? 'border-amber-200 bg-amber-50 hover:border-amber-300 dark:border-amber-900/50 dark:bg-amber-900/10'
                    : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950'
            "
        >
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                :class="
                    stats.dueToday > 0
                        ? 'bg-amber-100 dark:bg-amber-900/30'
                        : 'bg-slate-100 dark:bg-slate-800'
                "
            >
                <Clock
                    class="h-5 w-5"
                    :class="
                        stats.dueToday > 0
                            ? 'text-amber-600 dark:text-amber-400'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                />
            </div>
            <div>
                <p
                    class="text-2xl font-bold"
                    :class="
                        stats.dueToday > 0
                            ? 'text-amber-700 dark:text-amber-400'
                            : 'text-slate-900 dark:text-slate-100'
                    "
                >
                    {{ stats.dueToday }}
                </p>
                <p
                    class="text-xs"
                    :class="
                        stats.dueToday > 0
                            ? 'text-amber-500 dark:text-amber-500'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                >
                    Due today
                </p>
            </div>
        </Link>

        <Link
            href="/tasks?filter=completed"
            class="group flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:shadow-sm dark:border-slate-800 dark:bg-slate-950"
        >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                <CheckCircle2 class="h-5 w-5 text-green-600 dark:text-green-400" />
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                    {{ stats.completedThisWeek }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Done this week</p>
            </div>
        </Link>
    </div>
</template>
