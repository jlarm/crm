<script setup lang="ts">
import ComposeEmailDialog from '@/components/dealership/show/ComposeEmailDialog.vue';
import { Button } from '@/components/ui/button';
import type { Dealership, DealershipEmail, EmailStatusTone, EmailThread } from '@/pages/Dealership/types';
import { read } from '@/routes/dealerships/emails';
import { router, usePage, usePoll } from '@inertiajs/vue3';
import { ChevronDown, CornerUpLeft, Inbox, Mail, Paperclip, Plus } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps<{
    dealership: Dealership;
    /** Deferred: undefined while loading. */
    threads?: EmailThread[];
}>();

/**
 * Picks up delivery updates from Mailgun and contacts' replies while the tab is open.
 * Only the email list is reloaded; polling stops when the tab closes, slows down in a
 * background browser tab, and waits for each request to finish before the next.
 */
usePoll(10_000, { only: ['emailThreads'], showProgress: false }, { mode: 'rest' });

const isComposeOpen = ref(false);
const replyingTo = ref<DealershipEmail | null>(null);
const replyContactId = ref<number | null>(null);
const expandedThreadId = ref<number | null>(null);

/** A thread named in ?thread= (e.g. from the dashboard's Replies to answer) opens once the emails load. */
const page = usePage();
let threadToOpen: number | null = Number(new URL(page.url, 'http://localhost').searchParams.get('thread')) || null;

const toneClasses: Record<EmailStatusTone, string> = {
    success: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300',
    neutral: 'bg-slate-50 text-slate-600 ring-slate-500/20 dark:bg-slate-800/60 dark:text-slate-300',
    warning: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-300',
    danger: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-950/40 dark:text-red-300',
};

const eventLabels: Record<string, string> = {
    accepted: 'Accepted by Mailgun',
    delivered: "Accepted by the recipient's server",
    opened: 'Opened',
    clicked: 'Link clicked',
    failed: 'Delivery failed',
    complained: 'Marked as spam',
};

const hasThreads = computed(() => (props.threads?.length ?? 0) > 0);

/** The latest message the rep sent in a thread, which carries the delivery status worth showing. */
function latestOutbound(thread: EmailThread): DealershipEmail | undefined {
    return [...thread].reverse().find((email) => email.direction === 'outbound');
}

function replyCount(thread: EmailThread): number {
    return thread.filter((email) => email.direction === 'inbound').length;
}

function threadContactName(thread: EmailThread): string {
    return thread.find((email) => email.contact)?.contact?.name ?? thread[0].to;
}

function unreadCount(thread: EmailThread): number {
    return thread.filter((email) => email.direction === 'inbound' && email.readAt === null).length;
}

/** The contact wrote last, so the ball is in the rep's court. */
function awaitsReply(thread: EmailThread): boolean {
    return thread[thread.length - 1].direction === 'inbound';
}

function markRead(thread: EmailThread): void {
    if (unreadCount(thread) === 0) {
        return;
    }

    router.patch(read([props.dealership.id, thread[0].id]).url, {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['emailThreads', 'unreadReplyCount'],
        showProgress: false,
    });
}

function toggle(thread: EmailThread): void {
    const opening = expandedThreadId.value !== thread[0].id;
    expandedThreadId.value = opening ? thread[0].id : null;

    if (opening) {
        markRead(thread);
    }
}

function compose(): void {
    replyingTo.value = null;
    replyContactId.value = null;
    isComposeOpen.value = true;
}

