<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import DealershipWebLookup, { type DealershipLookupResult, type LikelyDuplicate } from '@/components/dealership/DealershipWebLookup.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
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
import { show as showDealership } from '@/routes/dealerships';
import { Form, Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, Save } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    allUsers: User[];
}

const props = defineProps<Props>();

const name = ref('');
const address = ref('');
const city = ref('');
const state = ref('');
const zipCode = ref('');
const phone = ref('');
const website = ref('');
/** PhoneInput only reads its default value on mount, so it is re-mounted when a lookup fills it. */
const phoneInputKey = ref(0);

const duplicate = ref<LikelyDuplicate | null>(null);

function applyLookupResult(result: DealershipLookupResult): void {
    name.value = result.name;
    address.value = result.address ?? address.value;
    city.value = result.city ?? city.value;
    state.value = result.state ?? state.value;
    zipCode.value = result.zip_code ?? zipCode.value;
    website.value = result.website ?? website.value;

    if (result.phone) {
        phone.value = result.phone;
        phoneInputKey.value++;
    }

    duplicate.value = result.duplicate;
}

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
            <Alert v-if="duplicate" class="mb-5 border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                <AlertTriangle />
                <AlertTitle>This dealership may already be in the CRM</AlertTitle>
                <AlertDescription class="text-amber-800 dark:text-amber-300">
                    <p>
                        {{ duplicate.reason }}:
                        <Link :href="showDealership(duplicate.match.id).url" class="font-semibold underline underline-offset-2">
                            {{ duplicate.match.name }}</Link
                        ><template v-if="duplicate.match.city || duplicate.match.state">
                            ({{ [duplicate.match.city, duplicate.match.state].filter(Boolean).join(', ') }})</template
                        >. You can still create it if it's a different location.
                    </p>
                </AlertDescription>
            </Alert>

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
                                            v-model="name"
                                            name="name"
                                            required
                                            placeholder="Name"
                                            autofocus
                                        />
                                        <InputError :message="errors.name" />
                                        <DealershipWebLookup
                                            :name="name"
                                            :city="city"
                                            :state="state"
                                            @pick="applyLookupResult"
                                        />
                                    </Field>

                                    <Field class="col-span-full">
                                        <FieldLabel for="address">Address</FieldLabel>
                                        <Input
                                            id="address"
                                            v-model="address"
                                            name="address"
                                            placeholder="Address"
                                        />
                                    </Field>

                                    <Field class="col-span-2">
                                        <FieldLabel for="city">City</FieldLabel>
                                        <Input
                                            id="city"
                                            v-model="city"
                                            name="city"
                                            placeholder="City"
                                        />
                                        <InputError :message="errors.city" />
                                    </Field>

                                    <Field class="col-span-2">
                                        <FieldLabel for="state">State</FieldLabel>
                                        <Input
                                            id="state"
                                            v-model="state"
                                            name="state"
                                            placeholder="ST"
                                            maxlength="2"
                                        />
                                        <InputError :message="errors.state" />
                                    </Field>

                                    <Field class="col-span-2">
                                        <FieldLabel for="zip_code">Zip Code</FieldLabel>
                                        <Input
                                            id="zip_code"
                                            v-model="zipCode"
                                            name="zip_code"
                                            placeholder="Zip Code"
                                        />
                                    </Field>

                                    <Field class="col-span-3">
                                        <FieldLabel for="phone">Phone</FieldLabel>
                                        <PhoneInput id="phone" :key="phoneInputKey" name="phone" :default-value="phone" />
                                    </Field>

                                    <Field class="col-span-3">
                                        <FieldLabel for="website">Website</FieldLabel>
                                        <Input
                                            id="website"
                                            v-model="website"
                                            name="website"
                                            placeholder="example.com"
                                        />
                                        <InputError :message="errors.website" />
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
                            <CardTitle>Assigned to</CardTitle>
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
                                            <span>Select users</span>
                                            <span class="text-xs text-slate-500">
                                                {{ selectedConsultantIds.length }}
                                            </span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent class="w-72">
                                        <div class="p-2">
                                            <Input
                                                v-model="consultantSearch"
                                                placeholder="Search users..."
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
                                                No users match that search.
                                            </div>
                                        </div>
                                    </DropdownMenuContent>
                                </DropdownMenu>

                                <div class="text-xs text-slate-500">Assigned users</div>
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
                                            aria-label="Remove user"
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
