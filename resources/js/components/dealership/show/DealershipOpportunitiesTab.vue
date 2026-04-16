<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import OpportunityActivityFeed from '@/components/dealership/show/OpportunityActivityFeed.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import type { Dealership, Opportunity } from '@/pages/Dealership/types';
import { Form, router } from '@inertiajs/vue3';
import { MoreVertical } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    dealership: Dealership;
}>();

const stages = [
    { value: 'prospect', label: 'Prospect' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'demo', label: 'Demo' },
    { value: 'proposal', label: 'Proposal' },
    { value: 'negotiation', label: 'Negotiation' },
    { value: 'won', label: 'Won' },
    { value: 'lost', label: 'Lost' },
];

const stageBadgeClass: Record<string, string> = {
    prospect: 'bg-slate-100 text-slate-700',
    contacted: 'bg-sky-100 text-sky-700',
    qualified: 'bg-blue-100 text-blue-700',
    demo: 'bg-indigo-100 text-indigo-700',
    proposal: 'bg-amber-100 text-amber-700',
    negotiation: 'bg-orange-100 text-orange-700',
    won: 'bg-green-100 text-green-700',
    lost: 'bg-red-100 text-red-700',
};

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isActivityOpen = ref(false);
const editingOpportunity = ref<Opportunity | null>(null);
const activityOpportunityId = ref<number | null>(null);

// Derived from props so it stays fresh after Inertia re-renders
const activityOpportunity = computed(() =>
    activityOpportunityId.value !== null
        ? (props.dealership.opportunities.find((o) => o.id === activityOpportunityId.value) ?? null)
        : null,
);

function openEdit(opportunity: Opportunity): void {
    editingOpportunity.value = opportunity;
    isEditOpen.value = true;
}

function openActivity(opportunity: Opportunity): void {
    activityOpportunityId.value = opportunity.id;
    isActivityOpen.value = true;
}

function deleteOpportunity(dealershipId: number, opportunity: Opportunity): void {
    if (!window.confirm('Delete this opportunity?')) {
        return;
    }
    router.delete(`/dealerships/${dealershipId}/opportunities/${opportunity.id}`);
}

function formatCurrency(value: number): string {
    if (value >= 1_000_000) {
        return `$${(value / 1_000_000).toFixed(1)}M`;
    }
    if (value >= 1_000) {
        return `$${(value / 1_000).toFixed(0)}K`;
    }
    return `$${value.toFixed(0)}`;
}
</script>

