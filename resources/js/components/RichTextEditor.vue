<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { Bold, Italic, Link as LinkIcon, List, ListOrdered, Redo2, Underline, Undo2 } from 'lucide-vue-next';
import { onBeforeUnmount, ref, watch, type Component } from 'vue';

/**
 * A small WYSIWYG editor for email bodies. v-model is HTML.
 *
 * The toolbar is limited to what reads well in email; the server sanitizes the
 * HTML down to the same tags, so anything pasted in beyond them is dropped.
 */
const props = defineProps<{
    id?: string;
    invalid?: boolean;
}>();

const model = defineModel<string>({ default: '' });

const editor = useEditor({
    content: model.value,
    extensions: [
        StarterKit.configure({
            heading: false,
            blockquote: false,
            code: false,
            codeBlock: false,
            horizontalRule: false,
            strike: false,
            link: {
                openOnClick: false,
                autolink: true,
                defaultProtocol: 'https',
                protocols: ['http', 'https', 'mailto'],
            },
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none min-h-48 px-3 py-2 focus:outline-none dark:prose-invert prose-p:my-2 prose-ul:my-2 prose-ol:my-2 prose-li:my-0 prose-a:text-orange-700 dark:prose-a:text-orange-400',
            ...(props.id ? { id: props.id } : {}),
            role: 'textbox',
            'aria-multiline': 'true',
        },
    },
    onUpdate: ({ editor }) => {
        model.value = editor.isEmpty ? '' : editor.getHTML();
    },
});

// Keep the editor in step when the form resets or pre-fills the body.
watch(model, (value) => {
    if (!editor.value) {
        return;
    }

    const current = editor.value.isEmpty ? '' : editor.value.getHTML();

    if (value !== current) {
        editor.value.commands.setContent(value, { emitUpdate: false });
    }
});

onBeforeUnmount(() => editor.value?.destroy());

const isLinkOpen = ref(false);
const linkUrl = ref('');

function openLink(open: boolean): void {
    isLinkOpen.value = open;

    if (open) {
        linkUrl.value = (editor.value?.getAttributes('link').href as string | undefined) ?? '';
    }
}

function applyLink(): void {
    const url = linkUrl.value.trim();
    const chain = editor.value?.chain().focus().extendMarkRange('link');

    if (url === '') {
        chain?.unsetLink().run();
    } else {
        const href = /^(https?:|mailto:)/i.test(url) ? url : url.includes('@') && !url.includes('/') ? `mailto:${url}` : `https://${url}`;
        chain?.setLink({ href }).run();
    }

    isLinkOpen.value = false;
}

function removeLink(): void {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
    isLinkOpen.value = false;
}

interface ToolbarAction {
    label: string;
    icon: Component;
    isActive?: () => boolean;
    run: () => void;
}

const formatting: ToolbarAction[] = [
    { label: 'Bold', icon: Bold, isActive: () => !!editor.value?.isActive('bold'), run: () => editor.value?.chain().focus().toggleBold().run() },
    { label: 'Italic', icon: Italic, isActive: () => !!editor.value?.isActive('italic'), run: () => editor.value?.chain().focus().toggleItalic().run() },
    { label: 'Underline', icon: Underline, isActive: () => !!editor.value?.isActive('underline'), run: () => editor.value?.chain().focus().toggleUnderline().run() },
];

const lists: ToolbarAction[] = [
    { label: 'Bulleted list', icon: List, isActive: () => !!editor.value?.isActive('bulletList'), run: () => editor.value?.chain().focus().toggleBulletList().run() },
    { label: 'Numbered list', icon: ListOrdered, isActive: () => !!editor.value?.isActive('orderedList'), run: () => editor.value?.chain().focus().toggleOrderedList().run() },
];

const history: ToolbarAction[] = [
    { label: 'Undo', icon: Undo2, run: () => editor.value?.chain().focus().undo().run() },
    { label: 'Redo', icon: Redo2, run: () => editor.value?.chain().focus().redo().run() },
];
</script>

<template>
    <div
        class="rounded-md border bg-transparent shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50 dark:bg-input/30"
        :class="invalid ? 'border-destructive ring-destructive/20' : 'border-input'"
    >
        <div class="flex flex-wrap items-center gap-0.5 border-b border-input px-1.5 py-1" role="toolbar" aria-label="Formatting">
            <template v-for="(group, index) in [formatting, lists]" :key="index">
                <button
                    v-for="action in group"
                    :key="action.label"
                    type="button"
                    class="rounded p-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                    :class="{ 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100': action.isActive?.() }"
                    :title="action.label"
                    :aria-label="action.label"
                    :aria-pressed="action.isActive?.() ?? undefined"
                    @click="action.run"
                >
                    <component :is="action.icon" class="h-4 w-4" />
                </button>
                <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700" aria-hidden="true" />
            </template>

            <Popover :open="isLinkOpen" @update:open="openLink">
                <PopoverTrigger as-child>
                    <button
                        type="button"
                        class="rounded p-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                        :class="{ 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100': editor?.isActive('link') }"
                        title="Link"
                        aria-label="Link"
                    >
                        <LinkIcon class="h-4 w-4" />
                    </button>
                </PopoverTrigger>
                <PopoverContent class="w-80" align="start">
                    <form class="flex flex-col gap-2" @submit.prevent="applyLink">
                        <label for="rich_text_link" class="text-xs font-medium">Link address</label>
                        <Input id="rich_text_link" v-model="linkUrl" placeholder="example.com" autocomplete="off" />
                        <div class="flex justify-end gap-2">
                            <Button v-if="editor?.isActive('link')" type="button" variant="ghost" size="sm" @click="removeLink">Remove</Button>
                            <Button type="submit" size="sm">Apply</Button>
                        </div>
                    </form>
                </PopoverContent>
            </Popover>

            <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700" aria-hidden="true" />

            <button
                v-for="action in history"
                :key="action.label"
                type="button"
                class="rounded p-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                :title="action.label"
                :aria-label="action.label"
                @click="action.run"
            >
                <component :is="action.icon" class="h-4 w-4" />
            </button>
        </div>

        <EditorContent :editor="editor" class="max-h-96 overflow-y-auto" />
    </div>
</template>
