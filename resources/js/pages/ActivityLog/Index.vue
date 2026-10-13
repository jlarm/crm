<script setup lang="ts">
import PaginationNav from '@/components/PaginationNav.vue';
import { Button } from '@/components/ui/button';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/activity-log';
import { Head, Link, router } from '@inertiajs/vue3';
import { ExternalLink, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Change {
    field: string;
    old: string | null;
    new: string | null;
}

interface Entry {
    id: number;
    description: string;
    event: 'created' | 'updated' | 'deleted' | null;
    type: string | null;
    subject: string | null;
    url: string | null;
    actor: { id: number; name: string } | null;
    changes: Change[];
    occurredAt: string | null;
}

interface Option {
    value: string;
    label: string;
}

interface Filters {
    user: string;
    type: string;
    event: string;
    from: string | null;
    to: string | null;
    search: string;
}

const props = defineProps<{
    entries: {
        data: Entry[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        total: number;
        from: number | null;
        to: number | null;
    };
    filters: Filters;
    users: Array<{ id: number; name: string }>;
    types: Option[];
    retentionDays: number;
}>();

/** Select items can't hold an empty value, so "any" stands in for no filter. */
const ANY = 'any';

const search = ref(props.filters.search);
const user = ref(props.filters.user || ANY);
const type = ref(props.filters.type || ANY);
const event = ref(props.filters.event || ANY);
const from = ref<string | null>(props.filters.from);
const to = ref<string | null>(props.filters.to);

const expanded = ref<Set<number>>(new Set());

const hasFilters = computed(
    () => search.value !== '' || user.value !== ANY || type.value !== ANY || event.value !== ANY || from.value !== null || to.value !== null,
);

function apply(): void {
    const query: Record<string, string> = {};

    if (search.value.trim() !== '') {
        query.search = search.value.trim();
    }
    if (user.value !== ANY) {
        query.user = user.value;
    }
    if (type.value !== ANY) {
        query.type = type.value;
    }
    if (event.value !== ANY) {
        query.event = event.value;
    }
    if (from.value) {
        query.from = from.value;
    }
    if (to.value) {
        query.to = to.value;
    }

    router.get(index().url, query, { preserveState: true, preserveScroll: true, replace: true });
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(search, () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(apply, 300);
});

watch([user, type, event, from, to], apply);

function clearFilters(): void {
    search.value = '';
    user.value = ANY;
    type.value = ANY;
    event.value = ANY;
    from.value = null;
    to.value = null;
}

function toggle(id: number): void {
    const next = new Set(expanded.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }
    expanded.value = next;
}

/** Edits show their first few changes; a created or deleted record's fields stay folded away. */
function visibleChanges(entry: Entry): Change[] {
    if (expanded.value.has(entry.id)) {
        return entry.changes;
    }

    return entry.event === 'updated' ? entry.changes.slice(0, 3) : [];
}

function toggleLabel(entry: Entry): string | null {
    const hidden = entry.changes.length - visibleChanges(entry).length;

    if (expanded.value.has(entry.id)) {
        return entry.changes.length > 3 || entry.event !== 'updated' ? 'Hide details' : null;
    }

    if (hidden <= 0) {
        return null;
    }

    return entry.event === 'updated' ? `Show ${hidden} more` : `Show details (${hidden})`;
}

/** "updated contact" for record changes, or the logged message for things like logins. */
function action(entry: Entry): string {
    if (entry.event) {
        return `${entry.event} ${(entry.type ?? 'record').toLowerCase()}`;
    }

    return entry.actor ? entry.description.charAt(0).toLowerCase() + entry.description.slice(1) : entry.description;
}

function formatDateTime(iso: string | null): string {
    if (!iso) {
        return '—';
    }

    return new Date(iso).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

const eventDotClasses: Record<string, string> = {
    created: 'bg-emerald-500',
    updated: 'bg-sky-500',
    deleted: 'bg-red-500',
};
</script>

<template>
    <Head title="Activity log" />

    <div class="space-y-6 p-6 pb-24">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">Activity log</h1>
            <p class="mt-1 text-sm text-slate-500">
                Every change made in the CRM, by whom and when. Entries are kept for {{ retentionDays }} days.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <Input v-model="search" placeholder="Search descriptions and values..." class="w-72" />

            <Select v-model="user">
                <SelectTrigger class="w-44" aria-label="User">
                    <SelectValue placeholder="Anyone" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ANY">Anyone</SelectItem>
                    <SelectItem v-for="option in users" :key="option.id" :value="String(option.id)">
                        {{ option.name }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="type">
                <SelectTrigger class="w-44" aria-label="Record type">
                    <SelectValue placeholder="All records" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ANY">All records</SelectItem>
                    <SelectItem v-for="option in types" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="event">
                <SelectTrigger class="w-36" aria-label="Change">
                    <SelectValue placeholder="Any change" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ANY">Any change</SelectItem>
                    <SelectItem value="created">Created</SelectItem>
                    <SelectItem value="updated">Updated</SelectItem>
                    <SelectItem value="deleted">Deleted</SelectItem>
                </SelectContent>
            </Select>

            <DatePicker v-model="from" placeholder="From" class="w-40" />
            <DatePicker v-model="to" placeholder="To" class="w-40" />

            <Button v-if="hasFilters" variant="ghost" size="sm" @click="clearFilters">
                <X class="size-4" />
                Clear
            </Button>
        </div>

        <div class="rounded-md border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
            <p v-if="entries.data.length === 0" class="py-12 text-center text-sm text-slate-400">
                No activity matches these filters.
            </p>

            <ul v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                <li v-for="entry in entries.data" :key="entry.id" class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:gap-4">
                    <time class="shrink-0 pt-px text-xs text-slate-500 tabular-nums sm:w-36" :datetime="entry.occurredAt ?? undefined">
                        {{ formatDateTime(entry.occurredAt) }}
                    </time>

                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-baseline gap-x-1.5 text-sm text-slate-600 dark:text-slate-300">
                            <span
                                class="mr-0.5 inline-block size-2 shrink-0 self-center rounded-full"
                                :class="entry.event ? eventDotClasses[entry.event] : 'bg-slate-300 dark:bg-slate-600'"
                                aria-hidden="true"
                            />
                            <span v-if="entry.actor" class="font-semibold text-slate-900 dark:text-slate-100">{{ entry.actor.name }}</span>
                            <span v-else-if="entry.event" class="text-slate-400">System</span>
                            <span>{{ action(entry) }}</span>
                            <template v-if="entry.subject">
                                <Link
                                    v-if="entry.url"
                                    :href="entry.url"
                                    class="inline-flex items-center gap-1 font-semibold text-slate-900 hover:underline dark:text-slate-100"
                                >
                                    {{ entry.subject }}
                                    <ExternalLink class="size-3 text-slate-400" />
                                </Link>
                                <span v-else class="font-semibold text-slate-900 dark:text-slate-100">{{ entry.subject }}</span>
                            </template>
                        </p>

                        <dl
                            v-if="visibleChanges(entry).length"
                            class="mt-2 grid grid-cols-[minmax(0,9rem)_minmax(0,1fr)] gap-x-4 gap-y-1 rounded-md bg-slate-50 px-3 py-2 text-xs dark:bg-slate-900/60"
                        >
                            <template v-for="change in visibleChanges(entry)" :key="change.field">
                                <dt class="truncate text-slate-500 capitalize" :title="change.field">{{ change.field }}</dt>
                                <dd class="min-w-0 break-words text-slate-800 dark:text-slate-200">
                                    <template v-if="entry.event === 'updated'">
                                        <span class="text-slate-400 line-through">{{ change.old ?? 'empty' }}</span>
                                        <span class="px-1 text-slate-400">→</span>
                                        <span>{{ change.new ?? 'empty' }}</span>
                                    </template>
                                    <template v-else>{{ change.new ?? change.old }}</template>
                                </dd>
                            </template>
                        </dl>

                        <button
                            v-if="toggleLabel(entry)"
                            type="button"
                            class="mt-1.5 text-xs font-medium text-orange-600 hover:underline"
                            @click="toggle(entry.id)"
                        >
                            {{ toggleLabel(entry) }}
                        </button>
                    </div>
                </li>
            </ul>
        </div>

        <PaginationNav :paginator="entries" />
    </div>
</template>