function reply(thread: EmailThread): void {
    markRead(thread);
    replyingTo.value = thread[thread.length - 1];
    replyContactId.value = thread.find((email) => email.contact)?.contact?.id ?? null;
    isComposeOpen.value = true;
}

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function formatSize(bytes: number): string {
    return bytes < 1024 * 1024 ? `${Math.max(1, Math.round(bytes / 1024))} KB` : `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

watch(
    () => props.threads,
    (threads) => {
        const thread = threadToOpen === null ? undefined : threads?.find((candidate) => candidate[0].id === threadToOpen);

        if (!thread) {
            return;
        }

        threadToOpen = null;
        expandedThreadId.value = thread[0].id;
        markRead(thread);
        nextTick(() => document.getElementById(`email-thread-${thread[0].id}`)?.scrollIntoView({ block: 'start', behavior: 'smooth' }));
    },
    { immediate: true },
);
</script>

<template>
    <div class="mt-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Emails</h2>
                <p class="text-xs text-muted-foreground">
                    “Delivered” means the contact's mail server accepted the email. Whether it lands in their inbox or spam folder can't be tracked.
                </p>
            </div>
            <Button @click="compose">
                <Plus class="h-4 w-4" />
                New email
            </Button>
        </div>

        <ComposeEmailDialog
            v-model:open="isComposeOpen"
            :dealership="dealership"
            :replying-to="replyingTo"
            :reply-contact-id="replyContactId"
        />

        <div v-if="threads === undefined" class="space-y-3">
            <div v-for="i in 3" :key="i" class="h-16 animate-pulse rounded-lg bg-slate-100 dark:bg-slate-800/60" />
        </div>

        <div
            v-else-if="!hasThreads"
            class="flex flex-col items-center rounded-lg border border-dashed border-slate-200 px-6 py-12 text-center dark:border-slate-700"
        >
            <Inbox class="mb-2 h-8 w-8 text-slate-300 dark:text-slate-600" />
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">No emails yet</p>
            <p class="mt-1 text-xs text-muted-foreground">Emails you send to this dealership's contacts, and their replies, show up here.</p>
        </div>

        <ul v-else class="divide-y divide-slate-200 rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
            <li v-for="thread in threads" :id="`email-thread-${thread[0].id}`" :key="thread[0].id" class="scroll-mt-20">
                <button
                    type="button"
                    class="flex w-full items-center gap-4 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-900/50"
                    :aria-expanded="expandedThreadId === thread[0].id"
                    @click="toggle(thread)"
                >
                    <span class="relative shrink-0">
                        <Mail class="h-4 w-4" :class="unreadCount(thread) ? 'text-orange-500' : 'text-slate-400'" />
                        <span
                            v-if="unreadCount(thread)"
                            class="absolute -top-1 -right-1 size-2 rounded-full bg-orange-500 ring-2 ring-white dark:ring-slate-950"
                            aria-hidden="true"
                        />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-2 text-sm text-slate-900 dark:text-slate-100" :class="unreadCount(thread) ? 'font-semibold' : 'font-medium'">
                            <span class="truncate">{{ thread[0].subject }}</span>
                            <span
                                v-if="unreadCount(thread)"
                                class="shrink-0 rounded-full bg-orange-500 px-2 py-0.5 text-[11px] font-semibold text-white"
                            >
                                {{ unreadCount(thread) === 1 ? 'New reply' : `${unreadCount(thread)} new replies` }}
                            </span>
                            <span
                                v-else-if="awaitsReply(thread)"
                                class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 ring-1 ring-amber-600/20 ring-inset dark:bg-amber-950/40 dark:text-amber-300"
                            >
                                Awaiting your reply
                            </span>
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ threadContactName(thread) }}
                            · {{ formatDateTime(thread[thread.length - 1].createdAt) }}
                            <template v-if="replyCount(thread)">
                                · {{ replyCount(thread) }} {{ replyCount(thread) === 1 ? 'reply' : 'replies' }}
                            </template>
                        </p>
                    </div>
                    <div v-if="latestOutbound(thread)" class="flex shrink-0 flex-col items-end gap-0.5">
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                            :class="toneClasses[latestOutbound(thread)!.statusTone]"
                            :title="latestOutbound(thread)!.statusDetail ?? undefined"
                        >
                            {{ latestOutbound(thread)!.statusLabel }}
                        </span>
                        <span v-if="latestOutbound(thread)!.statusReason" class="max-w-56 truncate text-[11px] text-muted-foreground">
                            {{ latestOutbound(thread)!.statusReason }}
                        </span>
                    </div>
                    <ChevronDown
                        class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
                        :class="{ 'rotate-180': expandedThreadId === thread[0].id }"
                    />
                </button>

                <div v-if="expandedThreadId === thread[0].id" class="space-y-3 bg-slate-50/60 px-4 pb-4 pt-1 dark:bg-slate-900/30">
                    <article
                        v-for="email in thread"
                        :key="email.id"
                        class="rounded-lg border bg-white p-4 dark:bg-slate-950"
                        :class="email.direction === 'inbound' ? 'border-sky-200 dark:border-sky-900' : 'border-slate-200 dark:border-slate-800'"
                    >
                        <header class="mb-3 flex flex-wrap items-start justify-between gap-2">
                            <div class="text-xs">
                                <p class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ email.from.name ?? email.from.email }}
                                    <span class="font-normal text-muted-foreground">&lt;{{ email.from.email }}&gt;</span>
                                </p>
                                <p class="text-muted-foreground">To {{ email.to }} · {{ formatDateTime(email.createdAt) }}</p>
                            </div>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                :class="toneClasses[email.statusTone]"
                            >
                                {{ email.statusLabel }}
                            </span>
                        </header>

                        <!-- bodyHtml is sanitized on the server and only ever set for emails reps wrote in the CRM. -->
                        <div
                            v-if="email.bodyHtml"
                            class="prose prose-sm max-w-none break-words text-slate-700 dark:prose-invert dark:text-slate-200 prose-p:my-2 prose-ul:my-2 prose-ol:my-2 prose-li:my-0 prose-a:text-orange-700 dark:prose-a:text-orange-400"
                            v-html="email.bodyHtml"
                        />
                        <p v-else class="whitespace-pre-wrap break-words text-sm text-slate-700 dark:text-slate-200">{{ email.body }}</p>

                        <ul v-if="email.attachments.length" class="mt-3 flex flex-wrap gap-2">
                            <li v-for="attachment in email.attachments" :key="attachment.id">
                                <a
                                    :href="attachment.url"
                                    class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                >
                                    <Paperclip class="h-3 w-3" />
                                    {{ attachment.filename }}
                                    <span class="text-muted-foreground">{{ formatSize(attachment.size) }}</span>
                                </a>
                            </li>
                        </ul>

                        <div
                            v-if="email.direction === 'outbound' && (email.statusReason || email.events.length)"
                            class="mt-3 border-t border-slate-100 pt-3 text-xs dark:border-slate-800"
                        >
                            <p v-if="email.statusReason" class="mb-1.5 font-medium" :class="email.statusTone === 'danger' ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300'">
                                {{ email.statusReason }}
                            </p>
                            <p v-if="email.statusDetail" class="mb-2 font-mono text-[11px] text-muted-foreground">{{ email.statusDetail }}</p>
                            <ol class="space-y-0.5 text-muted-foreground">
                                <li v-for="event in email.events" :key="event.id">
                                    {{ formatDateTime(event.occurredAt) }} ·
                                    {{ event.event === 'failed' && event.severity === 'temporary' ? 'Delayed, retrying' : (eventLabels[event.event] ?? event.event) }}
                                </li>
                            </ol>
                        </div>
                    </article>

                    <div class="flex justify-end">
                        <Button variant="outline" size="sm" @click="reply(thread)">
                            <CornerUpLeft class="h-3.5 w-3.5" />
                            Reply
                        </Button>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>
