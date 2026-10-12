<script setup lang="ts">
import LogActivityDialog from '@/components/dealership/show/LogActivityDialog.vue';
import { Button } from '@/components/ui/button';
import type { ActivityFeedItem, ActivityFeedMeta, Dealership, ProgressCategory } from '@/pages/Dealership/types';
import { destroy as destroyProgress } from '@/routes/dealerships/progress';
import { router } from '@inertiajs/vue3';
import {
    Activity as ActivityIcon,
    Briefcase,
    Building2,
    FileText,
    Mail,
    Phone,
    Plus,
    Sparkles,
    Store as StoreIcon,
    Trash2,
    User as UserIcon,
    Users,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    dealership: Dealership;
    progressCategories: ProgressCategory[];
}>();

const isLogOpen = ref(false);

const items = ref<ActivityFeedItem[]>([]);
const meta = ref<ActivityFeedMeta | null>(null);
const loading = ref(false);
const error = ref<string | null>(null);

const iconMap: Record<string, unknown> = {
    contact: UserIcon,
    store: StoreIcon,
    opportunity: Briefcase,
    note: FileText,
    sparkle: Sparkles,
    building: Building2,
    call: Phone,
    email: Mail,
    meeting: Users,
    activity: ActivityIcon,
};

function iconFor(name: string): unknown {
    return iconMap[name] ?? ActivityIcon;
}

async function load(page = 1): Promise<void> {
    if (loading.value) {
        return;
    }
    loading.value = true;
    error.value = null;
    try {
        const response = await fetch(
            `/dealerships/${props.dealership.id}/activities?page=${page}&per_page=25`,
            { headers: { Accept: 'application/json' } },
        );
        if (!response.ok) {
            throw new Error(`Request failed: ${response.status}`);
        }
        const json = (await response.json()) as { data: ActivityFeedItem[]; meta: ActivityFeedMeta };
        items.value = page === 1 ? json.data : [...items.value, ...json.data];
        meta.value = json.meta;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load activity';
    } finally {
        loading.value = false;
    }
}

function loadMore(): void {
    if (meta.value?.hasMore) {
        load(meta.value.currentPage + 1);
    }
}

function formatDateTime(item: ActivityFeedItem): string {
    const date = new Date(item.occurredAt);

    if (item.dateOnly) {
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    }

    return date.toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function deleteLoggedActivity(progressId: number): void {
    if (!window.confirm('Delete this activity?')) {
        return;
    }

    router.delete(destroyProgress([props.dealership.id, progressId]).url, {
        preserveScroll: true,
        onSuccess: () => load(1),
    });
}

onMounted(() => load(1));
</script>

<template>
    <div class="mt-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Activity</h2>
            <Button @click="isLogOpen = true">
                <Plus class="h-4 w-4" />
                Log activity
            </Button>
        </div>

        <LogActivityDialog
            v-model:open="isLogOpen"
            :dealership="dealership"
            :categories="progressCategories"
            @logged="load(1)"
        />

        <div v-if="error" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
            {{ error }}
        </div>

        <div v-if="loading && items.length === 0" class="space-y-3">
            <div
                v-for="i in 4"
                :key="i"
                class="h-16 animate-pulse rounded-lg bg-slate-100 dark:bg-slate-800/60"
            />
        </div>

        <div v-else-if="items.length === 0" class="rounded-lg border border-dashed border-slate-200 p-10 text-center dark:border-slate-700">
            <ActivityIcon class="mx-auto h-8 w-8 text-slate-400" />
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No activity yet for this dealership.</p>
        </div>

        <div v-else class="relative">
            <div class="absolute left-4 top-2 bottom-2 w-px bg-slate-200 dark:bg-slate-700" />

            <ul class="space-y-4">
                <li
                    v-for="item in items"
                    :key="item.id"
                    class="group relative flex gap-4"
                >
                    <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                        <component :is="iconFor(item.icon)" class="h-4 w-4" />
                    </span>

                    <div class="min-w-0 flex-1 pb-1">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ item.title }}
                        </p>
                        <p
                            v-if="item.description"
                            class="mt-0.5 text-sm text-slate-600 dark:text-slate-400"
                        >
                            {{ item.description }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                            <span v-if="item.actor">{{ item.actor.name }} · </span>
                            {{ formatDateTime(item) }}
                        </p>
                    </div>

                    <Button
                        v-if="item.progressId"
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 shrink-0 text-slate-400 opacity-0 transition-opacity group-hover:opacity-100 focus-visible:opacity-100"
                        aria-label="Delete activity"
                        @click="deleteLoggedActivity(item.progressId)"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </li>
            </ul>

            <div v-if="meta?.hasMore" class="mt-6 flex justify-center">
                <Button variant="outline" size="sm" :disabled="loading" @click="loadMore">
                    {{ loading ? 'Loading…' : 'Load more' }}
                </Button>
            </div>
        </div>
    </div>
</template>
