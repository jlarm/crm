<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { type CalendarRootEmits, type CalendarRootProps, CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody, CalendarGridHead, CalendarGridRow, CalendarHeadCell, CalendarHeader, CalendarHeading, CalendarNext, CalendarPrev, CalendarRoot, useForwardPropsEmits } from 'reka-ui';
import { reactiveOmit } from '@vueuse/core';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps<CalendarRootProps & { class?: HTMLAttributes['class'] }>();
const emits = defineEmits<CalendarRootEmits>();

const delegatedProps = reactiveOmit(props, 'class');
const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <CalendarRoot
        v-bind="forwarded"
        :class="cn('p-3', props.class)"
        v-slot="{ grid, weekDays }"
    >
        <CalendarHeader class="relative flex items-center justify-between">
            <CalendarPrev
                class="inline-flex size-7 items-center justify-center rounded-md border border-transparent text-muted-foreground transition-colors hover:border-border hover:text-foreground disabled:opacity-50"
            >
                <ChevronLeft class="size-4" />
            </CalendarPrev>
            <CalendarHeading class="text-sm font-medium" />
            <CalendarNext
                class="inline-flex size-7 items-center justify-center rounded-md border border-transparent text-muted-foreground transition-colors hover:border-border hover:text-foreground disabled:opacity-50"
            >
                <ChevronRight class="size-4" />
            </CalendarNext>
        </CalendarHeader>

        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:gap-6">
            <CalendarGrid v-for="month in grid" :key="month.value.toString()">
                <CalendarGridHead>
                    <CalendarGridRow class="flex">
                        <CalendarHeadCell
                            v-for="day in weekDays"
                            :key="day"
                            class="w-8 rounded-md text-[0.8rem] font-normal text-muted-foreground"
                        >
                            {{ day }}
                        </CalendarHeadCell>
                    </CalendarGridRow>
                </CalendarGridHead>
                <CalendarGridBody>
                    <CalendarGridRow
                        v-for="(weekDates, index) in month.rows"
                        :key="`weekDate-${index}`"
                        class="mt-1 flex w-full"
                    >
                        <CalendarCell
                            v-for="weekDate in weekDates"
                            :key="weekDate.toString()"
                            :date="weekDate"
                            class="relative p-0 text-center text-sm"
                        >
                            <CalendarCellTrigger
                                :day="weekDate"
                                :month="month.value"
                                :class="cn(
                                    'relative inline-flex size-8 items-center justify-center whitespace-nowrap rounded-md p-0 text-sm font-normal ring-offset-background transition-colors',
                                    'hover:bg-accent hover:text-accent-foreground',
                                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                                    'disabled:pointer-events-none disabled:opacity-50',
                                    '[&[data-today]:not([data-selected])]:bg-accent [&[data-today]:not([data-selected])]:text-accent-foreground',
                                    '[&[data-selected]]:bg-primary [&[data-selected]]:text-primary-foreground [&[data-selected]]:hover:bg-primary [&[data-selected]]:hover:text-primary-foreground',
                                    '[&[data-outside-view]]:text-muted-foreground/40 [&[data-outside-view][data-selected]]:bg-accent/50 [&[data-outside-view][data-selected]]:text-muted-foreground',
                                )"
                            />
                        </CalendarCell>
                    </CalendarGridRow>
                </CalendarGridBody>
            </CalendarGrid>
        </div>
    </CalendarRoot>
</template>
