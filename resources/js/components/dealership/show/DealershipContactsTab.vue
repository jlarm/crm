<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import PhoneInput from '@/components/ui/phone-input/PhoneInput.vue';
import type { Contact, Dealership } from '@/pages/Dealership/types';
import { Form, router } from '@inertiajs/vue3';
import { Linkedin, Mail, MoreVertical, Phone, Star } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    dealership: Dealership;
}>();

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const editingContact = ref<Contact | null>(null);

function openEdit(contact: Contact): void {
    editingContact.value = contact;
    isEditOpen.value = true;
}

function deleteContact(dealershipId: number, contact: Contact): void {
    if (!window.confirm('Delete this contact?')) {
        return;
    }
    router.delete(`/dealerships/${dealershipId}/contacts/${contact.id}`);
}
</script>

<template>
    <div class="mx-auto mt-6 w-full">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Contacts</h2>
            <Dialog v-model:open="isCreateOpen">
                <DialogTrigger as-child>
                    <Button>Create Contact</Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>Create Contact</DialogTitle>
                        <DialogDescription>Add a new contact for this dealership.</DialogDescription>
                    </DialogHeader>
                    <Form
                        :action="`/dealerships/${dealership.id}/contacts`"
                        method="post"
                        class="grid grid-cols-2 gap-4"
                        reset-on-success
                        :on-success="() => (isCreateOpen = false)"
                        v-slot="{ errors, processing }"
                    >
                        <Field class="col-span-2">
                            <FieldLabel for="contact_name">Name</FieldLabel>
                            <Input id="contact_name" name="name" required />
                            <InputError :message="errors.name" />
                        </Field>
                        <Field>
                            <FieldLabel for="contact_email">Email</FieldLabel>
                            <Input id="contact_email" name="email" type="email" />
                        </Field>
                        <Field>
                            <FieldLabel for="contact_phone">Phone</FieldLabel>
                            <PhoneInput id="contact_phone" name="phone" />
                        </Field>
                        <Field class="col-span-2">
                            <FieldLabel for="contact_position">Position</FieldLabel>
                            <Input id="contact_position" name="position" />
                        </Field>
                        <Field class="col-span-2">
                            <FieldLabel for="contact_linkedin">LinkedIn</FieldLabel>
                            <Input id="contact_linkedin" name="linkedin_link" />
                        </Field>
                        <Field class="col-span-2">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="primary_contact" value="1" />
                                Primary contact
                            </label>
                        </Field>
                        <DialogFooter class="col-span-2">
                            <Button :disabled="processing">Create</Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="contact in dealership.contacts"
                :key="contact.id"
                class="rounded-2xl border border-slate-200 shadow-sm transition hover:shadow-md dark:border-slate-800"
            >
                <CardHeader>
                    <div class="flex w-full items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <CardTitle class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ contact.name }}
                            </CardTitle>
                            <Star
                                v-if="contact.primaryContact"
                                class="h-3 w-3 text-amber-500"
                                fill="currentColor"
                            />
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="text-slate-500 hover:text-slate-700"
                                    aria-label="Contact actions"
                                >
                                    <MoreVertical class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="openEdit(contact)">Edit</DropdownMenuItem>
                                <DropdownMenuItem
                                    class="text-red-600 focus:text-red-600"
                                    @click="deleteContact(dealership.id, contact)"
                                >
                                    Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </CardHeader>
                <CardContent class="space-y-3 text-xs text-slate-600">
                    <span
                        v-if="contact.position"
                        class="inline-flex w-fit items-center rounded-xl bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700"
                    >
                        {{ contact.position }}
                    </span>
                    <div class="space-y-2">
                        <div v-if="contact.phone" class="flex items-center gap-3">
                            <Phone class="h-3 w-3 text-slate-500" />
                            <span>{{ contact.phone }}</span>
                        </div>
                        <div v-if="contact.email" class="flex items-center gap-3">
                            <Mail class="h-3 w-3 text-slate-500" />
                            <span>{{ contact.email }}</span>
                        </div>
                        <div v-if="contact.linkedinLink" class="flex min-w-0 items-center gap-3">
                            <Linkedin class="h-3 w-3 shrink-0 text-slate-500" />
                            <a
                                :href="contact.linkedinLink"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="truncate text-blue-600 hover:underline"
                            >{{ contact.linkedinLink }}</a>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <p v-if="dealership.contacts.length === 0" class="text-xs text-muted-foreground">
                No contacts yet.
            </p>
        </div>

        <Dialog v-model:open="isEditOpen">
            <DialogContent class="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>Edit Contact</DialogTitle>
                    <DialogDescription>Update contact details.</DialogDescription>
                </DialogHeader>
                <Form
                    v-if="editingContact"
                    :action="`/dealerships/${dealership.id}/contacts/${editingContact.id}`"
                    method="put"
                    class="grid grid-cols-2 gap-4"
                    preserve-scroll
                    :on-success="() => (isEditOpen = false)"
                    v-slot="{ errors, processing }"
                >
                    <Field class="col-span-2">
                        <FieldLabel for="contact_edit_name">Name</FieldLabel>
                        <Input
                            id="contact_edit_name"
                            name="name"
                            :default-value="editingContact.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </Field>
                    <Field>
                        <FieldLabel for="contact_edit_email">Email</FieldLabel>
                        <Input
                            id="contact_edit_email"
                            name="email"
                            type="email"
                            :default-value="editingContact.email || ''"
                        />
                    </Field>
                    <Field>
                        <FieldLabel for="contact_edit_phone">Phone</FieldLabel>
                        <PhoneInput
                            id="contact_edit_phone"
                            name="phone"
                            :default-value="editingContact.phone"
                        />
                    </Field>
                    <Field class="col-span-2">
                        <FieldLabel for="contact_edit_position">Position</FieldLabel>
                        <Input
                            id="contact_edit_position"
                            name="position"
                            :default-value="editingContact.position || ''"
                        />
                    </Field>
                    <Field class="col-span-2">
                        <FieldLabel for="contact_edit_linkedin">LinkedIn</FieldLabel>
                        <Input
                            id="contact_edit_linkedin"
                            name="linkedin_link"
                            :default-value="editingContact.linkedinLink || ''"
                        />
                    </Field>
                    <Field class="col-span-2">
                        <label class="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                name="primary_contact"
                                value="1"
                                :checked="editingContact.primaryContact"
                            />
                            Primary contact
                        </label>
                    </Field>
                    <DialogFooter class="col-span-2">
                        <Button :disabled="processing">Save</Button>
                    </DialogFooter>
                </Form>
            </DialogContent>
        </Dialog>
    </div>
</template>
