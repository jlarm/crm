import type { ComputedRef } from 'vue';
import { computed, ref, watch } from 'vue';
import type { DashboardCardKey } from '@/composables/useDashboardCards';

/** Columns a section spans on the dashboard's three-column grid. */
export type DashboardCardWidth = 1 | 2 | 3;

export type DashboardSectionKey = Extract<
    DashboardCardKey,
    'weeklyDigest' | 'emailReplies' | 'taskBoard' | 'pipeline' | 'goingQuiet' | 'recentActivity' | 'dealerships'
>;

type DashboardLayout = {
    order: DashboardSectionKey[];
    widths: Record<DashboardSectionKey, DashboardCardWidth>;
};

/** The arrangement before anyone customised it. */
const defaultLayout: DashboardLayout = {
    order: ['weeklyDigest', 'emailReplies', 'taskBoard', 'pipeline', 'goingQuiet', 'recentActivity', 'dealerships'],
    widths: {
        weeklyDigest: 3,
        emailReplies: 3,
        taskBoard: 2,
        pipeline: 1,
        goingQuiet: 2,
        recentActivity: 1,
        dealerships: 3,
    },
};

/** Widths each section can take; the dealerships table is unreadable at a third. */
export const allowedWidths: Record<DashboardSectionKey, DashboardCardWidth[]> = {
    weeklyDigest: [1, 2, 3],
    emailReplies: [1, 2, 3],
    taskBoard: [1, 2, 3],
    pipeline: [1, 2, 3],
    goingQuiet: [1, 2, 3],
    recentActivity: [1, 2, 3],
    dealerships: [2, 3],
};

const storageKey = 'dashboard-layout';

function cloneDefault(): DashboardLayout {
    return { order: [...defaultLayout.order], widths: { ...defaultLayout.widths } };
}

/**
 * The saved layout, cleaned up: unknown keys dropped, sections added since it was
 * saved appended in their default position, and widths kept to what each allows.
 */
function readLayout(): DashboardLayout {
    const layout = cloneDefault();

    try {
        const stored: unknown = JSON.parse(localStorage.getItem(storageKey) ?? 'null');

        if (!stored || typeof stored !== 'object') {
            return layout;
        }

        const { order, widths } = stored as { order?: unknown; widths?: unknown };

        if (Array.isArray(order)) {
            const known = order.filter((key): key is DashboardSectionKey => defaultLayout.order.includes(key));
            layout.order = [...new Set([...known, ...defaultLayout.order])];
        }

        if (widths && typeof widths === 'object') {
            for (const key of defaultLayout.order) {
                const width = (widths as Record<string, unknown>)[key];

                if (allowedWidths[key].includes(width as DashboardCardWidth)) {
                    layout.widths[key] = width as DashboardCardWidth;
                }
            }
        }
    } catch {
        // Unreadable storage falls back to the default layout.
    }

    return layout;
}

const layout = ref<DashboardLayout>(cloneDefault());
let restored = false;

export function useDashboardLayout(): {
    order: ComputedRef<DashboardSectionKey[]>;
    widthOf: (key: DashboardSectionKey) => DashboardCardWidth;
    setWidth: (key: DashboardSectionKey, width: DashboardCardWidth) => void;
    moveTo: (key: DashboardSectionKey, target: DashboardSectionKey, place: 'before' | 'after') => void;
    moveBy: (key: DashboardSectionKey, offset: -1 | 1, visible: DashboardSectionKey[]) => void;
    reset: () => void;
    isCustomised: () => boolean;
} {
    if (!restored) {
        restored = true;
        layout.value = readLayout();
    }

    watch(
        layout,
        (value) => {
            try {
                localStorage.setItem(storageKey, JSON.stringify(value));
            } catch {
                // Storage can be unavailable (private mode); the layout still applies to this visit.
            }
        },
        { deep: true },
    );

    const order = computed(() => layout.value.order);

    function widthOf(key: DashboardSectionKey): DashboardCardWidth {
        return layout.value.widths[key];
    }

    function setWidth(key: DashboardSectionKey, width: DashboardCardWidth): void {
        if (allowedWidths[key].includes(width)) {
            layout.value.widths = { ...layout.value.widths, [key]: width };
        }
    }

    function moveTo(key: DashboardSectionKey, target: DashboardSectionKey, place: 'before' | 'after'): void {
        if (key === target) {
            return;
        }

        const without = layout.value.order.filter((candidate) => candidate !== key);
        const index = without.indexOf(target) + (place === 'after' ? 1 : 0);
        without.splice(index, 0, key);
        layout.value.order = without;
    }

    /** Moves past the next visible section, so hidden ones don't swallow a key press. */
    function moveBy(key: DashboardSectionKey, offset: -1 | 1, visible: DashboardSectionKey[]): void {
        const position = visible.indexOf(key);
        const neighbour = visible[position + offset];

        if (position !== -1 && neighbour) {
            moveTo(key, neighbour, offset === 1 ? 'after' : 'before');
        }
    }

    function reset(): void {
        layout.value = cloneDefault();
    }

    function isCustomised(): boolean {
        return JSON.stringify(layout.value) !== JSON.stringify(defaultLayout);
    }

    return { order, widthOf, setWidth, moveTo, moveBy, reset, isCustomised };
}
