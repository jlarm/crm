<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { FileText, Upload, X } from "lucide-vue-next"
import { computed, ref } from "vue"
import { Button } from "@/components/ui/button"
import { cn } from "@/lib/utils"

/**
 * A drop zone around a native file input. The input keeps its `name`, so a surrounding
 * Inertia <Form> submits the file like any other field.
 */
const props = withDefaults(defineProps<{
  id?: string
  name?: string
  accept?: string
  required?: boolean
  disabled?: boolean
  invalid?: boolean
  /** Short line under the prompt, e.g. "CSV up to 10 MB". */
  hint?: string
  class?: HTMLAttributes["class"]
}>(), {
  id: undefined,
  name: undefined,
  accept: undefined,
  hint: undefined,
  class: undefined,
})

const emits = defineEmits<{
  (e: "update:file", file: File | null): void
}>()

const input = ref<HTMLInputElement | null>(null)
const file = ref<File | null>(null)
const dragging = ref(false)

const size = computed(() => {
  if (!file.value) {
    return ""
  }
  const kb = file.value.size / 1024

  return kb < 1024 ? `${Math.max(1, Math.round(kb))} KB` : `${(kb / 1024).toFixed(1)} MB`
})

function setFile(next: File | null): void {
  file.value = next
  emits("update:file", next)
}

function browse(): void {
  if (!props.disabled) {
    input.value?.click()
  }
}

function onChange(): void {
  setFile(input.value?.files?.[0] ?? null)
}

function onDrop(event: DragEvent): void {
  dragging.value = false
  const dropped = event.dataTransfer?.files?.[0]

  if (props.disabled || !dropped || !input.value) {
    return
  }

  // Put the dropped file on the real input so the form submits it.
  const transfer = new DataTransfer()
  transfer.items.add(dropped)
  input.value.files = transfer.files
  setFile(dropped)
}

function clear(): void {
  if (input.value) {
    input.value.value = ""
  }
  setFile(null)
}
</script>

<template>
  <div
    data-slot="file-upload"
    :data-dragging="dragging || undefined"
    :aria-invalid="invalid || undefined"
    :class="cn(
      'border-input dark:bg-input/30 relative flex w-full items-center gap-3 rounded-md border border-dashed bg-transparent p-4 text-sm shadow-xs transition-[color,box-shadow,border-color]',
      'has-[input:focus-visible]:border-ring has-[input:focus-visible]:ring-ring/50 has-[input:focus-visible]:ring-[3px]',
      'data-[dragging]:border-primary data-[dragging]:bg-accent/50',
      'aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40',
      disabled && 'pointer-events-none opacity-50',
      !file && 'cursor-pointer hover:bg-accent/40',
      props.class,
    )"
    @click="!file && browse()"
    @dragover.prevent="dragging = true"
    @dragleave.prevent="dragging = false"
    @drop.prevent="onDrop"
  >
    <input
      :id="id"
      ref="input"
      type="file"
      class="sr-only"
      :name="name"
      :accept="accept"
      :required="required"
      :disabled="disabled"
      @change="onChange"
      @click.stop
    >

    <template v-if="file">
      <div class="bg-muted text-muted-foreground flex size-10 shrink-0 items-center justify-center rounded-md">
        <FileText class="size-5" />
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-foreground truncate font-medium">
          {{ file.name }}
        </p>
        <p class="text-muted-foreground text-xs">
          {{ size }}
        </p>
      </div>
      <Button type="button" variant="outline" size="sm" @click.stop="browse">
        Replace
      </Button>
      <Button type="button" variant="ghost" size="icon" aria-label="Remove file" @click.stop="clear">
        <X class="size-4" />
      </Button>
    </template>

    <template v-else>
      <div class="bg-muted text-muted-foreground flex size-10 shrink-0 items-center justify-center rounded-md">
        <Upload class="size-5" />
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-foreground font-medium">
          Drop a file here or <span class="underline underline-offset-4">browse</span>
        </p>
        <p v-if="hint" class="text-muted-foreground text-xs">
          {{ hint }}
        </p>
      </div>
    </template>
  </div>
</template>
