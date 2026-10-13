<script setup lang="ts">
import { formatPhone } from '@/lib/phone';
import { ArrowRight, Sparkles, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import type { CallNoteAnalysis, Contact } from '@/pages/Dealership/types';

const props = defineProps<{
    analysis: CallNoteAnalysis;
    contacts: Contact[];
    currentStageLabel: string;
}>();

defineEmits<{
    (e: 'dismiss'): void;
}>();

const useSummary = ref(true);
const useStage = ref(true);
const useRating = ref(true);
const selectedTasks = ref<boolean[]>([]);
const selectedContacts = ref<boolean[]>([]);

watch(
    () => props.analysis,
    (analysis) => {
        useSummary.value = analysis.summary !== '';
        useStage.value = analysis.stage !== null;
        useRating.value = analysis.rating !== null;
        selectedTasks.value = analysis.tasks.map(() => true);
        selectedContacts.value = analysis.newContacts.map(() => true);
    },
    { immediate: true },
);

const hasSuggestions = computed(
    () =>
        props.analysis.summary !== '' ||
        props.analysis.tasks.length > 0 ||
        props.analysis.stage !== null ||
        props.analysis.rating !== null ||
        props.analysis.newContacts.length > 0,
);

function contactName(id: number | null): string | null {
    return props.contacts.find((contact) => contact.id === id)?.name ?? null;
}

function formatDate(value: string): string {
    return new Date(`${value}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function typeLabel(value: string): string {
    return value.replace('_', ' ').replace(/^\w/, (c) => c.toUpperCase());
}
</script>

<template>
    <div class="rounded-lg border border-violet-200 bg-violet-50/60 p-3 dark:border-violet-900 dark:bg-violet-950/30">
        <div class="mb-2 flex items-center justify-between">
            <p class="flex items-center gap-1.5 text-xs font-semibold text-violet-800 dark:text-violet-300">
                <Sparkles class="h-3.5 w-3.5" />
                Suggested updates
            </p>
            <button
                type="button"
                class="text-violet-400 hover:text-violet-700 dark:hover:text-violet-200"
                aria-label="Dismiss suggestions"
                @click="$emit('dismiss')"
            >
                <X class="h-3.5 w-3.5" />
            </button>
        </div>

        <p v-if="!hasSuggestions" class="text-xs text-slate-600 dark:text-slate-400">
            Nothing to suggest from these notes.
        </p>

        <div v-else class="space-y-3 text-sm">
            <p class="text-xs text-slate-500">Uncheck anything you don't want. Checked items are saved when you click Log.</p>

            <label v-if="analysis.summary" class="flex items-start gap-2.5">
                <Checkbox class="mt-0.5" :model-value="useSummary" @update:model-value="(v) => (useSummary = v === true)" />
                <span class="min-w-0">
                    <span class="block text-xs font-medium text-slate-500">Add summary above your notes</span>
                    <span class="block whitespace-pre-wrap text-slate-800 dark:text-slate-200">{{ analysis.summary }}</span>
                </span>
            </label>
            <input v-if="analysis.summary && useSummary" type="hidden" name="apply[summary]" :value="analysis.summary" />

            <div v-if="analysis.tasks.length > 0">
                <p class="mb-1 text-xs font-medium text-slate-500">Create tasks</p>
                <label v-for="(task, index) in analysis.tasks" :key="index" class="flex items-start gap-2.5 py-0.5">
                    <Checkbox
                        class="mt-0.5"
                        :model-value="selectedTasks[index]"
                        @update:model-value="(v) => (selectedTasks[index] = v === true)"
                    />
                    <span class="min-w-0">
                        <span class="text-slate-800 dark:text-slate-200">{{ task.title }}</span>
                        <span class="block text-xs text-slate-500">
                            {{ typeLabel(task.type) }} · {{ task.priority }} priority<template v-if="task.dueDate"> · due {{ formatDate(task.dueDate) }}</template><template v-if="contactName(task.contactId)"> · with {{ contactName(task.contactId) }}</template>
                        </span>
                    </span>
                    <template v-if="selectedTasks[index]">
                        <input type="hidden" :name="`apply[tasks][${index}][title]`" :value="task.title" />
                        <input type="hidden" :name="`apply[tasks][${index}][type]`" :value="task.type" />
                        <input type="hidden" :name="`apply[tasks][${index}][priority]`" :value="task.priority" />
                        <input type="hidden" :name="`apply[tasks][${index}][due_date]`" :value="task.dueDate ?? ''" />
                        <input type="hidden" :name="`apply[tasks][${index}][contact_id]`" :value="task.contactId ?? ''" />
                    </template>
                </label>
            </div>

            <label v-if="analysis.stage" class="flex items-start gap-2.5">
                <Checkbox class="mt-0.5" :model-value="useStage" @update:model-value="(v) => (useStage = v === true)" />
                <span class="min-w-0">
                    <span class="flex items-center gap-1.5 text-slate-800 dark:text-slate-200">
                        Move stage {{ currentStageLabel }} <ArrowRight class="h-3 w-3" /> {{ analysis.stage.label }}
                    </span>
                    <span v-if="analysis.stage.reason" class="block text-xs text-slate-500">{{ analysis.stage.reason }}</span>
                </span>
            </label>
            <input v-if="analysis.stage && useStage" type="hidden" name="apply[stage]" :value="analysis.stage.value" />

            <label v-if="analysis.rating" class="flex items-start gap-2.5">
                <Checkbox class="mt-0.5" :model-value="useRating" @update:model-value="(v) => (useRating = v === true)" />
                <span class="min-w-0">
                    <span class="text-slate-800 dark:text-slate-200">Set dealership rating to {{ analysis.rating.label }}</span>
                    <span v-if="analysis.rating.reason" class="block text-xs text-slate-500">{{ analysis.rating.reason }}</span>
                </span>
            </label>
            <input v-if="analysis.rating && useRating" type="hidden" name="apply[rating]" :value="analysis.rating.value" />

            <div v-if="analysis.newContacts.length > 0">
                <p class="mb-1 text-xs font-medium text-slate-500">Add contacts</p>
                <label v-for="(contact, index) in analysis.newContacts" :key="index" class="flex items-start gap-2.5 py-0.5">
                    <Checkbox
                        class="mt-0.5"
                        :model-value="selectedContacts[index]"
                        @update:model-value="(v) => (selectedContacts[index] = v === true)"
                    />
                    <span class="min-w-0">
                        <span class="text-slate-800 dark:text-slate-200">{{ contact.name }}</span>
                        <span class="block text-xs text-slate-500">
                            {{ [contact.position, contact.email, formatPhone(contact.phone)].filter(Boolean).join(' · ') || 'No other details' }}
                        </span>
                    </span>
                    <template v-if="selectedContacts[index]">
                        <input type="hidden" :name="`apply[contacts][${index}][name]`" :value="contact.name" />
                        <input type="hidden" :name="`apply[contacts][${index}][position]`" :value="contact.position" />
                        <input type="hidden" :name="`apply[contacts][${index}][email]`" :value="contact.email" />
                        <input type="hidden" :name="`apply[contacts][${index}][phone]`" :value="contact.phone" />
                    </template>
                </label>
            </div>
        </div>
    </div>
</template>
