<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed, ref } from 'vue';

const props = defineProps<{
    name: string;
    defaultValue?: string | null;
    placeholder?: string;
    id?: string;
    class?: string;
}>();

function formatPhone(value: string): string {
    const digits = value.replace(/\D/g, '').slice(0, 10);
    if (digits.length <= 3) {
        return digits;
    }
    if (digits.length <= 6) {
        return `${digits.slice(0, 3)}-${digits.slice(3)}`;
    }
    return `${digits.slice(0, 3)}-${digits.slice(3, 6)}-${digits.slice(6)}`;
}

const displayValue = ref(formatPhone(props.defaultValue ?? ''));
const rawValue = computed(() => displayValue.value.replace(/\D/g, ''));

function handleInput(event: Event): void {
    const input = event.target as HTMLInputElement;
    const formatted = formatPhone(input.value);
    displayValue.value = formatted;
    input.value = formatted;
}
</script>

<template>
    <input type="hidden" :name="name" :value="rawValue" />
    <input
        :id="id"
        type="tel"
        data-slot="input"
        :placeholder="placeholder ?? '999-999-9999'"
        :value="displayValue"
        :class="cn(
            'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
            props.class,
        )"
        @input="handleInput"
    />
</template>
