<script setup lang="ts">
import { SlidersHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { DashboardCard } from '@/composables/useDashboardCards';
import { dashboardCards, useDashboardCards } from '@/composables/useDashboardCards';

const { hiddenCount, isVisible, toggle, showAll } = useDashboardCards();

const groups = computed<{ label: string; cards: DashboardCard[] }[]>(() =>
    ['Stats', 'Sections'].map((label) => ({
        label,
        cards: dashboardCards.filter((card) => card.group === label),
    })),
);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button type="button" variant="outline" class="h-10">
                <SlidersHorizontal class="size-4" />
                Cards
                <span
                    v-if="hiddenCount"
                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-muted px-1 text-[11px] font-medium tabular-nums"
                >
                    {{ hiddenCount }} hidden
                </span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-56">
            <template v-for="(group, index) in groups" :key="group.label">
                <DropdownMenuSeparator v-if="index > 0" />
                <DropdownMenuLabel class="text-xs text-muted-foreground">
                    {{ group.label }}
                </DropdownMenuLabel>
                <DropdownMenuCheckboxItem
                    v-for="card in group.cards"
                    :key="card.key"
                    :model-value="isVisible(card.key)"
                    @select="(event: Event) => event.preventDefault()"
                    @update:model-value="toggle(card.key)"
                >
                    {{ card.label }}
                </DropdownMenuCheckboxItem>
            </template>

            <DropdownMenuSeparator />
            <DropdownMenuItem :disabled="hiddenCount === 0" @select="showAll">
                Show all cards
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
