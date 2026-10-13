<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

/**
 * Previous / Next controls for a Laravel length-aware paginator. Hidden when there's one page.
 */
defineProps<{
    paginator: {
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}>();

function go(url: string | null): void {
    if (url) {
        router.get(url, {}, { preserveState: true });
    }
}
</script>

<template>
    <nav
        v-if="paginator.last_page > 1"
        class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500"
        aria-label="Pagination"
    >
        <span>{{ paginator.from }}–{{ paginator.to }} of {{ paginator.total }}</span>
        <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" :disabled="!paginator.prev_page_url" @click="go(paginator.prev_page_url)">
                <ChevronLeft class="size-4" />
                Previous
            </Button>
            <span class="px-1 tabular-nums">Page {{ paginator.current_page }} of {{ paginator.last_page }}</span>
            <Button variant="outline" size="sm" :disabled="!paginator.next_page_url" @click="go(paginator.next_page_url)">
                Next
                <ChevronRight class="size-4" />
            </Button>
        </div>
    </nav>
</template>
