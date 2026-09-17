<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Filter, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface FilterOption {
    value: string;
    label: string;
}

interface Props {
    modelValue: {
        search: string;
        status: string;
        rating: string;
        type: string;
        scope: string;
        include_imported: string;
    };
    statuses: FilterOption[];
    ratings: FilterOption[];
    types: FilterOption[];
}

interface Emits {
    (e: 'update:modelValue', value: Props['modelValue']): void;
    (e: 'reset'): void;
}

type FilterSection = 'status' | 'rating' | 'type' | 'imported';

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const open = ref(false);
const activeSection = ref<FilterSection>('status');
const draftFilters = ref({ ...props.modelValue });

watch(
    () => props.modelValue,
    (value) => {
        draftFilters.value = { ...value };
    },
    { deep: true },
);

const activeChips = computed(() => {
    const chips: Array<{ key: keyof Props['modelValue']; label: string }> = [];

    const statusLabel = props.statuses.find(
        (s) => s.value === props.modelValue.status,
    )?.label;
    const ratingLabel = props.ratings.find(
        (r) => r.value === props.modelValue.rating,
    )?.label;
    const typeLabel = props.types.find(
        (t) => t.value === props.modelValue.type,
    )?.label;

    if (props.modelValue.search) {
        chips.push({ key: 'search', label: `Search: ${props.modelValue.search}` });
    }

    if (props.modelValue.status && statusLabel) {
        chips.push({ key: 'status', label: `Status: ${statusLabel}` });
    }

    if (props.modelValue.rating && ratingLabel) {
        chips.push({ key: 'rating', label: `Rating: ${ratingLabel}` });
    }

    if (props.modelValue.type && typeLabel) {
        chips.push({ key: 'type', label: `Type: ${typeLabel}` });
    }

    if (props.modelValue.include_imported === '1') {
        chips.push({ key: 'include_imported', label: 'Show imported' });
    }

    return chips;
});

function updateDraft(key: keyof Props['modelValue'], value: string): void {
    draftFilters.value = { ...draftFilters.value, [key]: value };
}

function applyFilters(): void {
    emit('update:modelValue', { ...draftFilters.value });
    open.value = false;
}

function clearFilters(): void {
    draftFilters.value = {
        ...draftFilters.value,
        search: '',
        status: '',
        rating: '',
        type: '',
        include_imported: '',
    };
    emit('reset');
    open.value = false;
}

function clearChip(key: keyof Props['modelValue']): void {
    emit('update:modelValue', { ...props.modelValue, [key]: '' });
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-3">
        <DropdownMenu v-model:open="open">
            <DropdownMenuTrigger as-child>
                <Button variant="outline" class="gap-2">
                    <Filter class="h-4 w-4" />
                    Filter
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" class="w-[640px] p-0 shadow-xl">
                <div class="grid grid-cols-[200px_1fr]">
                    <div class="border-r border-border p-4">
                        <div class="relative">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="draftFilters.search"
                                type="text"
                                placeholder="Search..."
                                class="pl-9"
                            />
                        </div>

                        <div class="mt-4 space-y-1">
                            <button
                                v-for="section in ['status', 'rating', 'type', 'imported'] as FilterSection[]"
                                :key="section"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition capitalize"
                                :class="
                                    activeSection === section
                                        ? 'bg-muted font-medium'
                                        : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'
                                "
                                @click="activeSection = section"
                            >
                                {{ section }}
                            </button>
                        </div>
                    </div>

                    <div class="p-4">
                        <div v-if="activeSection === 'status'" class="space-y-3">
                            <button
                                type="button"
                                class="flex w-full items-center gap-3 rounded-lg border border-border px-3 py-2 text-left"
                                @click="updateDraft('status', '')"
                            >
                                <Checkbox :model-value="draftFilters.status === ''" />
                                <span class="text-sm">All statuses</span>
                            </button>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    v-for="status in statuses"
                                    :key="status.value"
                                    type="button"
                                    class="flex items-center gap-3 rounded-lg border border-border px-3 py-2 text-left"
                                    @click="updateDraft('status', status.value)"
                                >
                                    <Checkbox
                                        :model-value="draftFilters.status === status.value"
                                    />
                                    <span class="text-sm">{{ status.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-else-if="activeSection === 'rating'" class="space-y-3">
                            <button
                                type="button"
                                class="flex w-full items-center gap-3 rounded-lg border border-border px-3 py-2 text-left"
                                @click="updateDraft('rating', '')"
                            >
                                <Checkbox :model-value="draftFilters.rating === ''" />
                                <span class="text-sm">All ratings</span>
                            </button>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    v-for="rating in ratings"
                                    :key="rating.value"
                                    type="button"
                                    class="flex items-center gap-3 rounded-lg border border-border px-3 py-2 text-left"
                                    @click="updateDraft('rating', rating.value)"
                                >
                                    <Checkbox
                                        :model-value="draftFilters.rating === rating.value"
                                    />
                                    <span class="text-sm">{{ rating.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-else-if="activeSection === 'type'" class="space-y-3">
                            <button
                                type="button"
                                class="flex w-full items-center gap-3 rounded-lg border border-border px-3 py-2 text-left"
                                @click="updateDraft('type', '')"
                            >
                                <Checkbox :model-value="draftFilters.type === ''" />
                                <span class="text-sm">All types</span>
                            </button>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    v-for="type in types"
                                    :key="type.value"
                                    type="button"
                                    class="flex items-center gap-3 rounded-lg border border-border px-3 py-2 text-left"
                                    @click="updateDraft('type', type.value)"
                                >
                                    <Checkbox
                                        :model-value="draftFilters.type === type.value"
                                    />
                                    <span class="text-sm">{{ type.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-else class="space-y-3">
                            <button
                                type="button"
                                class="flex w-full items-center gap-3 rounded-lg border border-border px-3 py-2 text-left"
                                @click="updateDraft('include_imported', draftFilters.include_imported === '1' ? '' : '1')"
                            >
                                <Checkbox :model-value="draftFilters.include_imported === '1'" />
                                <span class="text-sm">Show imported dealerships</span>
                            </button>
                        </div>

                        <div
                            class="mt-6 flex items-center justify-between border-t border-border pt-4"
                        >
                            <button
                                type="button"
                                class="text-sm font-medium text-muted-foreground transition hover:text-foreground"
                                @click="clearFilters"
                            >
                                Clear
                            </button>
                            <Button type="button" @click="applyFilters">Apply</Button>
                        </div>
                    </div>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>

        <div class="flex flex-wrap items-center gap-2">
            <Button
                v-for="chip in activeChips"
                :key="chip.label"
                variant="outline"
                size="sm"
                class="gap-1"
                type="button"
                @click="clearChip(chip.key)"
            >
                {{ chip.label }}
                <X class="h-3 w-3" />
            </Button>
        </div>
    </div>
</template>
