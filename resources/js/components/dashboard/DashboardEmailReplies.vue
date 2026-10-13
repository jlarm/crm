<script setup lang="ts">
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import type { EmailRepliesAwaiting, EmailReplyAwaiting } from '@/components/dashboard/types';
import { show } from '@/routes/dealerships';
import { Link } from '@inertiajs/vue3';
import { Mail } from 'lucide-vue-next';

defineProps<{
    replies: EmailRepliesAwaiting;
}>();

function threadUrl(reply: EmailReplyAwaiting): string {
    return show(reply.dealership.id, { query: { tab: 'emails', thread: reply.threadId } }).url;
}

function receivedLabel(iso: string): string {
    const received = new Date(iso);
    const minutes = Math.round((Date.now() - received.getTime()) / 60_000);

    if (minutes < 1) {
        return 'Just now';
    }
    if (minutes < 60) {
        return `${minutes}m ago`;
    }
    if (minutes < 60 * 24) {
        return `${Math.round(minutes / 60)}h ago`;
    }
    if (minutes < 60 * 24 * 7) {
        return `${Math.round(minutes / 60 / 24)}d ago`;
    }

    return received.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}
</script>

<template>
    <DashboardPanel
        title="Replies to answer"
        :description="
            replies.total === 1
                ? 'A contact wrote back and is waiting on you'
                : `${replies.total} contacts wrote back and are waiting on you`
        "
    >
        <ul class="divide-y divide-border">
            <li v-for="reply in replies.items" :key="reply.id">
                <Link
                    :href="threadUrl(reply)"
                    class="flex items-start gap-3 px-5 py-3.5 transition-colors hover:bg-accent/40 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none focus-visible:ring-inset"
                >
                    <span class="relative mt-0.5 shrink-0">
                        <Mail class="size-4" :class="reply.unread ? 'text-orange-500' : 'text-muted-foreground'" />
                        <span
                            v-if="reply.unread"
                            class="absolute -top-1 -right-1 size-2 rounded-full bg-orange-500 ring-2 ring-card"
                            aria-label="Unread"
                        />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="flex items-baseline justify-between gap-3 text-sm">
                            <span class="truncate text-foreground" :class="reply.unread ? 'font-semibold' : 'font-medium'">
                                {{ reply.from }}
                                <span class="font-normal text-muted-foreground">· {{ reply.dealership.name }}</span>
                            </span>
                            <span class="shrink-0 text-xs text-muted-foreground tabular-nums">{{ receivedLabel(reply.receivedAt) }}</span>
                        </p>
                        <p class="truncate text-xs text-foreground/80">{{ reply.subject }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ reply.snippet }}</p>
                    </div>
                </Link>
            </li>
        </ul>
        <p v-if="replies.total > replies.items.length" class="border-t border-border px-5 py-2.5 text-xs text-muted-foreground">
            {{ replies.total - replies.items.length }} more waiting. Answer these first, or check each dealership's Emails tab.
        </p>
    </DashboardPanel>
</template>
