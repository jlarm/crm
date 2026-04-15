<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';

const isLoading = ref(false);
let timeout: ReturnType<typeof setTimeout> | null = null;

router.on('start', () => {
    timeout = setTimeout(() => {
        isLoading.value = true;
    }, 250);
});

router.on('finish', () => {
    if (timeout) {
        clearTimeout(timeout);
        timeout = null;
    }
    isLoading.value = false;
});
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        leave-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isLoading"
            class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm"
        >
            <div
                class="flex items-center gap-3 rounded-lg border bg-card px-6 py-4 shadow-lg"
            >
                <Loader2 class="h-5 w-5 animate-spin text-primary" />
                <span class="text-sm font-medium">Loading...</span>
            </div>
        </div>
    </Transition>
</template>
