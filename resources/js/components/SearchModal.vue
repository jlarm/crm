<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Building2, Search, User } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { useSearch, type SearchResult } from '@/composables/useSearch';

const open = defineModel<boolean>('open', { default: false });

const { query, results, loading, clear } = useSearch();
const inputRef = ref<HTMLInputElement | null>(null);
const resultsRef = ref<HTMLDivElement | null>(null);
const activeIndex = ref(-1);

const dealershipResults = computed(() =>
    results.value.filter((r) => r.type === 'dealership'),
);
const contactResults = computed(() =>
    results.value.filter((r) => r.type === 'contact'),
);

watch(open, async (val) => {
    if (val) {
        activeIndex.value = -1;
        await nextTick();
        inputRef.value?.focus();
    } else {
        clear();
    }
});

watch(results, () => {
    activeIndex.value = -1;
});

function navigate(result: SearchResult) {
    router.visit(result.url);
    open.value = false;
}

function scrollActiveIntoView() {
    nextTick(() => {
        const el = resultsRef.value?.querySelector<HTMLElement>('[data-active="true"]');
        el?.scrollIntoView({ block: 'nearest' });
    });
}

function onKeydown(e: KeyboardEvent) {
    if (!results.value.length) {
        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex.value = Math.min(activeIndex.value + 1, results.value.length - 1);
        scrollActiveIntoView();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
        scrollActiveIntoView();
    } else if (e.key === 'Enter' && activeIndex.value >= 0) {
        e.preventDefault();
        navigate(results.value[activeIndex.value]);
    }
}

function flatIndex(result: SearchResult): number {
    return results.value.findIndex((r) => r.type === result.type && r.id === result.id);
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
                    placeholder="Search dealerships and contacts..."
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
                v-if="results.length > 0"
                ref="resultsRef"
                class="max-h-[400px] overflow-y-auto p-2"
            >
                <!-- Dealerships -->
                <template v-if="dealershipResults.length > 0">
                    <div
                        class="mb-1 px-2 py-1.5 text-xs font-medium text-muted-foreground"
                    >
                        Dealerships
                    </div>
                    <button
                        v-for="result in dealershipResults"
                        :key="result.id"
                        :data-active="activeIndex === flatIndex(result)"
                        class="flex w-full items-center gap-3 rounded-md px-2 py-2 text-left text-sm transition-colors"
                        :class="
                            activeIndex === flatIndex(result)
                                ? 'bg-accent text-accent-foreground'
                                : 'hover:bg-accent hover:text-accent-foreground'
                        "
                        @click="navigate(result)"
                        @mouseenter="activeIndex = flatIndex(result)"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-primary/10"
                        >
                            <Building2 class="h-4 w-4 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-medium">
                                {{ result.label }}
                            </div>
                            <div
                                v-if="result.subtitle || result.meta"
                                class="truncate text-xs text-muted-foreground"
                            >
                                {{ [result.subtitle, result.meta].filter(Boolean).join(' · ') }}
                            </div>
                        </div>
                    </button>
                </template>

                <!-- Contacts -->
                <template v-if="contactResults.length > 0">
                    <div
                        class="mb-1 mt-2 px-2 py-1.5 text-xs font-medium text-muted-foreground"
                        :class="{ 'mt-2': dealershipResults.length > 0 }"
                    >
                        Contacts
                    </div>
                    <button
                        v-for="result in contactResults"
                        :key="result.id"
                        :data-active="activeIndex === flatIndex(result)"
                        class="flex w-full items-center gap-3 rounded-md px-2 py-2 text-left text-sm transition-colors"
                        :class="
                            activeIndex === flatIndex(result)
                                ? 'bg-accent text-accent-foreground'
                                : 'hover:bg-accent hover:text-accent-foreground'
                        "
                        @click="navigate(result)"
                        @mouseenter="activeIndex = flatIndex(result)"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-secondary"
                        >
                            <User class="h-4 w-4 text-secondary-foreground" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-medium">
                                {{ result.label }}
                            </div>
                            <div
                                v-if="result.subtitle || result.meta"
                                class="truncate text-xs text-muted-foreground"
                            >
                                {{ [result.subtitle, result.meta].filter(Boolean).join(' · ') }}
                            </div>
                        </div>
                    </button>
                </template>
            </div>

            <!-- Empty state -->
            <div
                v-else-if="query.trim().length >= 2 && !loading"
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
