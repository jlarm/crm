<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Search, X } from 'lucide-vue-next';
import { computed } from 'vue';

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
        tag: string;
        scope: string;
        include_imported: string;
    };
    statuses: FilterOption[];
    ratings: FilterOption[];
    types: FilterOption[];
    tags: string[];
}

interface Emits {
    (e: 'update:modelValue', value: Props['modelValue']): void;
}

type FilterKey = keyof Props['modelValue'];

/** Select items can't hold an empty string, so "no filter" travels as this and maps back to ''. */
const ALL = '__all__';

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const tagOptions = computed<FilterOption[]>(() => props.tags.map((tag) => ({ value: tag, label: tag })));

const selects = computed(() => [
    { key: 'status' as const, label: 'Status', allLabel: 'All statuses', options: props.statuses },
    { key: 'rating' as const, label: 'Rating', allLabel: 'All ratings', options: props.ratings },
    { key: 'type' as const, label: 'Type', allLabel: 'All types', options: props.types },
    { key: 'tag' as const, label: 'Tag', allLabel: 'All tags', options: tagOptions.value },
]);

const hasActiveFilters = computed(() =>
    FILTER_KEYS.some((key) => Boolean(props.modelValue[key])),
);

const FILTER_KEYS: FilterKey[] = ['search', 'status', 'rating', 'type', 'tag', 'include_imported'];

/** Blanks the filters themselves; scope and sort stay as they are. */
function clearFilters(): void {
    emit('update:modelValue', {
        ...props.modelValue,
        ...Object.fromEntries(FILTER_KEYS.map((key) => [key, ''])),
    });
}

function update(key: FilterKey, value: string): void {
    emit('update:modelValue', { ...props.modelValue, [key]: value === ALL ? '' : value });
}
</script>

<template>
    <div class="flex flex-nowrap items-center gap-2 overflow-x-auto pb-1">
        <div class="relative min-w-48 flex-1 lg:max-w-72">
            <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
            <Input
                :model-value="modelValue.search"
                type="search"
                placeholder="Search name, city or tag…"
                aria-label="Search dealerships"
                class="h-10 bg-card pl-9"
                @update:model-value="(value) => update('search', String(value))"
            />
        </div>

        <Select
            v-for="select in selects"
            :key="select.key"
            :model-value="modelValue[select.key] || ALL"
            @update:model-value="(value) => update(select.key, String(value))"
        >
            <SelectTrigger
                class="h-10 shrink-0 bg-card data-[size=default]:h-10"
                :class="modelValue[select.key] ? 'border-foreground/30 text-foreground' : ''"
                :aria-label="`Filter by ${select.label.toLowerCase()}`"
            >
                <span class="text-muted-foreground">{{ select.label }}:</span>
                <SelectValue :placeholder="select.allLabel" />
            </SelectTrigger>
            <SelectContent class="max-h-72">
                <SelectItem :value="ALL">{{ select.allLabel }}</SelectItem>
                <SelectItem v-for="option in select.options" :key="option.value" :value="option.value">
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>

        <label class="inline-flex h-10 shrink-0 cursor-pointer items-center gap-2 rounded-md border border-input bg-card px-3 text-sm whitespace-nowrap">
            <Checkbox
                :model-value="modelValue.include_imported === '1'"
                @update:model-value="(checked) => update('include_imported', checked === true ? '1' : '')"
            />
            Show imported
        </label>

        <Button
            v-if="hasActiveFilters"
            type="button"
            variant="ghost"
            class="h-10 shrink-0 text-muted-foreground"
            @click="clearFilters"
        >
            <X class="size-4" />
            Clear
        </Button>
    </div>
</template>
