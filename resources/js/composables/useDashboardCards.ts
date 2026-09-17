import type { Ref } from 'vue';
import { computed, ref, watch } from 'vue';

export type DashboardCardKey =
    | 'activity'
    | 'pipelineValue'
    | 'won'
    | 'tasks'
    | 'book'
    | 'taskBoard'
    | 'pipeline'
    | 'goingQuiet'
    | 'recentActivity'
    | 'dealerships';

export type DashboardCard = {
    key: DashboardCardKey;
    label: string;
    group: 'Stats' | 'Sections';
};

export const dashboardCards: DashboardCard[] = [
    { key: 'activity', label: 'Contacts logged', group: 'Stats' },
    { key: 'pipelineValue', label: 'Open pipeline', group: 'Stats' },
    { key: 'won', label: 'Won this month', group: 'Stats' },
    { key: 'tasks', label: 'Open tasks', group: 'Stats' },
    { key: 'book', label: 'In your book', group: 'Stats' },
    { key: 'taskBoard', label: "Today's work", group: 'Sections' },
    { key: 'pipeline', label: 'Pipeline', group: 'Sections' },
    { key: 'goingQuiet', label: 'Going quiet', group: 'Sections' },
    { key: 'recentActivity', label: 'Recent activity', group: 'Sections' },
    { key: 'dealerships', label: 'Dealerships', group: 'Sections' },
];

const storageKey = 'dashboard-hidden-cards';

function readHidden(): DashboardCardKey[] {
    try {
        const stored: unknown = JSON.parse(localStorage.getItem(storageKey) ?? '[]');

        if (!Array.isArray(stored)) {
            return [];
        }

        return dashboardCards
            .map((card) => card.key)
            .filter((key) => stored.includes(key));
    } catch {
        return [];
    }
}

const hidden = ref<DashboardCardKey[]>([]);
let restored = false;

export function useDashboardCards(): {
    hidden: Ref<DashboardCardKey[]>;
    hiddenCount: Ref<number>;
    isVisible: (key: DashboardCardKey) => boolean;
    toggle: (key: DashboardCardKey) => void;
    showAll: () => void;
} {
    if (!restored) {
        restored = true;
        hidden.value = readHidden();
    }

    watch(hidden, (value) => {
        try {
            localStorage.setItem(storageKey, JSON.stringify(value));
        } catch {
            // Storage can be unavailable (private mode); the choice still applies to this visit.
        }
    });

    function isVisible(key: DashboardCardKey): boolean {
        return !hidden.value.includes(key);
    }

    function toggle(key: DashboardCardKey): void {
        hidden.value = isVisible(key)
            ? [...hidden.value, key]
            : hidden.value.filter((hiddenKey) => hiddenKey !== key);
    }

    function showAll(): void {
        hidden.value = [];
    }

    return {
        hidden,
        hiddenCount: computed(() => hidden.value.length),
        isVisible,
        toggle,
        showAll,
    };
}
