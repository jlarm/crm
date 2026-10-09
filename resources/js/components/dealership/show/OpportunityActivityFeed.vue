<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import CallNoteSuggestions from '@/components/dealership/show/CallNoteSuggestions.vue';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Button } from '@/components/ui/button';
import { Field, FieldLabel } from '@/components/ui/field';
import { Textarea } from '@/components/ui/textarea';
import type { Activity, CallNoteAnalysis, Dealership, Opportunity } from '@/pages/Dealership/types';
import analyzeCallNotes from '@/actions/App/Http/Controllers/OpportunityActivityAnalysisController';
import { Form, router, useHttp } from '@inertiajs/vue3';
import { FileText, Mail, Phone, Sparkles, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    dealership: Dealership;
    opportunity: Opportunity;
}>();

const activityTypes = [
    { value: 'call', label: 'Call' },
    { value: 'note', label: 'Note' },
    { value: 'email', label: 'Email' },
];

const typeConfig: Record<string, { icon: unknown; classes: string; dot: string }> = {
    call: {
        icon: Phone,
        classes: 'bg-blue-100 text-blue-700',
        dot: 'bg-blue-500',
    },
    note: {
        icon: FileText,
        classes: 'bg-slate-100 text-slate-700',
        dot: 'bg-slate-400',
    },
    email: {
        icon: Mail,
        classes: 'bg-violet-100 text-violet-700',
        dot: 'bg-violet-500',
    },
};

function deleteActivity(activity: Activity): void {
    if (!window.confirm('Delete this activity?')) {
        return;
    }
    router.delete(
        `/dealerships/${props.dealership.id}/opportunities/${props.opportunity.id}/activities/${activity.id}`,
        { preserveScroll: true },
    );
}

const occurredAt = ref<string | null>(null);
const notes = ref('');
const analysis = ref<CallNoteAnalysis | null>(null);
const analysisError = ref<string | null>(null);

/** Matches the server's minimum so the button only enables when a request can succeed. */
const MIN_NOTES_LENGTH = 20;

const analyzer = useHttp<{ notes: string }, CallNoteAnalysis>({ notes: '' });

const canAnalyze = computed(() => notes.value.trim().length >= MIN_NOTES_LENGTH && !analyzer.processing);

function analyze(): void {
    analysisError.value = null;
    analyzer.notes = notes.value;
    analyzer.post(
        analyzeCallNotes({ dealership: props.dealership.id, opportunity: props.opportunity.id }).url,
        {
            onSuccess: (response: CallNoteAnalysis) => {
                analysis.value = response;
            },
            onHttpException: (response) => {
                analysisError.value = errorMessageFrom(response.data);
            },
        },
    );
}

function errorMessageFrom(body: string): string {
    try {
        const parsed: unknown = JSON.parse(body);

        if (parsed && typeof parsed === 'object' && 'message' in parsed && typeof parsed.message === 'string') {
            return parsed.message;
        }
    } catch {
        // Not JSON; fall through to the generic message.
    }

    return 'The AI could not analyze these notes right now.';
}

function resetForm(): void {
    occurredAt.value = null;
    notes.value = '';
    analysis.value = null;
    analysisError.value = null;
}

