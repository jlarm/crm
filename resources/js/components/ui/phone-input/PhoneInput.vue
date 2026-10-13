<script setup lang="ts">
import { cn } from '@/lib/utils';
import { formatPhoneAsTyped } from '@/lib/phone';
import { computed, ref, watch } from 'vue';

/**
 * Phone field shown as 999-999-9999 that submits digits only.
 *
 * Inside an Inertia <Form>, pass `name` and it posts through a hidden input.
 * With useForm, bind v-model instead; the model receives the digits.
 */
const props = defineProps<{
    name?: string;
    defaultValue?: string | null;
    placeholder?: string;
    id?: string;
    class?: string;
}>();

const model = defineModel<string | null>({ default: undefined });

const displayValue = ref(formatPhoneAsTyped(model.value ?? props.defaultValue ?? ''));
const rawValue = computed(() => displayValue.value.replace(/\D/g, ''));

watch(model, (value) => {
    if (value !== undefined && (value ?? '').replace(/\D/g, '') !== rawValue.value) {
        displayValue.value = formatPhoneAsTyped(value ?? '');
    }
});

function handleInput(event: Event): void {
    const input = event.target as HTMLInputElement;
    const formatted = formatPhoneAsTyped(input.value);
    displayValue.value = formatted;
    input.value = formatted;

    if (model.value !== undefined) {
        model.value = rawValue.value;
    }
}
</script>

<template>
    <input v-if="name" type="hidden" :name="name" :value="rawValue" />
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
