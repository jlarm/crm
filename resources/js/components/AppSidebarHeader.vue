<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useSearchModal } from '@/composables/useSearchModal';
import type { BreadcrumbItem } from '@/types';
import { Search } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { show: showSearch } = useSearchModal();

const isMac = ref(false);

onMounted(() => {
    isMac.value =
        typeof navigator !== 'undefined' &&
        /Mac|iPhone|iPad|iPod/.test(navigator.platform);
});

const shortcutKey = computed(() => (isMac.value ? '⌘' : 'Ctrl'));
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex flex-1 items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <Button
            variant="outline"
            size="sm"
            class="hidden h-9 w-64 justify-start gap-2 px-3 text-muted-foreground sm:flex"
            @click="showSearch"
        >
            <Search class="h-4 w-4" />
            <span class="text-sm">Search...</span>
            <kbd
                class="ml-auto inline-flex items-center gap-0.5 rounded border bg-muted px-1.5 py-0.5 font-mono text-[10px] text-muted-foreground"
            >
                <span>{{ shortcutKey }}</span>
                <span>K</span>
            </kbd>
        </Button>

        <Button
            variant="ghost"
            size="icon"
            class="h-9 w-9 sm:hidden"
            @click="showSearch"
        >
            <Search class="h-4 w-4" />
            <span class="sr-only">Search</span>
        </Button>
    </header>
</template>
