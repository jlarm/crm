<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { X } from 'lucide-vue-next';
import { computed, ref, useId } from 'vue';

interface Props {
    /** Existing tag names offered as suggestions while typing. */
    suggestions?: string[];
    /** When set, each tag is also posted as a hidden `${name}[]` input inside a surrounding form. */
    name?: string;
    placeholder?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    suggestions: () => [],
    name: undefined,
    placeholder: 'Add a tag…',
    disabled: false,
});

const tags = defineModel<string[]>({ required: true });

const MAX_LENGTH = 50;
const listId = `tag-suggestions-${useId()}`;
const draft = ref('');

const availableSuggestions = computed(() => {
    const taken = new Set(tags.value.map((tag) => tag.toLowerCase()));

    return props.suggestions.filter((tag) => !taken.has(tag.toLowerCase()));
});

function addDraft(): void {
    const tag = draft.value.replace(/\s+/g, ' ').trim().slice(0, MAX_LENGTH);
    draft.value = '';

    if (!tag || tags.value.some((existing) => existing.toLowerCase() === tag.toLowerCase())) {
        return;
    }

    // Reuse the stored casing when it matches an existing tag.
    const existing = props.suggestions.find((s) => s.toLowerCase() === tag.toLowerCase());
    tags.value = [...tags.value, existing ?? tag];
}

function removeTag(tag: string): void {
    tags.value = tags.value.filter((t) => t !== tag);
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        addDraft();
    } else if (event.key === 'Backspace' && draft.value === '' && tags.value.length > 0) {
        removeTag(tags.value[tags.value.length - 1]);
    }
}

/** Picking from the datalist fills the input without a keypress, so add it straight away. */
function onInput(event: Event): void {
    if ((event as InputEvent).inputType === undefined || (event as InputEvent).inputType === 'insertReplacementText') {
        const value = draft.value.trim().toLowerCase();
        if (props.suggestions.some((s) => s.toLowerCase() === value)) {
            addDraft();
        }
    }
}
</script>

<template>
    <div class="space-y-2">
        <template v-if="name">
            <input v-for="tag in tags" :key="tag" type="hidden" :name="`${name}[]`" :value="tag" />
        </template>

        <Input
            v-model="draft"
            :list="listId"
            :placeholder="placeholder"
            :disabled="disabled"
            :maxlength="MAX_LENGTH"
            autocomplete="off"
            @keydown="onKeydown"
            @input="onInput"
            @blur="addDraft"
        />
        <datalist :id="listId">
            <option v-for="tag in availableSuggestions" :key="tag" :value="tag" />
        </datalist>

        <div v-if="tags.length" class="flex flex-wrap gap-1.5">
            <span
                v-for="tag in tags"
                :key="tag"
                class="inline-flex items-center gap-1 rounded-md border border-border bg-muted px-2 py-0.5 text-xs font-medium text-foreground"
            >
                {{ tag }}
                <button
                    type="button"
                    class="rounded text-muted-foreground hover:text-foreground"
                    :aria-label="`Remove tag ${tag}`"
                    :disabled="disabled"
                    @click="removeTag(tag)"
                >
                    <X class="size-3" />
                </button>
            </span>
        </div>
    </div>
</template>
