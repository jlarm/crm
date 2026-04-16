<script setup lang="ts">
defineProps<{
    label: string;
    value: string;
    sub?: string;
    trend?: 'up' | 'down' | 'neutral';
}>();
</script>

<template>
    <div class="flex flex-col gap-1 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
            {{ label }}
        </p>
        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
            {{ value }}
        </p>
        <p v-if="sub" class="text-xs text-slate-500 dark:text-slate-400">
            {{ sub }}
        </p>
    </div>
</template>
