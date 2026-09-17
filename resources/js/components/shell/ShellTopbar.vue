<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronsRight, Menu, Search } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import ThemeToggle from '@/components/shell/ThemeToggle.vue';
import { useSearchModal } from '@/composables/useSearchModal';
import type { ShellCrumb } from '@/composables/useShellNavigation';
import { useShellNavigation } from '@/composables/useShellNavigation';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    /** Explicit crumbs from the page; the route-derived trail is used otherwise. */
    breadcrumbs?: BreadcrumbItem[];
}>();

const emit = defineEmits<{
    (event: 'open-menu'): void;
}>();

const { show: showSearch } = useSearchModal();
const { breadcrumbs: derivedCrumbs } = useShellNavigation();

const crumbs = computed<ShellCrumb[]>(() => {
    if (!props.breadcrumbs?.length) {
        return derivedCrumbs.value;
    }

    const root = derivedCrumbs.value[0];

    return root ? [root, ...props.breadcrumbs.filter((crumb) => crumb.title !== root.title)] : props.breadcrumbs;
});

const shortcutKey = ref('Ctrl');

onMounted(() => {
    if (/Mac|iPhone|iPad|iPod/.test(navigator.platform)) {
        shortcutKey.value = '⌘';
    }
});
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-border bg-background/85 px-4 backdrop-blur-md sm:px-6"
    >
        <button
            type="button"
            class="inline-flex size-9 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none lg:hidden"
            aria-label="Open navigation"
            @click="emit('open-menu')"
        >
            <Menu class="size-4" />
        </button>

        <nav aria-label="Breadcrumb" class="min-w-0 flex-1">
            <ol class="flex min-w-0 items-center gap-2 text-[15px]">
                <template v-for="(crumb, index) in crumbs" :key="`${index}-${crumb.title}`">
                    <li v-if="index > 0" aria-hidden="true" class="shrink-0 text-muted-foreground/60">
                        <ChevronsRight class="size-4" />
                    </li>
                    <li class="flex min-w-0 items-center" :class="index < crumbs.length - 1 ? 'hidden shrink-0 sm:flex' : ''">
                        <Link
                            v-if="index < crumbs.length - 1"
                            :href="crumb.href"
                            class="flex items-center gap-2 rounded-md text-muted-foreground transition-colors hover:text-foreground"
                        >
                            <component :is="crumb.icon" v-if="crumb.icon" class="size-4" />
                            {{ crumb.title }}
                        </Link>
                        <span
                            v-else
                            aria-current="page"
                            class="flex min-w-0 items-center gap-2 font-medium text-foreground"
                        >
                            <component :is="crumb.icon" v-if="crumb.icon" class="size-4 shrink-0 text-muted-foreground" />
                            <span class="truncate">{{ crumb.title }}</span>
                        </span>
                    </li>
                </template>
            </ol>
        </nav>

        <button
            type="button"
            class="hidden h-10 w-72 items-center gap-2.5 rounded-lg border border-border bg-card px-3 text-sm text-muted-foreground transition-colors hover:border-foreground/20 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none md:flex xl:w-96"
            @click="showSearch"
        >
            <Search class="size-4" />
            <span>Search dealerships, contacts…</span>
            <kbd
                class="ml-auto rounded-md border border-border bg-muted px-1.5 py-0.5 font-mono text-[11px] text-muted-foreground"
            >
                {{ shortcutKey }}K
            </kbd>
        </button>
        <button
            type="button"
            class="inline-flex size-9 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none md:hidden"
            aria-label="Search"
            @click="showSearch"
        >
            <Search class="size-4" />
        </button>

        <ThemeToggle cycle />
    </header>
</template>
