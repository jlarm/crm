<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next';
import { computed } from 'vue';

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

function goToPage(url: string | null | undefined): void {
    if (!url) return;
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
}

const firstPageUrl = computed(
    () => props.links.find((link) => link.label === '1')?.url,
);

const lastPageUrl = computed(
    () =>
        props.links
            .slice(0, -1)
            .reverse()
            .find((link) => !link.label.includes('Previous'))?.url,
);

const prevPageUrl = computed(
    () => props.links.find((link) => link.label.includes('Previous'))?.url,
);

const nextPageUrl = computed(
    () => props.links.find((link) => link.label.includes('Next'))?.url,
);
</script>

<template>
    <div class="flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
            Showing {{ from || 0 }} to {{ to || 0 }} of {{ total }} results
        </div>

        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="icon"
                :disabled="!firstPageUrl || currentPage === 1"
                @click="goToPage(firstPageUrl)"
            >
                <ChevronsLeft class="h-4 w-4" />
            </Button>
            <Button
                variant="outline"
                size="icon"
                :disabled="!prevPageUrl"
                @click="goToPage(prevPageUrl)"
            >
                <ChevronLeft class="h-4 w-4" />
            </Button>

            <div class="flex items-center gap-1">
                <span class="text-sm">
                    Page {{ currentPage }} of {{ lastPage }}
                </span>
            </div>

            <Button
                variant="outline"
                size="icon"
                :disabled="!nextPageUrl"
                @click="goToPage(nextPageUrl)"
            >
                <ChevronRight class="h-4 w-4" />
            </Button>
            <Button
                variant="outline"
                size="icon"
                :disabled="!lastPageUrl || currentPage === lastPage"
                @click="goToPage(lastPageUrl)"
            >
                <ChevronsRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
