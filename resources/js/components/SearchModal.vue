<script setup lang="ts">
import { computed, nextTick, ref, watch, type Component } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    Building2,
    CheckSquare,
    LayoutDashboard,
    ListChecks,
    Plus,
    Search,
    Settings2,
    Store as StoreIcon,
    TrendingUp,
    Upload,
    User,
    Users,
} from 'lucide-vue-next';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { useRecentItems } from '@/composables/useRecentItems';
import { useSearch, type SearchResult, type SearchResultType } from '@/composables/useSearch';
import { toUrl } from '@/lib/utils';
import { dashboard } from '@/routes';
import dealerships from '@/routes/dealerships';
import dealershipImport from '@/routes/dealerships/import';
import sales from '@/routes/sales';
import { edit as profileEdit } from '@/routes/settings/profile';
import tasks from '@/routes/tasks';
import users from '@/routes/users';

interface PaletteItem {
    key: string;
    label: string;
    subtitle: string | null;
    url: string;
    icon: Component;
    iconClass: string;
    iconWrapperClass: string;
    result?: SearchResult;
}

interface PaletteGroup {
    label: string;
    items: PaletteItem[];
}

const open = defineModel<boolean>('open', { default: false });

const page = usePage<{ auth: { roles?: string[] } }>();
const { query, results, loading, clear } = useSearch();
const { recentItems, recordRecentItem } = useRecentItems();
const inputRef = ref<HTMLInputElement | null>(null);
const resultsRef = ref<HTMLDivElement | null>(null);
const activeIndex = ref(-1);

const resultStyles: Record<SearchResultType, { group: string; icon: Component; iconClass: string; iconWrapperClass: string }> = {
    dealership: { group: 'Dealerships', icon: Building2, iconClass: 'text-primary', iconWrapperClass: 'bg-primary/10' },
    store: { group: 'Stores', icon: StoreIcon, iconClass: 'text-emerald-600 dark:text-emerald-400', iconWrapperClass: 'bg-emerald-500/10' },
    contact: { group: 'Contacts', icon: User, iconClass: 'text-secondary-foreground', iconWrapperClass: 'bg-secondary' },
    task: { group: 'Tasks', icon: CheckSquare, iconClass: 'text-amber-600 dark:text-amber-400', iconWrapperClass: 'bg-amber-500/10' },
};

const resultTypeOrder: SearchResultType[] = ['dealership', 'store', 'contact', 'task'];

function toPaletteItem(result: SearchResult): PaletteItem {
    const style = resultStyles[result.type];

    return {
        key: `${result.type}-${result.id}`,
        label: result.label,
        subtitle: [result.subtitle, result.meta].filter(Boolean).join(' · ') || null,
        url: result.url,
        icon: style.icon,
        iconClass: style.iconClass,
        iconWrapperClass: style.iconWrapperClass,
        result,
    };
}

function shortcut(key: string, label: string, url: string, icon: Component): PaletteItem {
    return {
        key: `shortcut-${key}`,
        label,
        subtitle: null,
        url,
        icon,
        iconClass: 'text-muted-foreground',
        iconWrapperClass: 'bg-muted',
    };
}

const isSearching = computed(() => query.value.trim().length >= 2);

const groups = computed<PaletteGroup[]>(() => {
    if (isSearching.value) {
        return resultTypeOrder
            .map((type) => ({
                label: resultStyles[type].group,
                items: results.value.filter((r) => r.type === type).map(toPaletteItem),
            }))
            .filter((group) => group.items.length > 0);
    }

    const goTo = [
        shortcut('dashboard', 'Dashboard', toUrl(dashboard()), LayoutDashboard),
        shortcut('tasks', 'Tasks', toUrl(tasks.index()), ListChecks),
        shortcut('sales', 'Sales', toUrl(sales.index()), TrendingUp),
    ];

    if (page.props.auth?.roles?.includes('super_admin')) {
        goTo.push(shortcut('users', 'Users', toUrl(users.index()), Users));
    }

    goTo.push(shortcut('settings', 'Settings', toUrl(profileEdit()), Settings2));

    return [
        { label: 'Recent', items: recentItems.value.map(toPaletteItem) },
        {
            label: 'Quick actions',
            items: [
                shortcut('new-dealership', 'New dealership', toUrl(dealerships.create()), Plus),
                shortcut('import-dealerships', 'Import dealerships', toUrl(dealershipImport.create()), Upload),
            ],
        },
        { label: 'Go to', items: goTo },
    ].filter((group) => group.items.length > 0);
});

