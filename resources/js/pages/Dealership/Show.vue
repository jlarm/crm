<script setup lang="ts">
import DealershipActivityTab from '@/components/dealership/show/DealershipActivityTab.vue';
import DealershipContactsTab from '@/components/dealership/show/DealershipContactsTab.vue';
import DealershipDetailsTab from '@/components/dealership/show/DealershipDetailsTab.vue';
import DealershipEmailsTab from '@/components/dealership/show/DealershipEmailsTab.vue';
import DealershipOpportunitiesTab from '@/components/dealership/show/DealershipOpportunitiesTab.vue';
import DealershipShowTabs from '@/components/dealership/show/DealershipShowTabs.vue';
import DealershipStoresTab from '@/components/dealership/show/DealershipStoresTab.vue';
import DealershipTasksTab from '@/components/dealership/show/DealershipTasksTab.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useRecentItems } from '@/composables/useRecentItems';
import { show } from '@/routes/dealerships';
import { destroy as unstar, store as star } from '@/routes/dealerships/star';
import { cn, ratingClass, statusClass } from '@/lib/utils';
import type { Dealership, DealershipShowTab, EmailThread, ProgressCategory, User } from '@/pages/Dealership/types';
import type { FilterOption, Task } from '@/pages/Tasks/types';
import { Head, router, usePage, usePoll } from '@inertiajs/vue3';
import { ArrowLeft, Star } from 'lucide-vue-next';
import { nextTick, ref, watch } from 'vue';

interface Props {
    dealership: Dealership;
    allUsers: User[];
    tagOptions: string[];
    tasks: Task[];
    taskFilterOptions: {
        types: FilterOption[];
        priorities: FilterOption[];
    };
    progressCategories: ProgressCategory[];
    /** Deferred: undefined until loaded. */
    emailThreads?: EmailThread[];
    unreadReplyCount: number;
    isStarred: boolean;
}

const props = defineProps<Props>();

const { recordRecentItem } = useRecentItems();

watch(
    () => props.dealership.id,
    () => {
        recordRecentItem({
            type: 'dealership',
            id: props.dealership.id,
            label: props.dealership.name,
            subtitle: [props.dealership.city, props.dealership.state].filter(Boolean).join(', ') || null,
            meta: null,
            url: show(props.dealership.id).url,
        });
    },
    { immediate: true },
);

const starred = ref(props.isStarred);
watch(() => props.isStarred, (value) => (starred.value = value));

/** Flips the star straight away and rolls back if the request fails. */
function toggleStar(): void {
    const wasStarred = starred.value;
    starred.value = !wasStarred;

    const options = {
        preserveScroll: true,
        preserveState: true,
        only: ['isStarred'],
        showProgress: false,
        onError: () => (starred.value = wasStarred),
    };

    if (wasStarred) {
        router.delete(unstar(props.dealership.id).url, options);
    } else {
        router.put(star(props.dealership.id).url, {}, options);
    }
}

const TABS: DealershipShowTab[] = ['details', 'stores', 'contacts', 'tasks', 'opportunities', 'emails', 'activity'];

/** The tab named in ?tab=, so a refresh, a shared link or a redirect back after saving lands on the same tab. */
function tabFromUrl(url: string): DealershipShowTab {
    const tab = new URL(url, 'http://localhost').searchParams.get('tab');

    return TABS.includes(tab as DealershipShowTab) ? (tab as DealershipShowTab) : 'details';
}

const page = usePage();

// Keeps the Emails tab's new-reply badge current while working on other tabs.
usePoll(10_000, { only: ['unreadReplyCount'], showProgress: false }, { mode: 'rest' });
const activeTab = ref<DealershipShowTab>(tabFromUrl(page.url));
const tabScrollPositions = ref<Record<DealershipShowTab, number>>({
    details: 0,
    stores: 0,
    contacts: 0,
    tasks: 0,
    opportunities: 0,
    emails: 0,
    activity: 0,
});

function goBack(): void {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/dashboard';
    }
}

function setActiveTab(tab: DealershipShowTab): void {
    tabScrollPositions.value[activeTab.value] = window.scrollY;
    const targetScrollTop = tabScrollPositions.value[tab] ?? window.scrollY;
    activeTab.value = tab;

    const url = new URL(page.url, window.location.origin);
    if (tab === 'details') {
        url.searchParams.delete('tab');
    } else {
        url.searchParams.set('tab', tab);
    }
    router.replace({ url: url.pathname + url.search, preserveScroll: true, preserveState: true });

    nextTick(() => {
        requestAnimationFrame(() => {
            window.scrollTo({ top: targetScrollTop, behavior: 'auto' });
        });
    });
}
</script>

<template>
    <Head :title="dealership.name" />

    <div class="px-8 py-3">
        <div class="flex shrink-0 items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <Button
                    variant="outline"
                    size="icon"
                    aria-label="Go back"
                    @click="goBack"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">
                            {{ dealership.name }}
                        </h1>
                        <button
                            type="button"
                            class="rounded-md p-1 transition-colors hover:bg-amber-50 dark:hover:bg-amber-950/40"
                            :aria-pressed="starred"
                            :aria-label="starred ? 'Unstar dealership' : 'Star dealership'"
                            :title="starred ? 'Starred: shown on your dashboard. Click to unstar.' : 'Star to keep it on your dashboard'"
                            @click="toggleStar"
                        >
                            <Star class="size-5" :class="starred ? 'fill-amber-400 text-amber-400' : 'text-slate-300 hover:text-amber-400 dark:text-slate-600'" />
                        </button>
                    </div>
                    <div class="mt-1 flex items-center gap-1">
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">
                            ID: {{ dealership.id }}
                        </p>
                        <Badge :class="cn('ml-2', statusClass(dealership.status))">
                            {{ dealership.status }}
                        </Badge>
                        <Badge :class="cn('ml-2', ratingClass(dealership.rating))">
                            {{ dealership.rating }}
                        </Badge>
                    </div>
                </div>
            </div>
        </div>

        <DealershipShowTabs
            :active-tab="activeTab"
            :badges="{ emails: unreadReplyCount }"
            @update:active-tab="setActiveTab"
        />

        <DealershipDetailsTab
            v-if="activeTab === 'details'"
            :dealership="dealership"
            :all-users="props.allUsers"
            :tag-options="tagOptions"
        />

        <DealershipStoresTab
            v-if="activeTab === 'stores'"
            :dealership="dealership"
        />

        <DealershipContactsTab
            v-if="activeTab === 'contacts'"
            :dealership="dealership"
        />

        <DealershipTasksTab
            v-if="activeTab === 'tasks'"
            :dealership="dealership"
            :tasks="tasks"
            :all-users="allUsers"
            :task-filter-options="taskFilterOptions"
        />

        <DealershipOpportunitiesTab
            v-if="activeTab === 'opportunities'"
            :dealership="dealership"
        />

        <DealershipEmailsTab
            v-if="activeTab === 'emails'"
            :dealership="dealership"
            :threads="emailThreads"
        />

        <DealershipActivityTab
            v-if="activeTab === 'activity'"
            :dealership="dealership"
            :progress-categories="progressCategories"
        />
    </div>
</template>
