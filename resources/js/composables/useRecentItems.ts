import { ref } from 'vue';
import type { SearchResult } from '@/composables/useSearch';

const STORAGE_KEY = 'search.recent';
const MAX_RECENT_ITEMS = 6;

function readStoredItems(): SearchResult[] {
    try {
        const stored = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]');

        return Array.isArray(stored) ? (stored as SearchResult[]) : [];
    } catch {
        return [];
    }
}

const recentItems = ref<SearchResult[]>(readStoredItems());

/**
 * Most recently opened dealerships, contacts, stores and tasks, kept per browser.
 */
export function useRecentItems() {
    function recordRecentItem(item: SearchResult) {
        recentItems.value = [
            item,
            ...recentItems.value.filter(
                (existing) => !(existing.type === item.type && existing.id === item.id),
            ),
        ].slice(0, MAX_RECENT_ITEMS);

        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(recentItems.value));
        } catch {
            // Storage unavailable (private mode, quota); keep the in-memory list.
        }
    }

    return { recentItems, recordRecentItem };
}
