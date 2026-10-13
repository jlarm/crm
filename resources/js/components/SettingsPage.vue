<script setup lang="ts">
import { settingsPages } from '@/composables/useShellNavigation';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Page container for the settings screens: tabs to move between them, then a readable
 * width with each section (a direct child of the slot) separated by a rule.
 */
const page = usePage();

function pathOf(url: string): string {
    return new URL(url, window.location.origin).pathname;
}

const currentPath = computed(() => pathOf(page.url));

function isCurrent(href: string): boolean {
    return pathOf(href) === currentPath.value;
}
</script>

<template>
    <div class="mx-auto w-full max-w-2xl px-4 py-8 sm:px-6 lg:py-10">
        <nav class="mb-8 flex gap-6 border-b border-border text-sm font-medium" aria-label="Settings">
            <Link
                v-for="settingsPage in settingsPages"
                :key="settingsPage.href"
                :href="settingsPage.href"
                class="-mb-px border-b-2 pb-3 transition-colors"
                :class="isCurrent(settingsPage.href) ? 'border-orange-500 text-orange-600' : 'border-transparent text-muted-foreground hover:text-foreground'"
                :aria-current="isCurrent(settingsPage.href) ? 'page' : undefined"
            >
                {{ settingsPage.title }}
            </Link>
        </nav>

        <div class="space-y-10 [&>*+*]:border-t [&>*+*]:border-border [&>*+*]:pt-10">
            <slot />
        </div>
    </div>
</template>
