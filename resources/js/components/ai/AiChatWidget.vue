<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { send as sendChat } from '@/routes/ai/chat';
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

interface Props {
    dealershipId?: number | null;
    dealershipName?: string | null;
}

const props = withDefaults(defineProps<Props>(), {
    dealershipId: null,
    dealershipName: null,
});

type View = 'home' | 'chat';

interface ChatMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
}

function randomId(): string {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }
    return `id-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
}

const page = usePage();
const firstName = computed(() => {
    const name = (page.props.auth as { user?: { name?: string } } | undefined)?.user?.name ?? '';
    return name.split(' ')[0] || 'there';
});

const isOpen = ref(false);
const view = ref<View>('home');
const input = ref('');
const messages = ref<ChatMessage[]>([]);
const conversationId = ref<string | null>(null);
const isSending = ref(false);
const errorMessage = ref<string | null>(null);
const threadEl = ref<HTMLDivElement | null>(null);

const subtitle = computed(() =>
    props.dealershipName
        ? `Ask anything about ${props.dealershipName}.`
        : 'Your CRM assistant — ask about activity, opportunities, or what to do next.',
);

const popularPrompts = [
    { title: 'Summarize recent activity', prompt: 'Summarize the recent activity for this dealership.' },
    { title: 'What needs follow-up?', prompt: 'What needs follow-up here, and why?' },
    { title: 'Draft a check-in email', prompt: 'Draft a short, friendly check-in email I can send today.' },
];

function toggle() {
    isOpen.value = !isOpen.value;
}

function startChat(prompt?: string) {
    view.value = 'chat';
    if (prompt) {
        void send(prompt);
    } else {
        nextTick(() => {
            const ta = document.getElementById('ai-chat-input') as HTMLTextAreaElement | null;
            ta?.focus();
        });
    }
}

function backToHome() {
    view.value = 'home';
}

watch(messages, () => {
    nextTick(() => {
        threadEl.value?.scrollTo({ top: threadEl.value.scrollHeight, behavior: 'smooth' });
    });
}, { deep: true });

async function send(prompt?: string) {
    const text = (prompt ?? input.value).trim();
    if (!text || isSending.value) {
        return;
    }

    errorMessage.value = null;
    input.value = '';
    isSending.value = true;

    messages.value.push({
        id: randomId(),
        role: 'user',
        content: text,
    });

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 90_000);

    try {
        const xsrfMatch = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
        const xsrf = xsrfMatch ? decodeURIComponent(xsrfMatch[1]) : '';
        const res = await fetch(sendChat().url, {
            method: 'POST',
            credentials: 'same-origin',
            signal: controller.signal,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': xsrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                message: text,
                conversation_id: conversationId.value,
                dealership_id: props.dealershipId,
            }),
        });

        if (!res.ok) {
            const body = await res.text();
            throw new Error(`Request failed (${res.status}): ${body.slice(0, 120)}`);
        }

        const json = (await res.json()) as { conversation_id: string; reply: string };
        conversationId.value = json.conversation_id;
        messages.value.push({
            id: randomId(),
            role: 'assistant',
            content: json.reply,
        });
    } catch (err) {
        if (err instanceof DOMException && err.name === 'AbortError') {
            errorMessage.value = 'The assistant took too long to respond. Try again.';
        } else {
            errorMessage.value = err instanceof Error ? err.message : 'Something went wrong.';
        }
    } finally {
        clearTimeout(timeout);
        isSending.value = false;
    }
}

function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        void send();
    }
}
</script>

<template>
    <div class="pointer-events-none fixed inset-0 z-50 flex items-end justify-end p-4 sm:p-6">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-if="isOpen"
                class="bg-card text-foreground ring-border pointer-events-auto mb-3 flex h-[600px] w-[380px] max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl shadow-2xl ring-1"
            >
                <header class="border-border bg-card relative flex shrink-0 items-center justify-between border-b px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-muted text-foreground flex h-9 w-9 items-center justify-center rounded-full">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-foreground text-sm font-semibold">Assistant</p>
                            <p class="text-muted-foreground text-xs">
                                {{ dealershipName ?? 'CRM helper' }}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="text-muted-foreground hover:bg-muted hover:text-foreground rounded-md p-1 transition"
                        aria-label="Close"
                        @click="toggle"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </header>

                <div v-if="view === 'home'" class="flex flex-1 flex-col overflow-y-auto">
                    <div class="px-5 pt-6 pb-4">
                        <h2 class="text-foreground text-2xl font-bold">
                            Hi, {{ firstName }} <span class="inline-block">👋</span>
                        </h2>
                        <p class="text-muted-foreground mt-1 text-sm">
                            {{ subtitle }}
                        </p>
                        <Button class="mt-5 h-11 w-full rounded-lg text-sm font-semibold" @click="startChat()">
                            Send a message
                        </Button>
                    </div>

                    <div class="px-5 pb-6">
                        <p class="text-muted-foreground mb-2 text-xs font-medium tracking-wide uppercase">Popular topics</p>
                        <div class="border-border overflow-hidden rounded-lg border">
                            <button
                                v-for="(topic, idx) in popularPrompts"
                                :key="topic.title"
                                type="button"
                                class="hover:bg-muted flex w-full items-center justify-between px-4 py-3 text-left transition"
                                :class="idx > 0 ? 'border-border border-t' : ''"
                                @click="startChat(topic.prompt)"
                            >
                                <span class="text-foreground text-sm font-medium">{{ topic.title }}</span>
                                <svg class="text-muted-foreground h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="flex flex-1 flex-col overflow-hidden">
                    <div class="border-border flex shrink-0 items-center gap-2 border-b px-3 py-2">
                        <button
                            type="button"
                            class="text-muted-foreground hover:bg-muted hover:text-foreground rounded p-1 transition"
                            aria-label="Back"
                            @click="backToHome"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <span class="text-foreground text-sm font-medium">
                            {{ dealershipName ? `Chat — ${dealershipName}` : 'Assistant' }}
                        </span>
                    </div>

                    <div ref="threadEl" class="flex-1 space-y-3 overflow-y-auto px-4 py-4">
                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            class="flex"
                            :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                        >
                            <div
                                class="max-w-[80%] rounded-2xl px-3 py-2 text-sm whitespace-pre-wrap"
                                :class="msg.role === 'user'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted text-foreground'"
                            >
                                {{ msg.content }}
                            </div>
                        </div>

                        <div v-if="isSending" class="flex justify-start">
                            <div class="bg-muted flex items-center gap-1 rounded-2xl px-3 py-2">
                                <span class="bg-muted-foreground h-2 w-2 animate-pulse rounded-full" />
                                <span class="bg-muted-foreground h-2 w-2 animate-pulse rounded-full [animation-delay:150ms]" />
                                <span class="bg-muted-foreground h-2 w-2 animate-pulse rounded-full [animation-delay:300ms]" />
                            </div>
                        </div>

                        <p v-if="errorMessage" class="text-destructive text-center text-xs">{{ errorMessage }}</p>
                    </div>

                    <div class="border-border shrink-0 border-t p-3">
                        <div class="flex items-end gap-2">
                            <textarea
                                id="ai-chat-input"
                                v-model="input"
                                rows="1"
                                placeholder="Type a message…"
                                class="border-input bg-background text-foreground placeholder:text-muted-foreground focus:border-ring max-h-32 min-h-10 flex-1 resize-none rounded-lg border px-3 py-2 text-sm outline-none"
                                :disabled="isSending"
                                @keydown="handleKeydown"
                            />
                            <Button
                                class="h-10 rounded-lg px-4"
                                :disabled="isSending || !input.trim()"
                                @click="send()"
                            >
                                Send
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <button
            type="button"
            class="bg-primary text-primary-foreground hover:bg-primary/90 ring-border pointer-events-auto flex h-12 w-12 items-center justify-center rounded-full shadow-lg ring-1 transition"
            :aria-label="isOpen ? 'Close chat' : 'Open chat'"
            @click="toggle"
        >
            <svg v-if="!isOpen" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
            </svg>
            <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>
</template>
