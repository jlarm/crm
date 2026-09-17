<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    currentPage: number;
    lastPage: number;
    from: number;
    to: number;
    total: number;
    links: PaginationLink[];
}>();

function visit(url: string | null | undefined): void {
    if (!url) {
        return;
    }

    const parsed = new URL(url, window.location.origin);

    router.get(parsed.pathname, Object.fromEntries(parsed.searchParams), {
        preserveState: true,
        preserveScroll: true,
        only: ['dealerships'],
    });
}

function visitPage(page: number): void {
    const target = Math.min(Math.max(1, Math.trunc(page)), props.lastPage);
    const params = Object.fromEntries(new URLSearchParams(window.location.search));

    router.get(
        window.location.pathname,
        { ...params, page: target },
        { preserveState: true, preserveScroll: true, only: ['dealerships'] },
    );
}

const previousUrl = computed(() => props.links[0]?.url ?? null);
const nextUrl = computed(() => props.links[props.links.length - 1]?.url ?? null);
const pageLinks = computed(() => props.links.slice(1, -1));

const jumpTo = ref('');

function submitJump(): void {
    const page = Number(jumpTo.value);

    if (Number.isFinite(page) && page >= 1) {
        visitPage(page);
    }

    jumpTo.value = '';
}

const pageButton =
    'inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm tabular-nums transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-40';
</script>

<template>
    <nav
        aria-label="Pagination"
        class="flex flex-col items-center justify-between gap-3 px-1 pt-4 text-sm md:flex-row"
    >
        <p class="text-muted-foreground">
            Showing
            <span class="font-medium text-foreground tabular-nums">{{ from || 0 }}–{{ to || 0 }}</span>
            of
            <span class="font-medium text-foreground tabular-nums">{{ total }}</span>
        </p>

        <div v-if="lastPage > 1" class="flex items-center gap-1.5">
            <button
                type="button"
                :class="[pageButton, 'border-border bg-card text-foreground hover:bg-accent']"
                :disabled="!previousUrl"
                aria-label="Previous page"
                @click="visit(previousUrl)"
            >
                <ChevronLeft class="size-4" />
            </button>

            <template v-for="(link, index) in pageLinks" :key="`${index}-${link.label}`">
                <span
                    v-if="!link.url"
                    class="inline-flex h-8 min-w-8 items-center justify-center text-muted-foreground"
                >
                    …
                </span>
                <button
                    v-else
                    type="button"
                    :class="[
                        pageButton,
                        link.active
                            ? 'border-foreground/20 bg-accent font-medium text-foreground'
                            : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground',
                    ]"
                    :aria-current="link.active ? 'page' : undefined"
                    @click="visit(link.url)"
                >
                    {{ link.label }}
                </button>
            </template>

            <button
                type="button"
                :class="[pageButton, 'border-border bg-card text-foreground hover:bg-accent']"
                :disabled="!nextUrl"
                aria-label="Next page"
                @click="visit(nextUrl)"
            >
                <ChevronRight class="size-4" />
            </button>
        </div>

        <form v-if="lastPage > 1" class="flex items-center gap-2" @submit.prevent="submitJump">
            <label for="dealership-page-jump" class="text-muted-foreground">Go to page</label>
            <input
                id="dealership-page-jump"
                v-model="jumpTo"
                type="number"
                min="1"
                :max="lastPage"
                inputmode="numeric"
                class="h-8 w-16 rounded-md border border-input bg-card px-2 text-sm tabular-nums focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            />
            <button
                type="submit"
                class="inline-flex h-8 items-center gap-1 rounded-md px-2 font-medium text-foreground hover:bg-accent"
            >
                Go
                <ChevronRight class="size-4" />
            </button>
        </form>
    </nav>
</template>
