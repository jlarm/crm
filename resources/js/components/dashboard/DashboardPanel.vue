<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <section class="flex min-w-0 flex-col rounded-xl border border-border bg-card">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4">
            <div class="min-w-0">
                <h2 class="text-[15px] font-semibold text-foreground">{{ title }}</h2>
                <p v-if="description" class="mt-0.5 text-xs text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </header>
        <div class="min-w-0 flex-1">
            <slot />
        </div>
    </section>
</template>
