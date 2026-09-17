<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import ShellSidebar from '@/components/shell/ShellSidebar.vue';
import ShellTopbar from '@/components/shell/ShellTopbar.vue';
import { Sheet, SheetContent, SheetDescription, SheetTitle } from '@/components/ui/sheet';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const storageKey = 'shell-sidebar-collapsed';
const collapsed = ref(false);
const mobileOpen = ref(false);

onMounted(() => {
    try {
        collapsed.value = localStorage.getItem(storageKey) === '1';
    } catch {
        collapsed.value = false;
    }
});

function toggleCollapsed(): void {
    collapsed.value = !collapsed.value;

    try {
        localStorage.setItem(storageKey, collapsed.value ? '1' : '0');
    } catch {
        // Storage can be unavailable (private mode); the toggle still works for this visit.
    }
}

const removeNavigateListener = router.on('navigate', () => {
    mobileOpen.value = false;
});

onUnmounted(removeNavigateListener);
</script>

<template>
    <div class="min-h-dvh bg-background">
        <div class="flex min-h-dvh">
            <aside
                class="sticky top-0 hidden h-dvh shrink-0 border-r border-sidebar-border bg-sidebar transition-[width] duration-200 lg:block"
                :class="collapsed ? 'w-[72px]' : 'w-[272px]'"
            >
                <ShellSidebar :collapsed="collapsed" @toggle-collapse="toggleCollapsed" />
            </aside>

            <Sheet v-model:open="mobileOpen">
                <SheetContent side="left" class="w-[288px] bg-sidebar p-0 [&>button]:hidden">
                    <SheetTitle class="sr-only">Navigation</SheetTitle>
                    <SheetDescription class="sr-only">Main navigation</SheetDescription>
                    <ShellSidebar hide-collapse @navigate="mobileOpen = false" />
                </SheetContent>
            </Sheet>

            <div class="flex min-w-0 flex-1 flex-col">
                <ShellTopbar :breadcrumbs="breadcrumbs" @open-menu="mobileOpen = true" />
                <main class="min-w-0 flex-1">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
