<script setup lang="ts">
import { formatPhone } from '@/lib/phone';
import { Button } from '@/components/ui/button';
import { lookup } from '@/routes/dealerships';
import { useHttp } from '@inertiajs/vue3';
import { AlertTriangle, ExternalLink, Sparkles, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

export interface LikelyDuplicate {
    reason: string;
    match: { id: number; name: string; city: string | null; state: string | null };
}

export interface DealershipLookupResult {
    name: string;
    address: string | null;
    city: string | null;
    state: string | null;
    zip_code: string | null;
    phone: string | null;
    website: string | null;
    sourceUrl: string | null;
    duplicate: LikelyDuplicate | null;
}

const props = defineProps<{
    name: string;
    city: string;
    state: string;
}>();

const emit = defineEmits<{
    (event: 'pick', result: DealershipLookupResult): void;
}>();

const MIN_NAME_LENGTH = 3;

const searcher = useHttp<{ name: string; city: string; state: string }, DealershipLookupResult[]>({
    name: '',
    city: '',
    state: '',
});

const results = ref<DealershipLookupResult[] | null>(null);
const error = ref<string | null>(null);

const canSearch = computed(() => props.name.trim().length >= MIN_NAME_LENGTH && !searcher.processing);

function search(): void {
    error.value = null;
    results.value = null;
    searcher.name = props.name;
    searcher.city = props.city;
    searcher.state = props.state;

    searcher.post(lookup().url, {
        onSuccess: (response: DealershipLookupResult[]) => {
            results.value = response;
        },
        onHttpException: (response) => {
            error.value = response.status === 429
                ? 'Too many lookups in a row. Wait a minute and try again.'
                : errorMessageFrom(response.data);
        },
    });
}

function errorMessageFrom(body: string): string {
    try {
        const parsed: unknown = JSON.parse(body);

        if (parsed && typeof parsed === 'object' && 'message' in parsed && typeof parsed.message === 'string') {
            return parsed.message;
        }
    } catch {
        // Not JSON; fall through to the generic message.
    }

    return "Couldn't search for this dealership right now. You can still fill in the details by hand.";
}

function pick(result: DealershipLookupResult): void {
    emit('pick', result);
    results.value = null;
}

function location(result: DealershipLookupResult): string {
    const cityState = [result.city, result.state].filter(Boolean).join(', ');

    return [result.address, [cityState, result.zip_code].filter(Boolean).join(' ')].filter(Boolean).join(', ');
}

function hostname(url: string): string {
    try {
        return new URL(url).hostname.replace(/^www\./, '');
    } catch {
        return url;
    }
}
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center gap-2">
            <Button
                type="button"
                variant="outline"
                size="sm"
                :disabled="!canSearch"
                :title="canSearch ? 'Search the web for this dealership and fill in its details' : 'Type at least 3 letters of the name first'"
                @click="search"
            >
                <Sparkles class="h-3.5 w-3.5" />
                {{ searcher.processing ? 'Searching the web…' : 'Find details online' }}
            </Button>
            <span v-if="searcher.processing" class="text-xs text-muted-foreground">This usually takes 10–30 seconds.</span>
            <span v-else-if="!results && !error" class="text-xs text-muted-foreground">
                Add the city and state for better matches.
            </span>
        </div>

        <div v-if="searcher.processing" class="space-y-2 rounded-lg border border-violet-200 p-3 dark:border-violet-900">
            <div class="h-3 w-1/3 animate-pulse rounded bg-violet-100 dark:bg-violet-900/50" />
            <div class="h-3 w-full animate-pulse rounded bg-violet-100 dark:bg-violet-900/50" />
            <div class="h-3 w-2/3 animate-pulse rounded bg-violet-100 dark:bg-violet-900/50" />
        </div>

        <p v-else-if="error" class="text-xs text-red-600 dark:text-red-400">{{ error }}</p>
        <p v-if="searcher.errors.name" class="text-xs text-red-600 dark:text-red-400">{{ searcher.errors.name }}</p>

        <div v-if="results" class="rounded-lg border border-violet-200 dark:border-violet-900">
            <div class="flex items-center justify-between border-b border-violet-100 px-3 py-2 dark:border-violet-900/60">
                <p class="text-xs font-medium text-violet-700 dark:text-violet-300">
                    {{ results.length ? 'Pick the right business. Details were found by AI, so double-check them.' : 'No matching businesses found. Try adding the city and state.' }}
                </p>
                <button
                    type="button"
                    class="text-muted-foreground hover:text-foreground"
                    aria-label="Dismiss results"
                    @click="results = null"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
            <ul v-if="results.length" class="divide-y divide-violet-100 dark:divide-violet-900/60">
                <li v-for="(result, index) in results" :key="index" class="flex items-start justify-between gap-3 px-3 py-2.5">
                    <div class="min-w-0 text-sm">
                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ result.name }}</p>
                        <p v-if="location(result)" class="text-xs text-muted-foreground">{{ location(result) }}</p>
                        <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-muted-foreground">
                            <span v-if="result.phone">{{ formatPhone(result.phone) }}</span>
                            <span v-if="result.website">{{ hostname(result.website) }}</span>
                            <a
                                v-if="result.sourceUrl"
                                :href="result.sourceUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-0.5 underline underline-offset-2 hover:text-foreground"
                            >
                                Source <ExternalLink class="h-3 w-3" />
                            </a>
                        </p>
                        <p v-if="result.duplicate" class="mt-1 flex items-center gap-1 text-xs text-amber-700 dark:text-amber-400">
                            <AlertTriangle class="h-3 w-3" />
                            {{ result.duplicate.match.name }} may already be in the CRM
                        </p>
                    </div>
                    <Button type="button" size="sm" variant="secondary" @click="pick(result)">Use this</Button>
                </li>
            </ul>
        </div>
    </div>
</template>
