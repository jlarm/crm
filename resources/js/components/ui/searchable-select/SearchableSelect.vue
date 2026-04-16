<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Check, ChevronsUpDown, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Option {
    value: string;
    label: string;
}

const props = withDefaults(
    defineProps<{
        modelValue: string | null;
        options: Option[];
        placeholder?: string;
        clearable?: boolean;
        class?: string;
    }>(),
    {
        placeholder: 'Select...',
        clearable: true,
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: string | null): void;
}>();

const open = ref(false);
const search = ref('');

const selectedLabel = computed(
    () => props.options.find((o) => o.value === props.modelValue)?.label ?? null,
);

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) {
        return props.options;
    }
    return props.options.filter((o) => o.label.toLowerCase().includes(q));
});

watch(open, (isOpen) => {
    if (!isOpen) {
        search.value = '';
    }
});

function select(value: string): void {
    emit('update:modelValue', value === props.modelValue ? null : value);
    open.value = false;
}

function clear(e: MouseEvent): void {
    e.stopPropagation();
    emit('update:modelValue', null);
}
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                type="button"
                variant="outline"
                role="combobox"
                :class="
                    cn(
                        'w-full justify-between font-normal',
                        !selectedLabel && 'text-muted-foreground',
                        props.class,
                    )
                "
            >
                <span class="truncate">{{ selectedLabel ?? placeholder }}</span>
                <span class="ml-2 flex shrink-0 items-center gap-1">
                    <X
                        v-if="clearable && modelValue"
                        class="h-3.5 w-3.5 opacity-50 hover:opacity-100"
                        @click="clear"
                    />
                    <ChevronsUpDown class="h-3.5 w-3.5 opacity-50" />
                </span>
            </Button>
        </PopoverTrigger>

        <PopoverContent align="start" class="w-[var(--reka-popover-trigger-width)] p-0">
            <div class="border-b px-3 py-2">
                <Input
                    v-model="search"
                    placeholder="Search..."
                    class="h-8 border-0 p-0 shadow-none focus-visible:ring-0"
                    @keydown.escape="open = false"
                />
            </div>

            <div class="max-h-56 overflow-y-auto p-1">
                <p
                    v-if="filtered.length === 0"
                    class="px-3 py-6 text-center text-sm text-muted-foreground"
                >
                    No results found.
                </p>

                <button
                    v-for="option in filtered"
                    :key="option.value"
                    type="button"
                    class="flex w-full items-center gap-2 rounded-sm px-3 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground"
                    @click="select(option.value)"
                >
                    <Check
                        class="h-3.5 w-3.5 shrink-0"
                        :class="modelValue === option.value ? 'opacity-100' : 'opacity-0'"
                    />
                    {{ option.label }}
                </button>
            </div>
        </PopoverContent>
    </Popover>
</template>
