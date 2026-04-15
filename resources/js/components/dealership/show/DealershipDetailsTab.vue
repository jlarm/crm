<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import type { Dealership, User } from '@/pages/Dealership/types';
import { Form } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    dealership: Dealership;
    allUsers: User[];
}>();

const consultantSearch = ref('');
const selectedConsultantIds = ref<number[]>([]);
const initialConsultantIds = ref<number[]>([]);
const lastDealershipId = ref<number | null>(null);

const selectedConsultants = computed(() =>
    props.allUsers.filter((user) => selectedConsultantIds.value.includes(user.id)),
);

const filteredConsultants = computed(() => {
    const search = consultantSearch.value.trim().toLowerCase();
    const available = props.allUsers.filter(
        (user) => !selectedConsultantIds.value.includes(user.id),
    );

    if (!search) {
        return available;
    }

    return available.filter((user) => user.name.toLowerCase().includes(search));
});

function syncConsultantsFromDealership(): void {
    const ids = (props.dealership.users ?? []).map((u) => u.id);
    selectedConsultantIds.value = ids;
    initialConsultantIds.value = ids;
}

watch(
    () => props.dealership.id,
    (id) => {
        if (!id || lastDealershipId.value === id) {
            return;
        }
        lastDealershipId.value = id;
        syncConsultantsFromDealership();
    },
    { immediate: true },
);

const consultantsDirty = computed(() => {
    const current = [...selectedConsultantIds.value].sort();
    const initial = [...initialConsultantIds.value].sort();
    return (
        current.length !== initial.length ||
        current.some((id, i) => id !== initial[i])
    );
});

watch(
    () => (props.dealership.users ?? []).map((u) => u.id).join(','),
    () => {
        if (!consultantsDirty.value) {
            syncConsultantsFromDealership();
        }
    },
);

function toggleConsultant(userId: number, add: boolean): void {
    if (add) {
        if (!selectedConsultantIds.value.includes(userId)) {
            selectedConsultantIds.value = [...selectedConsultantIds.value, userId];
        }
    } else {
        selectedConsultantIds.value = selectedConsultantIds.value.filter(
            (id) => id !== userId,
        );
    }
}
</script>

