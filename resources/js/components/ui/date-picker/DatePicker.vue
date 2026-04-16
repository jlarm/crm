<script setup lang="ts">
import { Calendar } from '@/components/ui/calendar';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { CalendarDate, parseDate, today, getLocalTimeZone } from '@internationalized/date';
import { CalendarIcon, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string | null;
        placeholder?: string;
        clearable?: boolean;
        class?: string;
    }>(),
    {
        placeholder: 'Pick a date',
        clearable: true,
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: string | null): void;
}>();

const open = ref(false);

const calendarValue = computed<CalendarDate | undefined>(() => {
    if (!props.modelValue) {
        return undefined;
    }
    try {
        return parseDate(props.modelValue);
    } catch {
        return undefined;
    }
});

const displayValue = computed<string>(() => {
    if (!calendarValue.value) {
        return '';
    }
    return calendarValue.value.toDate(getLocalTimeZone()).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
});

const isToday = computed(() => {
    if (!calendarValue.value) return false;
    const t = today(getLocalTimeZone());
    return calendarValue.value.compare(t) === 0;
});

const isPast = computed(() => {
    if (!calendarValue.value) return false;
    return calendarValue.value.compare(today(getLocalTimeZone())) < 0;
});

function onSelect(date: CalendarDate | undefined): void {
    emit('update:modelValue', date ? date.toString() : null);
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
                :class="
                    cn(
                        'w-full justify-start text-left font-normal',
                        !modelValue && 'text-muted-foreground',
                        isPast && 'border-red-300 text-red-600 hover:border-red-400 dark:border-red-800 dark:text-red-400',
                        isToday && 'border-amber-300 text-amber-700 hover:border-amber-400 dark:border-amber-800 dark:text-amber-400',
                        props.class,
                    )
                "
            >
                <CalendarIcon class="mr-2 h-4 w-4 shrink-0 opacity-50" />
                <span class="flex-1 truncate">
                    {{ displayValue || placeholder }}
                </span>
                <X
                    v-if="clearable && modelValue"
                    class="ml-1 h-3.5 w-3.5 shrink-0 opacity-50 hover:opacity-100"
                    @click="clear"
                />
            </Button>
        </PopoverTrigger>

        <PopoverContent align="start" class="w-auto">
            <Calendar
                :model-value="calendarValue"
                initial-focus
                @update:model-value="onSelect"
            />
        </PopoverContent>
    </Popover>
</template>
