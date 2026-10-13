<script setup lang="ts">
import type { DashboardCardWidth, DashboardSectionKey } from '@/composables/useDashboardLayout';
import { ArrowDown, ArrowUp, GripVertical } from 'lucide-vue-next';
import { computed, ref } from 'vue';

/**
 * One section of the dashboard grid. While arranging, it shows a bar to drag it,
 * move it with the keyboard and pick its width.
 */
const props = defineProps<{
    sectionKey: DashboardSectionKey;
    label: string;
    width: DashboardCardWidth;
    allowedWidths: DashboardCardWidth[];
    arranging: boolean;
    isFirst: boolean;
    isLast: boolean;
}>();

const emit = defineEmits<{
    (event: 'set-width', width: DashboardCardWidth): void;
    (event: 'move-by', offset: -1 | 1): void;
    (event: 'drop', dragged: DashboardSectionKey, place: 'before' | 'after'): void;
}>();

const DRAG_TYPE = 'application/x-dashboard-section';

/** Full class names so Tailwind keeps them. */
const spanClasses: Record<DashboardCardWidth, string> = {
    1: 'xl:col-span-1',
    2: 'xl:col-span-2',
    3: 'xl:col-span-3',
};

const widthLabels: Record<DashboardCardWidth, string> = { 1: '⅓', 2: '⅔', 3: 'Full' };

const dropPlace = ref<'before' | 'after' | null>(null);
const isDragging = ref(false);

const spanClass = computed(() => spanClasses[props.width]);

function onDragStart(event: DragEvent): void {
    event.dataTransfer?.setData(DRAG_TYPE, props.sectionKey);
    event.dataTransfer?.setData('text/plain', props.label);
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
    }
    isDragging.value = true;
}

/** Dropping on the first half of a card places the dragged one before it, else after. */
function placeFor(event: DragEvent): 'before' | 'after' {
    const box = (event.currentTarget as HTMLElement).getBoundingClientRect();
    const isWide = box.width > box.height * 2;

    return isWide
        ? (event.clientY < box.top + box.height / 2 ? 'before' : 'after')
        : (event.clientX < box.left + box.width / 2 ? 'before' : 'after');
}

function onDragOver(event: DragEvent): void {
    if (!props.arranging || !event.dataTransfer?.types.includes(DRAG_TYPE)) {
        return;
    }

    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    dropPlace.value = isDragging.value ? null : placeFor(event);
}

function onDrop(event: DragEvent): void {
    const dragged = event.dataTransfer?.getData(DRAG_TYPE) as DashboardSectionKey | undefined;
    const place = placeFor(event);
    dropPlace.value = null;

    if (dragged && dragged !== props.sectionKey) {
        event.preventDefault();
        emit('drop', dragged, place);
    }
}
</script>

<template>
    <div
        class="relative flex min-w-0 flex-col transition-opacity"
        :class="[spanClass, { 'opacity-40': isDragging, 'rounded-xl outline-2 outline-offset-4 outline-orange-300 outline-dashed dark:outline-orange-800': arranging }]"
        :draggable="arranging"
        :data-section="sectionKey"
        @dragstart="onDragStart"
        @dragend="isDragging = false"
        @dragover="onDragOver"
        @dragleave="dropPlace = null"
        @drop="onDrop"
    >
        <div
            v-if="dropPlace"
            class="pointer-events-none absolute z-20 rounded-full bg-orange-500"
            :class="
                width === 3
                    ? ['inset-x-0 h-1', dropPlace === 'before' ? '-top-4' : '-bottom-4']
                    : ['inset-y-0 w-1', dropPlace === 'before' ? '-left-4' : '-right-4']
            "
            aria-hidden="true"
        />

        <div class="flex flex-1 flex-col">
            <div
                v-if="arranging"
                class="mb-2 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-orange-200 bg-orange-50 px-2 py-1.5 text-xs dark:border-orange-900 dark:bg-orange-950/40"
            >
                <span class="flex cursor-grab items-center gap-1.5 font-medium text-orange-800 active:cursor-grabbing dark:text-orange-200">
                    <GripVertical class="size-4" aria-hidden="true" />
                    {{ label }}
                </span>
                <span class="flex items-center gap-1">
                    <span class="inline-flex rounded-md border border-orange-200 bg-white p-0.5 dark:border-orange-900 dark:bg-slate-950" role="group" :aria-label="`${label} width`">
                        <button
                            v-for="option in allowedWidths"
                            :key="option"
                            type="button"
                            class="rounded px-2 py-0.5 font-medium transition-colors"
                            :class="option === width ? 'bg-orange-500 text-white' : 'text-slate-600 hover:bg-orange-100 dark:text-slate-300 dark:hover:bg-orange-900/40'"
                            :aria-pressed="option === width"
                            :title="`${widthLabels[option]} width`"
                            @click="emit('set-width', option)"
                        >
                            {{ widthLabels[option] }}
                        </button>
                    </span>
                    <button
                        type="button"
                        class="rounded p-1 text-slate-600 hover:bg-orange-100 disabled:opacity-30 dark:text-slate-300 dark:hover:bg-orange-900/40"
                        :disabled="isFirst"
                        :aria-label="`Move ${label} earlier`"
                        @click="emit('move-by', -1)"
                    >
                        <ArrowUp class="size-3.5" />
                    </button>
                    <button
                        type="button"
                        class="rounded p-1 text-slate-600 hover:bg-orange-100 disabled:opacity-30 dark:text-slate-300 dark:hover:bg-orange-900/40"
                        :disabled="isLast"
                        :aria-label="`Move ${label} later`"
                        @click="emit('move-by', 1)"
                    >
                        <ArrowDown class="size-3.5" />
                    </button>
                </span>
            </div>

            <!-- A size container, so each card lays itself out for its column width, not the screen's. -->
            <div class="@container flex-1 [&>*]:h-full" :class="{ 'pointer-events-none select-none': arranging }">
                <slot />
            </div>
        </div>
    </div>
</template>