function displayDate(activity: Activity): string {
    return activity.occurredAt ?? activity.createdAt;
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <!-- Log new activity -->
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
            <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Log Activity</p>
            <Form
                :action="`/dealerships/${dealership.id}/opportunities/${opportunity.id}/activities`"
                method="post"
                class="flex flex-col gap-3"
                reset-on-success
                preserve-scroll
                :on-success="resetForm"
                v-slot="{ errors, processing }"
            >
                <div class="flex gap-2">
                    <button
                        v-for="t in activityTypes"
                        :key="t.value"
                        type="button"
                        class="relative"
                    >
                        <!-- hidden radio drives the value -->
                        <input
                            type="radio"
                            name="type"
                            :value="t.value"
                            :id="`type_${opportunity.id}_${t.value}`"
                            class="peer sr-only"
                            :checked="t.value === 'note'"
                        />
                        <label
                            :for="`type_${opportunity.id}_${t.value}`"
                            class="flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition-colors peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-700 dark:peer-checked:bg-orange-950 dark:peer-checked:text-orange-300"
                            :class="typeConfig[t.value]?.classes ?? ''"
                        >
                            <component :is="typeConfig[t.value]?.icon" class="h-3 w-3" />
                            {{ t.label }}
                        </label>
                    </button>
                </div>
                <InputError :message="errors.type" />

                <Field>
                    <FieldLabel :for="`details_${opportunity.id}`">Details</FieldLabel>
                    <Textarea
                        :id="`details_${opportunity.id}`"
                        v-model="notes"
                        name="details"
                        :rows="3"
                        placeholder="What happened? Paste call notes and click Analyze to turn them into tasks and updates."
                        required
                    />
                    <InputError :message="errors.details" />
                </Field>

                <div v-if="analyzer.processing" class="space-y-2 rounded-lg border border-violet-200 p-3 dark:border-violet-900">
                    <div class="h-3 w-1/3 animate-pulse rounded bg-violet-100 dark:bg-violet-900/50" />
                    <div class="h-3 w-full animate-pulse rounded bg-violet-100 dark:bg-violet-900/50" />
                    <div class="h-3 w-2/3 animate-pulse rounded bg-violet-100 dark:bg-violet-900/50" />
                </div>
                <p v-else-if="analysisError" class="text-xs text-red-600 dark:text-red-400">{{ analysisError }}</p>
                <InputError v-if="analyzer.errors.notes" :message="analyzer.errors.notes" />
                <CallNoteSuggestions
                    v-if="analysis && !analyzer.processing"
                    :analysis="analysis"
                    :contacts="dealership.contacts"
                    :current-stage-label="opportunity.stageLabel"
                    @dismiss="analysis = null"
                />
                <InputError :message="Object.entries(errors).find(([key]) => key.startsWith('apply'))?.[1]" />

                <Field>
                    <FieldLabel>Date (optional)</FieldLabel>
                    <DatePicker
                        v-model="occurredAt"
                        placeholder="Pick a date"
                    />
                    <input type="hidden" name="occurred_at" :value="occurredAt ?? ''" />
                </Field>

                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        :disabled="!canAnalyze"
                        :title="canAnalyze ? 'Suggest tasks, stage and contact updates from these notes' : 'Write a few sentences of notes first'"
                        @click="analyze"
                    >
                        <Sparkles class="h-3.5 w-3.5" />
                        {{ analyzer.processing ? 'Analyzing…' : 'Analyze' }}
                    </Button>
                    <Button type="submit" size="sm" :disabled="processing || analyzer.processing">Log</Button>
                </div>
            </Form>
        </div>

        <!-- Timeline -->
        <div v-if="opportunity.activities.length > 0" class="relative">
            <!-- vertical line -->
            <div class="absolute left-3 top-0 h-full w-px bg-slate-200 dark:bg-slate-700" />

            <ul class="space-y-5 pl-9">
                <li
                    v-for="activity in opportunity.activities"
                    :key="activity.id"
                    class="relative"
                >
                    <!-- dot -->
                    <span
                        class="absolute -left-[1.375rem] top-1.5 h-2.5 w-2.5 rounded-full border-2 border-white dark:border-slate-900"
                        :class="typeConfig[activity.type]?.dot ?? 'bg-slate-400'"
                    />

                    <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-xs dark:border-slate-700 dark:bg-slate-900">
                        <div class="mb-1.5 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="typeConfig[activity.type]?.classes ?? 'bg-slate-100 text-slate-700'"
                                >
                                    <component :is="typeConfig[activity.type]?.icon" class="h-3 w-3" />
                                    {{ activity.typeLabel }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    {{ activity.user.name }} &middot; {{ displayDate(activity) }}
                                </span>
                            </div>
                            <button
                                type="button"
                                class="text-slate-300 transition hover:text-red-500 dark:text-slate-600"
                                aria-label="Delete activity"
                                @click="deleteActivity(activity)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <p class="whitespace-pre-wrap text-sm text-slate-700 dark:text-slate-300">{{ activity.details }}</p>
                    </div>
                </li>
            </ul>
        </div>

        <p v-else class="text-center text-xs text-muted-foreground">
            No activity yet. Log the first one above.
        </p>
    </div>
</template>
