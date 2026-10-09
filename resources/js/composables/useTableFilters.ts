import { router } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { onMounted, ref, watch } from 'vue';

/** Every filter is a query-string value; an empty string means "not filtered". */
type FilterValues = Record<string, string>;

interface UseTableFiltersOptions<T extends FilterValues> {
    routeUrl: string;
    initialFilters: T;
    debounceMs?: number;
    onlyProps?: string[];
    storageKey?: string;
    persistedKeys?: (keyof T & string)[];
}

export function useTableFilters<T extends FilterValues>(options: UseTableFiltersOptions<T>): {
    filters: Ref<T>;
    isLoadingData: Ref<boolean>;
    applyFilters: () => void;
    resetFilters: () => void;
    hasActiveFilters: (excludeKeys?: string[]) => boolean;
} {
    const {
        routeUrl,
        initialFilters,
        debounceMs = 300,
        onlyProps = [],
        storageKey,
        persistedKeys = [],
    } = options;

    const defaultFilters = { ...initialFilters };
    const filters = ref({ ...defaultFilters }) as Ref<T>;
    const isLoadingData = ref(false);
    let debounceTimeout: number | null = null;

    onMounted(() => {
        const restoredFilters = getStoredFilters();

        if (!restoredFilters) {
            return;
        }

        filters.value = {
            ...filters.value,
            ...restoredFilters,
        };
    });

    watch(
        filters,
        () => {
            storeFilters();
            isLoadingData.value = true;

            if (debounceTimeout) {
                clearTimeout(debounceTimeout);
            }

            debounceTimeout = setTimeout(() => {
                applyFilters();
            }, debounceMs);
        },
        { deep: true },
    );

    function getStoredFilters(): Partial<T> | null {
        if (
            typeof window === 'undefined' ||
            !storageKey ||
            persistedKeys.length === 0
        ) {
            return null;
        }

        const storedFilters = localStorage.getItem(storageKey);

        if (!storedFilters) {
            return null;
        }

        try {
            const parsedFilters: unknown = JSON.parse(storedFilters);

            if (
                !parsedFilters ||
                typeof parsedFilters !== 'object' ||
                Array.isArray(parsedFilters)
            ) {
                return null;
            }

            const stored = parsedFilters as Record<string, unknown>;
            const restored: Partial<T> = {};

            for (const key of persistedKeys) {
                const value = stored[key];

                // Ignore anything that isn't a string; it can't be a filter value.
                if (typeof value === 'string') {
                    restored[key] = value as T[typeof key];
                }
            }

            return restored;
        } catch {
            return null;
        }
    }

    function storeFilters(): void {
        if (
            typeof window === 'undefined' ||
            !storageKey ||
            persistedKeys.length === 0
        ) {
            return;
        }

        const storedFilters = Object.fromEntries(
            persistedKeys.map((key) => [key, filters.value[key] ?? null]),
        );

        localStorage.setItem(storageKey, JSON.stringify(storedFilters));
    }

    function applyFilters(): void {
        const params = Object.fromEntries(
            Object.entries(filters.value).filter(
                ([, value]) => value !== '' && value !== null,
            ),
        );

        isLoadingData.value = true;

        router.get(routeUrl, params, {
            preserveState: true,
            preserveScroll: true,
            only: onlyProps.length > 0 ? onlyProps : undefined,
            onFinish: () => {
                isLoadingData.value = false;
            },
        });
    }

    function resetFilters(): void {
        filters.value = { ...defaultFilters };
    }

    function hasActiveFilters(excludeKeys: string[] = []): boolean {
        return Object.entries(filters.value)
            .filter(([key]) => !excludeKeys.includes(key))
            .some(([, value]) => value !== '' && value !== null);
    }

    return {
        filters,
        isLoadingData,
        applyFilters,
        resetFilters,
        hasActiveFilters,
    };
}
