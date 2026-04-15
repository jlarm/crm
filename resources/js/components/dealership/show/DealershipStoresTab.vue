<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
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
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import type { Dealership, Store } from '@/pages/Dealership/types';
import { Form, router } from '@inertiajs/vue3';
import { MoreVertical } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    dealership: Dealership;
}>();

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const editingStore = ref<Store | null>(null);

function openEdit(store: Store): void {
    editingStore.value = store;
    isEditOpen.value = true;
}

function deleteStore(dealershipId: number, store: Store): void {
    if (!window.confirm('Delete this store?')) {
        return;
    }
    router.delete(`/dealerships/${dealershipId}/stores/${store.id}`);
}
</script>

<template>
    <div class="mx-auto mt-6 w-full">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Stores</h2>
            <Dialog v-model:open="isCreateOpen">
                <DialogTrigger as-child>
                    <Button>Create Store</Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Create Store</DialogTitle>
                        <DialogDescription>Add a new store for this dealership.</DialogDescription>
                    </DialogHeader>
                    <Form
                        :action="`/dealerships/${dealership.id}/stores`"
                        method="post"
                        class="grid grid-cols-2 gap-4"
                        reset-on-success
                        :on-success="() => (isCreateOpen = false)"
                        v-slot="{ errors, processing }"
                    >
                        <Field class="col-span-2">
                            <FieldLabel for="store_name">Name</FieldLabel>
                            <Input id="store_name" name="name" required />
                            <InputError :message="errors.name" />
                        </Field>
                        <Field class="col-span-2">
                            <FieldLabel for="store_address">Address</FieldLabel>
                            <Input id="store_address" name="address" />
                        </Field>
                        <Field>
                            <FieldLabel for="store_city">City</FieldLabel>
                            <Input id="store_city" name="city" />
                        </Field>
                        <Field>
                            <FieldLabel for="store_state">State</FieldLabel>
                            <Input id="store_state" name="state" />
                        </Field>
                        <Field>
                            <FieldLabel for="store_zip">Zip</FieldLabel>
                            <Input id="store_zip" name="zip_code" />
                        </Field>
                        <Field>
                            <FieldLabel for="store_phone">Phone</FieldLabel>
                            <Input id="store_phone" name="phone" />
                        </Field>
                        <Field class="col-span-2">
                            <FieldLabel for="store_solution_name">Current Solution Name</FieldLabel>
                            <Input id="store_solution_name" name="current_solution_name" />
                        </Field>
                        <Field class="col-span-2">
                            <FieldLabel for="store_solution_use">Current Solution Use</FieldLabel>
                            <Input id="store_solution_use" name="current_solution_use" />
                        </Field>
                        <DialogFooter class="col-span-2">
                            <Button :disabled="processing">Create</Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="store in dealership.stores"
                :key="store.id"
                class="rounded-2xl border border-slate-200 shadow-sm transition hover:shadow-md dark:border-slate-800"
            >
                <CardHeader>
                    <div class="space-y-1">
                        <div class="flex w-full items-center justify-between gap-4">
                            <CardTitle class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ store.name }}
                            </CardTitle>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-slate-500 hover:text-slate-700"
                                        aria-label="Store actions"
                                    >
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="openEdit(store)">Edit</DropdownMenuItem>
                                    <DropdownMenuItem
                                        class="text-red-600 focus:text-red-600"
                                        @click="deleteStore(dealership.id, store)"
                                    >
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <p class="text-xs text-slate-500">{{ store.address || '—' }}</p>
                        <p class="text-xs text-slate-500">
                            {{ store.city }}{{ store.state ? `, ${store.state}` : '' }}
                            {{ store.zipCode || '' }}
                        </p>
                        <p class="text-xs text-slate-500">{{ store.phone || '—' }}</p>
                    </div>
                </CardHeader>
            </Card>
            <p v-if="dealership.stores.length === 0" class="text-xs text-muted-foreground">
                No stores yet.
            </p>
        </div>

        <Dialog v-model:open="isEditOpen">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Edit Store</DialogTitle>
                    <DialogDescription>Update store details.</DialogDescription>
                </DialogHeader>
                <Form
                    v-if="editingStore"
                    :action="`/dealerships/${dealership.id}/stores/${editingStore.id}`"
                    method="put"
                    class="grid grid-cols-2 gap-4"
                    v-slot="{ errors, processing }"
                >
                    <Field class="col-span-2">
                        <FieldLabel for="store_edit_name">Name</FieldLabel>
                        <Input
                            id="store_edit_name"
                            name="name"
                            :default-value="editingStore.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </Field>
                    <Field class="col-span-2">
                        <FieldLabel for="store_edit_address">Address</FieldLabel>
                        <Input
                            id="store_edit_address"
                            name="address"
                            :default-value="editingStore.address"
                        />
                    </Field>
                    <Field>
                        <FieldLabel for="store_edit_city">City</FieldLabel>
                        <Input
                            id="store_edit_city"
                            name="city"
                            :default-value="editingStore.city"
                        />
                    </Field>
                    <Field>
                        <FieldLabel for="store_edit_state">State</FieldLabel>
                        <Input
                            id="store_edit_state"
                            name="state"
                            :default-value="editingStore.state"
                        />
                    </Field>
                    <Field>
                        <FieldLabel for="store_edit_zip">Zip</FieldLabel>
                        <Input
                            id="store_edit_zip"
                            name="zip_code"
                            :default-value="editingStore.zipCode"
                        />
                    </Field>
                    <Field>
                        <FieldLabel for="store_edit_phone">Phone</FieldLabel>
                        <Input
                            id="store_edit_phone"
                            name="phone"
                            :default-value="editingStore.phone"
                        />
                    </Field>
                    <Field class="col-span-2">
                        <FieldLabel for="store_edit_solution_name">Current Solution Name</FieldLabel>
                        <Input
                            id="store_edit_solution_name"
                            name="current_solution_name"
                            :default-value="editingStore.currentSolutionName"
                        />
                    </Field>
                    <Field class="col-span-2">
                        <FieldLabel for="store_edit_solution_use">Current Solution Use</FieldLabel>
                        <Input
                            id="store_edit_solution_use"
                            name="current_solution_use"
                            :default-value="editingStore.currentSolutionUse"
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