<template>
    <div class="mx-auto mt-6 w-full">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Opportunities</h2>
            <Dialog v-model:open="isCreateOpen">
                <DialogTrigger as-child>
                    <Button>Create Opportunity</Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>Create Opportunity</DialogTitle>
                        <DialogDescription>Add a new opportunity for this dealership.</DialogDescription>
                    </DialogHeader>
                    <Form
                        :action="`/dealerships/${dealership.id}/opportunities`"
                        method="post"
                        class="grid grid-cols-2 gap-4"
                        reset-on-success
                        :on-success="() => (isCreateOpen = false)"
                        v-slot="{ errors, processing }"
                    >
                        <Field class="col-span-2">
                            <FieldLabel for="opp_name">Name</FieldLabel>
                            <Input id="opp_name" name="name" required />
                            <InputError :message="errors.name" />
                        </Field>
                        <Field>
                            <FieldLabel for="opp_stage">Stage</FieldLabel>
                            <select
                                id="opp_stage"
                                name="stage"
                                required
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            >
                                <option
                                    v-for="stage in stages"
                                    :key="stage.value"
                                    :value="stage.value"
                                >
                                    {{ stage.label }}
                                </option>
                            </select>
                            <InputError :message="errors.stage" />
                        </Field>
                        <Field>
                            <FieldLabel for="opp_estimated_value">Estimated Value ($)</FieldLabel>
                            <Input id="opp_estimated_value" name="estimated_value" type="number" min="0" step="0.01" placeholder="0.00" />
                            <InputError :message="errors.estimated_value" />
                        </Field>
                        <Field>
                            <FieldLabel for="opp_probability">Probability (%)</FieldLabel>
                            <Input id="opp_probability" name="probability" type="number" min="0" max="100" placeholder="0–100" />
                        </Field>
                        <Field>
                            <FieldLabel for="opp_close_date">Expected Close Date</FieldLabel>
                            <Input id="opp_close_date" name="expected_close_date" type="date" />
                        </Field>
                        <Field class="col-span-2">
                            <FieldLabel for="opp_next_action">Next Action</FieldLabel>
                            <Input id="opp_next_action" name="next_action" />
                        </Field>
                        <DialogFooter class="col-span-2">
                            <Button :disabled="processing">Create</Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="opportunity in dealership.opportunities"
                :key="opportunity.id"
                class="rounded-2xl border border-slate-200 shadow-sm transition hover:shadow-md dark:border-slate-800"
            >
                <CardHeader>
                    <div class="flex w-full items-start justify-between gap-4">
                        <div class="flex flex-col gap-1">
                            <CardTitle class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ opportunity.name }}
                            </CardTitle>
                            <span
                                class="inline-flex w-fit items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="stageBadgeClass[opportunity.stage] ?? 'bg-slate-100 text-slate-700'"
                            >
                                {{ opportunity.stageLabel }}
                            </span>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="text-slate-500 hover:text-slate-700"
                                    aria-label="Opportunity actions"
                                >
                                    <MoreVertical class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="openActivity(opportunity)">Activity</DropdownMenuItem>
                                <DropdownMenuItem @click="openEdit(opportunity)">Edit</DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem
                                    class="text-red-600 focus:text-red-600"
                                    @click="deleteOpportunity(dealership.id, opportunity)"
                                >
                                    Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </CardHeader>
                <CardContent class="space-y-2 text-sm text-slate-600 dark:text-slate-400">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Estimated Value</span>
                        <span class="font-semibold text-slate-900 dark:text-slate-100">
                            {{ formatCurrency(opportunity.estimatedValue) }}
                        </span>
                    </div>
                    <div v-if="opportunity.probability !== null" class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Probability</span>
                        <span>{{ opportunity.probability }}%</span>
                    </div>
                    <div v-if="opportunity.expectedCloseDate" class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Close Date</span>
                        <span>{{ opportunity.expectedCloseDate }}</span>
                    </div>
                    <div v-if="opportunity.nextAction" class="pt-1 text-xs text-slate-500">
                        <span class="font-medium">Next: </span>{{ opportunity.nextAction }}
                    </div>
                    <button
                        type="button"
                        class="mt-2 flex w-full items-center justify-between rounded-md border border-dashed border-slate-200 px-3 py-1.5 text-xs text-slate-500 transition hover:border-orange-300 hover:text-orange-600 dark:border-slate-700"
                        @click="openActivity(opportunity)"
                    >
                        <span>Activity</span>
                        <span
                            v-if="opportunity.activities.length > 0"
                            class="rounded-full bg-slate-100 px-2 py-0.5 font-medium dark:bg-slate-800"
                        >
                            {{ opportunity.activities.length }}
                        </span>
                    </button>
                </CardContent>
            </Card>
            <p v-if="dealership.opportunities.length === 0" class="text-xs text-muted-foreground">
                No opportunities yet.
            </p>
        </div>

        <!-- Activity Dialog -->
        <Dialog v-model:open="isActivityOpen">
            <DialogContent class="sm:max-w-lg max-h-[85vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ activityOpportunity?.name }}</DialogTitle>
                    <DialogDescription>Activity timeline for this opportunity.</DialogDescription>
                </DialogHeader>
                <OpportunityActivityFeed
                    v-if="activityOpportunity"
                    :dealership="dealership"
                    :opportunity="activityOpportunity"
                />
            </DialogContent>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:open="isEditOpen">
            <DialogContent class="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>Edit Opportunity</DialogTitle>
                    <DialogDescription>Update opportunity details.</DialogDescription>
                </DialogHeader>
                <Form
                    v-if="editingOpportunity"
                    :action="`/dealerships/${dealership.id}/opportunities/${editingOpportunity.id}`"
                    method="put"
                    class="grid grid-cols-2 gap-4"
                    preserve-scroll
                    :on-success="() => (isEditOpen = false)"
                    v-slot="{ errors, processing }"
                >
                    <Field class="col-span-2">
                        <FieldLabel for="opp_edit_name">Name</FieldLabel>
                        <Input
                            id="opp_edit_name"
                            name="name"
                            :default-value="editingOpportunity.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </Field>
                    <Field>
                        <FieldLabel for="opp_edit_stage">Stage</FieldLabel>
                        <select
                            id="opp_edit_stage"
                            name="stage"
                            required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <option
                                v-for="stage in stages"
                                :key="stage.value"
                                :value="stage.value"
                                :selected="editingOpportunity.stage === stage.value"
                            >
                                {{ stage.label }}
                            </option>
                        </select>
                        <InputError :message="errors.stage" />
                    </Field>
                    <Field>
                        <FieldLabel for="opp_edit_estimated_value">Estimated Value ($)</FieldLabel>
                        <Input
                            id="opp_edit_estimated_value"
                            name="estimated_value"
                            type="number"
                            min="0"
                            step="0.01"
                            :default-value="editingOpportunity.estimatedValue.toString()"
                        />
                        <InputError :message="errors.estimated_value" />
                    </Field>
                    <Field>
                        <FieldLabel for="opp_edit_probability">Probability (%)</FieldLabel>
                        <Input
                            id="opp_edit_probability"
                            name="probability"
                            type="number"
                            min="0"
                            max="100"
                            :default-value="editingOpportunity.probability?.toString() ?? ''"
                        />
                    </Field>
                    <Field>
                        <FieldLabel for="opp_edit_close_date">Expected Close Date</FieldLabel>
                        <Input
                            id="opp_edit_close_date"
                            name="expected_close_date"
                            type="date"
                            :default-value="editingOpportunity.expectedCloseDate ?? ''"
                        />
                    </Field>
                    <Field class="col-span-2">
                        <FieldLabel for="opp_edit_next_action">Next Action</FieldLabel>
                        <Input
                            id="opp_edit_next_action"
                            name="next_action"
                            :default-value="editingOpportunity.nextAction ?? ''"
                        />
                    </Field>
                    <DialogFooter class="col-span-2">
                        <Button :disabled="processing">Save</Button>
                    </DialogFooter>
                </Form>
            </DialogContent>
        </Dialog>
    </div>
</template>
