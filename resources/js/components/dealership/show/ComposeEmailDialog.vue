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
import { draft, store } from '@/routes/dealerships/emails';
import { useForm, useHttp } from '@inertiajs/vue3';
import { Paperclip, Sparkles, Undo2, X } from 'lucide-vue-next';
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

const drafter = useHttp<{ parent_id: number | null; contact_id: number | null; notes: string }, { subject: string; body: string; bodyHtml: string }>({
    parent_id: null,
    contact_id: null,
    notes: '',
});
const draftError = ref<string | null>(null);
/** What the rep had written before the AI draft replaced it, so it can be restored. */
const bodyBeforeDraft = ref<string | null>(null);

const canDraft = computed(() => !drafter.processing && (props.replyingTo !== null && props.replyingTo !== undefined || drafter.notes.trim() !== ''));

function draftWithAi(): void {
    draftError.value = null;
    drafter.parent_id = form.parent_id;
    drafter.contact_id = form.contact_id === '' ? null : Number(form.contact_id);

    drafter.post(draft(props.dealership.id).url, {
        onSuccess: (response: { subject: string; body: string; bodyHtml: string }) => {
            bodyBeforeDraft.value = form.body;
            form.body = response.bodyHtml;

            if (!props.replyingTo || form.subject.trim() === '') {
                form.subject = response.subject;
            }
        },
        onHttpException: (response) => {
            draftError.value = response.status === 429
                ? 'Too many drafts in a row. Wait a minute and try again.'
                : messageFrom(response.data) ?? "Couldn't write a draft right now. Try again, or write the email yourself.";
        },
    });
}

function undoDraft(): void {
    form.body = bodyBeforeDraft.value ?? '';
    bodyBeforeDraft.value = null;
}

function messageFrom(body: string): string | null {
    try {
        const parsed: unknown = JSON.parse(body);

        if (parsed && typeof parsed === 'object' && 'message' in parsed && typeof parsed.message === 'string') {
            return parsed.message;
        }
    } catch {
        // Not JSON.
    }

    return null;
}

watch(open, (isOpen) => {
    if (!isOpen) {
        return;
    }

    drafter.reset();
    drafter.clearErrors();
    draftError.value = null;
    bodyBeforeDraft.value = null;

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

                <div class="space-y-2 rounded-lg border border-violet-200 bg-violet-50/40 p-3 dark:border-violet-900 dark:bg-violet-950/20">
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Input
                            v-model="drafter.notes"
                            maxlength="1000"
                            :placeholder="replyingTo ? 'Anything to include? (optional) e.g. offer Tuesday or Thursday for a call' : 'What should it say? e.g. intro to our cyber risk assessment, ask for 20 minutes'"
                            aria-label="Notes for the AI draft"
                            @keydown.enter.prevent="canDraft && draftWithAi()"
                        />
                        <Button type="button" variant="outline" class="shrink-0" :disabled="!canDraft" @click="draftWithAi">
                            <Sparkles class="h-3.5 w-3.5" />
                            {{ drafter.processing ? 'Writing…' : replyingTo ? 'Draft reply with AI' : 'Draft with AI' }}
                        </Button>
                    </div>
                    <p v-if="drafter.processing" class="text-xs text-muted-foreground">Reading the thread and writing a draft. This takes a few seconds.</p>
                    <p v-else-if="draftError || drafter.errors.notes" class="text-xs text-red-600 dark:text-red-400">{{ draftError ?? drafter.errors.notes }}</p>
                    <p v-else-if="bodyBeforeDraft !== null" class="flex items-center gap-2 text-xs text-muted-foreground">
                        Draft added below. Read it and edit before sending.
                        <button type="button" class="inline-flex items-center gap-1 font-medium text-violet-700 hover:underline dark:text-violet-300" @click="undoDraft">
                            <Undo2 class="h-3 w-3" />
                            Undo
                        </button>
                    </p>
                </div>

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
