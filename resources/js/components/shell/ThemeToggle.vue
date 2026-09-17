<script setup lang="ts">
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const props = defineProps<{
    /** Render a single icon button that cycles through the modes. */
    cycle?: boolean;
}>();

const { appearance, updateAppearance } = useAppearance();

const modes = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
] as const;

const current = computed(
    () => modes.find((mode) => mode.value === appearance.value) ?? modes[2],
);

function cycleMode(): void {
    const index = modes.findIndex((mode) => mode.value === appearance.value);

    updateAppearance(modes[(index + 1) % modes.length].value);
}
</script>

<template>
    <button
        v-if="props.cycle"
        type="button"
        class="inline-flex size-9 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        :aria-label="`Theme: ${current.label}. Switch theme`"
        :title="`Theme: ${current.label}`"
        @click="cycleMode"
    >
        <component :is="current.icon" class="size-4" />
    </button>

    <div
        v-else
        role="radiogroup"
        aria-label="Theme"
        class="grid grid-cols-3 gap-0.5 rounded-lg border border-border bg-background p-0.5"
    >
        <button
            v-for="mode in modes"
            :key="mode.value"
            type="button"
            role="radio"
            :aria-checked="appearance === mode.value"
            :title="mode.label"
            class="flex h-7 items-center justify-center gap-1.5 rounded-md text-xs transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            :class="
                appearance === mode.value
                    ? 'bg-accent font-medium text-foreground shadow-xs'
                    : 'text-muted-foreground hover:text-foreground'
            "
            @click="updateAppearance(mode.value)"
        >
            <component :is="mode.icon" class="size-3.5" />
            <span>{{ mode.label }}</span>
        </button>
    </div>
</template>