const flatItems = computed(() => groups.value.flatMap((group) => group.items));

watch(open, async (val) => {
    if (val) {
        activeIndex.value = -1;
        await nextTick();
        inputRef.value?.focus();
    } else {
        clear();
    }
});

watch(flatItems, () => {
    activeIndex.value = -1;
});

function navigate(item: PaletteItem) {
    if (item.result) {
        recordRecentItem(item.result);
    }

    router.visit(item.url);
    open.value = false;
}

function scrollActiveIntoView() {
    nextTick(() => {
        const el = resultsRef.value?.querySelector<HTMLElement>('[data-active="true"]');
        el?.scrollIntoView({ block: 'nearest' });
    });
}

function onKeydown(e: KeyboardEvent) {
    if (!flatItems.value.length) {
        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex.value = Math.min(activeIndex.value + 1, flatItems.value.length - 1);
        scrollActiveIntoView();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
        scrollActiveIntoView();
    } else if (e.key === 'Enter' && activeIndex.value >= 0) {
        e.preventDefault();
        navigate(flatItems.value[activeIndex.value]);
    }
}

function flatIndex(item: PaletteItem): number {
    return flatItems.value.findIndex((candidate) => candidate.key === item.key);
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            class="overflow-hidden p-0 shadow-2xl sm:max-w-xl"
            :show-close-button="false"
        >
            <DialogTitle class="sr-only">Search</DialogTitle>

            <!-- Search Input -->
            <div class="flex items-center border-b px-4">
                <Search class="mr-3 h-4 w-4 shrink-0 text-muted-foreground" />
                <input
                    ref="inputRef"
                    v-model="query"
                    type="text"
                    placeholder="Search dealerships, stores, contacts, tasks..."
                    class="h-12 w-full bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                    @keydown="onKeydown"
                />
                <div v-if="loading" class="ml-2 h-4 w-4 shrink-0">
                    <svg
                        class="animate-spin text-muted-foreground"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        />
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                        />
                    </svg>
                </div>
            </div>

            <!-- Results -->
            <div
                v-if="flatItems.length > 0"
                ref="resultsRef"
                class="max-h-[400px] overflow-y-auto p-2"
            >
                <template v-for="(group, groupIndex) in groups" :key="group.label">
                    <div
                        class="mb-1 px-2 py-1.5 text-xs font-medium text-muted-foreground"
                        :class="{ 'mt-2': groupIndex > 0 }"
                    >
                        {{ group.label }}
                    </div>
                    <button
                        v-for="item in group.items"
                        :key="item.key"
                        :data-active="activeIndex === flatIndex(item)"
                        class="flex w-full items-center gap-3 rounded-md px-2 py-2 text-left text-sm transition-colors"
                        :class="
                            activeIndex === flatIndex(item)
                                ? 'bg-accent text-accent-foreground'
                                : 'hover:bg-accent hover:text-accent-foreground'
                        "
                        @click="navigate(item)"
                        @mouseenter="activeIndex = flatIndex(item)"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md"
                            :class="item.iconWrapperClass"
                        >
                            <component :is="item.icon" class="h-4 w-4" :class="item.iconClass" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-medium">
                                {{ item.label }}
                            </div>
                            <div
                                v-if="item.subtitle"
                                class="truncate text-xs text-muted-foreground"
                            >
                                {{ item.subtitle }}
                            </div>
                        </div>
                    </button>
                </template>
            </div>

            <!-- Empty state -->
            <div
                v-else-if="isSearching && !loading"
                class="px-4 py-10 text-center text-sm text-muted-foreground"
            >
                No results for "{{ query }}"
            </div>

            <!-- Footer hint -->
            <div
                class="flex items-center justify-end gap-3 border-t px-4 py-2 text-xs text-muted-foreground"
            >
                <span class="flex items-center gap-1">
                    <kbd class="rounded border bg-muted px-1.5 py-0.5 font-mono text-[10px]">↑↓</kbd>
                    navigate
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="rounded border bg-muted px-1.5 py-0.5 font-mono text-[10px]">↵</kbd>
                    open
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="rounded border bg-muted px-1.5 py-0.5 font-mono text-[10px]">Esc</kbd>
                    close
                </span>
            </div>
        </DialogContent>
    </Dialog>
</template>
