<script setup lang="ts">
import type { DealershipShowTab } from '@/pages/Dealership/types';

defineProps<{
    activeTab: DealershipShowTab;
    /** Counts shown next to a tab's label, e.g. unread email replies. */
    badges?: Partial<Record<DealershipShowTab, number>>;
}>();

const emit = defineEmits<{
    (event: 'update:activeTab', value: DealershipShowTab): void;
}>();
</script>

<template>
    <div class="mt-6 border-b border-slate-200 dark:border-slate-800">
        <nav class="flex gap-8 text-sm font-medium">
            <button
                v-for="{ tab, label } in [
                    { tab: 'details', label: 'Info' },
                    { tab: 'stores', label: 'Stores' },
                    { tab: 'contacts', label: 'Contacts' },
                    { tab: 'tasks', label: 'Tasks' },
                    { tab: 'opportunities', label: 'Opportunities' },
                    { tab: 'emails', label: 'Emails' },
                    { tab: 'activity', label: 'Activity' },
                ] as { tab: DealershipShowTab; label: string }[]"
                :key="tab"
                type="button"
                class="cursor-pointer pb-3 transition-colors"
                :class="
                    activeTab === tab
                        ? 'border-b-2 border-orange-500 text-orange-600'
                        : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-200'
                "
                @click="emit('update:activeTab', tab)"
            >
                <span class="inline-flex items-center gap-1.5">
                    {{ label }}
                    <span
                        v-if="badges?.[tab]"
                        class="inline-flex min-w-5 items-center justify-center rounded-full bg-orange-500 px-1.5 text-[11px] leading-5 font-semibold text-white"
                        :aria-label="`${badges[tab]} new`"
                    >
                        {{ badges[tab] }}
                    </span>
                </span>
            </button>
        </nav>
    </div>
</template>
