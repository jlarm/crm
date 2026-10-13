<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Dealership, DealershipEmail } from '@/pages/Dealership/types';
import { store } from '@/routes/dealerships/emails';
import { useForm } from '@inertiajs/vue3';
import { Paperclip, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    dealership: Dealership;
    /** The message being answered, when replying within a thread. */
    replyingTo?: DealershipEmail | null;
    /** The contact the thread is with, preselected when replying. */
    replyContactId?: number | null;
}>();

const open = defineModel<boolean>('open', { default: false });

const MAX_TOTAL_BYTES = 10 * 1024 * 1024;

const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    contact_id: '' as string,
    subject: '',
    body: '',
    parent_id: null as number | null,
    attachments: [] as File[],
});

const emailableContacts = computed(() => props.dealership.contacts.filter((contact) => contact.email));

const totalBytes = computed(() => form.attachments.reduce((sum, file) => sum + file.size, 0));
const isOverLimit = computed(() => totalBytes.value > MAX_TOTAL_BYTES);

const attachmentError = computed(() => {
    const errors = form.errors as Record<string, string | undefined>;
    const key = Object.keys(errors).find((name) => name.startsWith('attachments'));

    return key ? errors[key] : undefined;
});

watch(open, (isOpen) => {
    if (!isOpen) {
        return;
    }

    form.reset();
    form.clearErrors();

    if (props.replyingTo) {
        const subject = props.replyingTo.subject;
        form.subject = /^re:/i.test(subject) ? subject : `Re: ${subject}`;
        form.parent_id = props.replyingTo.id;
    }

    const preselected = props.replyContactId ?? (emailableContacts.value.length === 1 ? emailableContacts.value[0].id : null);
    form.contact_id = preselected ? String(preselected) : '';
});

function addFiles(event: Event): void {
    const input = event.target as HTMLInputElement;
    form.attachments = [...form.attachments, ...Array.from(input.files ?? [])];
    input.value = '';
}

function removeFile(index: number): void {
    form.attachments = form.attachments.filter((_, i) => i !== index);
}

function formatSize(bytes: number): string {
    if (bytes < 1024 * 1024) {
        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }

    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

function submit(): void {
    form
        .transform((data) => ({
            ...data,
            contact_id: data.contact_id === '' ? null : Number(data.contact_id),
        }))
        .submit(store(props.dealership.id), {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
            },
        });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ replyingTo ? 'Reply' : 'New email' }}</DialogTitle>
                <DialogDescription>
                    Sent from your email address, with a copy to you. Your signature is added automatically.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <Field>
                    <FieldLabel for="email_contact">To</FieldLabel>
                    <Select v-model="form.contact_id">
                        <SelectTrigger id="email_contact" class="w-full">
                            <SelectValue placeholder="Choose a contact" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="contact in dealership.contacts"
                                :key="contact.id"
                                :value="String(contact.id)"
                                :disabled="!contact.email"
                            >
                                {{ contact.name }}
                                <span class="text-muted-foreground">{{ contact.email ? `<${contact.email}>` : '(no email)' }}</span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="emailableContacts.length === 0" class="text-xs text-muted-foreground">
                        None of this dealership's contacts have an email address yet. Add one on the Contacts tab.
                    </p>
                    <InputError :message="form.errors.contact_id" />
                </Field>

                <Field>
                    <FieldLabel for="email_subject">Subject</FieldLabel>
                    <Input id="email_subject" v-model="form.subject" maxlength="255" required />
                    <InputError :message="form.errors.subject" />
                </Field>

                <Field>
                    <FieldLabel for="email_body">Message</FieldLabel>
                    <RichTextEditor id="email_body" v-model="form.body" :invalid="!!form.errors.body" />
                    <InputError :message="form.errors.body" />
                </Field>

                <div class="space-y-2">
                    <input
                        ref="fileInput"
                        type="file"
                        multiple
                        class="hidden"
                        accept=".pdf,.png,.jpg,.jpeg,.gif,.doc,.docx,.xls,.xlsx,.csv,.txt"
                        @change="addFiles"
                    />
                    <Button type="button" variant="outline" size="sm" @click="fileInput?.click()">
                        <Paperclip class="h-3.5 w-3.5" />
                        Attach files
                    </Button>
                    <ul v-if="form.attachments.length" class="space-y-1">
                        <li
                            v-for="(file, index) in form.attachments"
                            :key="`${file.name}-${index}`"
                            class="flex items-center justify-between rounded-md border border-slate-200 px-2.5 py-1.5 text-xs dark:border-slate-700"
                        >
                            <span class="truncate">{{ file.name }}</span>
                            <span class="ml-3 flex shrink-0 items-center gap-2 text-muted-foreground">
                                {{ formatSize(file.size) }}
                                <button type="button" class="hover:text-foreground" :aria-label="`Remove ${file.name}`" @click="removeFile(index)">
                                    <X class="h-3.5 w-3.5" />
                                </button>
                            </span>
                        </li>
                    </ul>
                    <p v-if="form.attachments.length" class="text-xs" :class="isOverLimit ? 'text-red-600 dark:text-red-400' : 'text-muted-foreground'">
                        {{ formatSize(totalBytes) }} of 10 MB
                    </p>
                    <InputError :message="attachmentError" />
                </div>

                <DialogFooter>
                    <Button type="submit" :disabled="form.processing || isOverLimit || emailableContacts.length === 0">
                        {{ form.processing ? 'Sending…' : 'Send' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
