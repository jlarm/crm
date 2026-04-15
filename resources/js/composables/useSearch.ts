import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { search } from '@/routes';

export type SearchResultType = 'dealership' | 'contact';

export interface SearchResult {
    type: SearchResultType;
    id: number;
    label: string;
    subtitle: string | null;
    meta: string | null;
    url: string;
}

export function useSearch() {
    const query = ref('');
    const results = ref<SearchResult[]>([]);
    const loading = ref(false);

    const performSearch = useDebounceFn(async (q: string) => {
        if (q.trim().length < 2) {
            results.value = [];
            return;
        }

        loading.value = true;

        try {
            const url = search({ query: { q } }).url;
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            results.value = (await response.json()) as SearchResult[];
        } catch {
            results.value = [];
        } finally {
            loading.value = false;
        }
    }, 300);

    watch(query, (q) => performSearch(q));

    function clear() {
        query.value = '';
        results.value = [];
    }

    return { query, results, loading, clear };
}
