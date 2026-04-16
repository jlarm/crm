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
import PhoneInput from '@/components/ui/phone-input/PhoneInput.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import type { User } from '@/pages/Dealership/types';
import { Form, Head } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    allUsers: User[];
}

const props = defineProps<Props>();

const consultantSearch = ref('');
const selectedConsultantIds = ref<number[]>([]);

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
    <Head title="New Dealership" />

    <div class="px-8 py-3">
        <div class="flex shrink-0 items-center gap-4">
            <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">
                New Dealership
            </h1>
        </div>

        <div class="mx-auto mt-5 w-full">
            <Form
                action="/dealerships"
                method="post"
                class="grid grid-cols-1 gap-5 md:grid-cols-3"
                v-slot="{ errors, processing }"
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
                                            required
                                            placeholder="Name"
                                            autofocus
                                        />
                                        <InputError :message="errors.name" />
                                    </Field>

                                    <Field class="col-span-full">
                                        <FieldLabel for="address">Address</FieldLabel>
                                        <Input
                                            id="address"
                                            name="address"
                                            placeholder="Address"
                                        />
                                    </Field>

                                    <Field class="col-span-2">
                                        <FieldLabel for="city">City</FieldLabel>
                                        <Input
                                            id="city"
                                            name="city"
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
                                            placeholder="Zip Code"
                                        />
                                    </Field>

                                    <Field class="col-span-full">
                                        <FieldLabel for="phone">Phone</FieldLabel>
                                        <PhoneInput id="phone" name="phone" />
                                    </Field>

                                    <Separator class="col-span-full my-3" />

                                    <Field class="col-span-3">
                                        <FieldLabel for="current_solution_name">Current Solution Name</FieldLabel>
                                        <Input
                                            id="current_solution_name"
                                            name="current_solution_name"
                                            placeholder="Solution Name"
                                        />
                                    </Field>

                                    <Field class="col-span-3">
                                        <FieldLabel for="current_solution_use">Current Solution Use</FieldLabel>
                                        <Input
                                            id="current_solution_use"
                                            name="current_solution_use"
                                            placeholder="Solution Use"
                                        />
                                    </Field>

                                    <Field class="col-span-full">
                                        <FieldLabel for="notes">Notes</FieldLabel>
                                        <Textarea
                                            id="notes"
                                            name="notes"
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
                                        <FieldLabel for="type">Type</FieldLabel>
                                        <Select name="type">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="Automotive">Automotive</SelectItem>
                                                <SelectItem value="RV">RV</SelectItem>
                                                <SelectItem value="Motorsports">Motorsports</SelectItem>
                                                <SelectItem value="Maritime">Maritime</SelectItem>
                                                <SelectItem value="Association">Association</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="errors.type" />
                                    </Field>

                                    <Field class="col-span-2">
                                        <FieldLabel for="status">Status</FieldLabel>
                                        <Select name="status" default-value="active">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="active">Active</SelectItem>
                                                <SelectItem value="inactive">Inactive</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="errors.status" />
                                    </Field>

                                    <Field class="col-span-2">
                                        <FieldLabel for="rating">Rating</FieldLabel>
                                        <Select name="rating" default-value="warm">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select rating" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="hot">Hot</SelectItem>
                                                <SelectItem value="warm">Warm</SelectItem>
                                                <SelectItem value="cold">Cold</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="errors.rating" />
                                    </Field>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="col-span-full flex justify-end">
                    <Button type="submit" :disabled="processing">
                        <Save />
                        Create Dealership
                    </Button>
                </div>
            </Form>
        </div>
    </div>
</template>
