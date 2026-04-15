import { router } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

interface UseTableFiltersOptions {
    routeUrl: string;
    initialFilters?: Record<string, any>;
    debounceMs?: number;
    onlyProps?: string[];
    storageKey?: string;
    persistedKeys?: string[];
}

export function useTableFilters(options: UseTableFiltersOptions) {
    const {
        routeUrl,
        initialFilters = {},
        debounceMs = 300,
        onlyProps = [],
        storageKey,
        persistedKeys = [],
    } = options;

    const defaultFilters = { ...initialFilters };
    const filters = ref({ ...defaultFilters });
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

            if (debounceTimeout) {
                clearTimeout(debounceTimeout);
            }

            debounceTimeout = setTimeout(() => {
                applyFilters();
            }, debounceMs);
        },
        { deep: true },
    );

    function getStoredFilters(): Record<string, unknown> | null {
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

            return Object.fromEntries(
                persistedKeys
                    .filter((key) => key in parsedFilters)
                    .map((key) => [
                        key,
                        parsedFilters[key as keyof typeof parsedFilters],
                    ]),
            );
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

        router.get(routeUrl, params, {
            preserveState: true,
            preserveScroll: true,
            only: onlyProps.length > 0 ? onlyProps : undefined,
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
        applyFilters,
        resetFilters,
        hasActiveFilters,
    };
}
