<script setup lang="ts">
import DealershipContactsTab from '@/components/dealership/show/DealershipContactsTab.vue';
import DealershipDetailsTab from '@/components/dealership/show/DealershipDetailsTab.vue';
import DealershipOpportunitiesTab from '@/components/dealership/show/DealershipOpportunitiesTab.vue';
import DealershipShowTabs from '@/components/dealership/show/DealershipShowTabs.vue';
import DealershipStoresTab from '@/components/dealership/show/DealershipStoresTab.vue';
import DealershipTasksTab from '@/components/dealership/show/DealershipTasksTab.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn, ratingClass, statusClass } from '@/lib/utils';
import type { Dealership, DealershipShowTab, User } from '@/pages/Dealership/types';
import type { FilterOption, Task } from '@/pages/Tasks/types';
import { Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { nextTick, ref } from 'vue';

interface Props {
    dealership: Dealership;
    allUsers: User[];
    tasks: Task[];
    taskFilterOptions: {
        types: FilterOption[];
        priorities: FilterOption[];
    };
}

const props = defineProps<Props>();

const activeTab = ref<DealershipShowTab>('details');
const tabScrollPositions = ref<Record<DealershipShowTab, number>>({
    details: 0,
    stores: 0,
    contacts: 0,
    tasks: 0,
    opportunities: 0,
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
                    <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">
                        {{ dealership.name }}
                    </h1>
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
            @update:active-tab="setActiveTab"
        />

        <DealershipDetailsTab
            v-if="activeTab === 'details'"
            :dealership="dealership"
            :all-users="props.allUsers"
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
    </div>
</template>