<template>
    <div class="mx-auto mt-5 w-full">
        <Form
            :action="`/dealerships/${dealership.id}`"
            method="put"
            class="grid grid-cols-1 gap-5 md:grid-cols-3"
            set-defaults-on-success
            v-slot="{ errors, processing, isDirty }"
        >
            <div class="col-span-2 space-y-5">
                <Card>
                    <div class="space-y-5 px-5">
                        <FieldGroup>
                            <div class="grid grid-cols-6 gap-5">
                                <Field class="col-span-full">
                                    <FieldLabel for="name">Name</FieldLabel>
                                    <Input
                                        id="name"
                                        name="name"
                                        :default-value="dealership.name"
                                        required
                                        placeholder="Name"
                                    />
                                    <InputError :message="errors.name" />
                                </Field>

                                <Field class="col-span-full">
                                    <FieldLabel for="address">Address</FieldLabel>
                                    <Input
                                        id="address"
                                        name="address"
                                        :default-value="dealership.address"
                                        placeholder="Address"
                                    />
                                </Field>

                                <Field class="col-span-2">
                                    <FieldLabel for="city">City</FieldLabel>
                                    <Input
                                        id="city"
                                        name="city"
                                        :default-value="dealership.city"
                                        required
                                        placeholder="City"
                                    />
                                    <InputError :message="errors.city" />
                                </Field>

                                <Field class="col-span-2">
                                    <FieldLabel for="state">State</FieldLabel>
                                    <Input
                                        id="state"
                                        name="state"
                                        :default-value="dealership.state"
                                        required
                                        placeholder="ST"
                                        maxlength="2"
                                    />
                                    <InputError :message="errors.state" />
                                </Field>

                                <Field class="col-span-2">
                                    <FieldLabel for="zip_code">Zip Code</FieldLabel>
                                    <Input
                                        id="zip_code"
                                        name="zip_code"
                                        :default-value="dealership.zipCode"
                                        placeholder="Zip Code"
                                    />
                                </Field>

                                <Field class="col-span-full">
                                    <FieldLabel for="phone">Phone</FieldLabel>
                                    <Input
                                        id="phone"
                                        name="phone"
                                        :default-value="dealership.phone"
                                        placeholder="999-999-9999"
                                    />
                                </Field>

                                <Separator class="col-span-full my-3" />

                                <Field class="col-span-3">
                                    <FieldLabel for="current_solution_name">Current Solution Name</FieldLabel>
                                    <Input
                                        id="current_solution_name"
                                        name="current_solution_name"
                                        :default-value="dealership.currentSolutionName"
                                        placeholder="Solution Name"
                                    />
                                </Field>

                                <Field class="col-span-3">
                                    <FieldLabel for="current_solution_use">Current Solution Use</FieldLabel>
                                    <Input
                                        id="current_solution_use"
                                        name="current_solution_use"
                                        :default-value="dealership.currentSolutionUse"
                                        placeholder="Solution Use"
                                    />
                                </Field>

                                <Field class="col-span-full">
                                    <FieldLabel for="notes">Notes</FieldLabel>
                                    <Textarea
                                        id="notes"
                                        name="notes"
                                        :default-value="dealership.notes"
                                        placeholder="Notes"
                                    />
                                </Field>
                            </div>
                        </FieldGroup>
                    </div>
                </Card>
            </div>

            <div class="self-start space-y-5">
                <Card>
                    <CardHeader>
                        <CardTitle>Consultants</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <input
                                v-for="id in selectedConsultantIds"
                                :key="id"
                                type="hidden"
                                name="user_ids[]"
                                :value="id"
                            />
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-between">
                                        <span>Select consultants</span>
                                        <span class="text-xs text-slate-500">
                                            {{ selectedConsultantIds.length }}
                                        </span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent class="w-72">
                                    <div class="p-2">
                                        <Input
                                            v-model="consultantSearch"
                                            placeholder="Search consultants..."
                                        />
                                    </div>
                                    <div class="max-h-60 overflow-auto py-1">
                                        <DropdownMenuItem
                                            v-for="user in filteredConsultants"
                                            :key="user.id"
                                            @select.prevent="toggleConsultant(user.id, true)"
                                            @click="toggleConsultant(user.id, true)"
                                        >
                                            {{ user.name }}
                                        </DropdownMenuItem>
                                        <div
                                            v-if="filteredConsultants.length === 0"
                                            class="px-2 py-2 text-xs text-slate-500"
                                        >
                                            No consultants match that search.
                                        </div>
                                    </div>
                                </DropdownMenuContent>
                            </DropdownMenu>

                            <div class="text-xs text-slate-500">Selected consultants</div>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="user in selectedConsultants"
                                    :key="user.id"
                                    class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700"
                                >
                                    {{ user.name }}
                                    <button
                                        type="button"
                                        class="text-slate-500 hover:text-slate-700"
                                        aria-label="Remove consultant"
                                        @click="toggleConsultant(user.id, false)"
                                    >
                                        ×
                                    </button>
                                </span>
                                <span v-if="selectedConsultants.length === 0" class="text-xs text-slate-500">
                                    None selected.
                                </span>
                            </div>

                            <Separator class="my-2" />

                            <div class="grid grid-cols-2 gap-4">
                                <Field class="col-span-2">
                                    <FieldLabel for="status">Status</FieldLabel>
                                    <Select name="status" :default-value="dealership.status">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="dealership.status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="inactive">Inactive</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field class="col-span-2">
                                    <FieldLabel for="rating">Rating</FieldLabel>
                                    <Select name="rating" :default-value="dealership.rating">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="dealership.rating" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="hot">Hot</SelectItem>
                                            <SelectItem value="warm">Warm</SelectItem>
                                            <SelectItem value="cold">Cold</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="col-span-full flex justify-end">
                <Button
                    type="submit"
                    :disabled="processing || (!isDirty && !consultantsDirty)"
                >
                    <Save />
                    Save Changes
                </Button>
            </div>
        </Form>
    </div>
</template>
