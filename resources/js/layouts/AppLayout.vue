<script setup lang="ts">
import AiChatWidget from '@/components/ai/AiChatWidget.vue';
import AppHeaderLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';
import { toast, Toaster } from 'vue-sonner';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

type PageDealership = { id: number; name: string } | undefined;

const page = usePage<{ flash: { success?: string }; dealership?: PageDealership }>();

const aiDealership = computed<PageDealership>(() => {
    const d = page.props.dealership;
    return d && typeof d === 'object' && 'id' in d ? d : undefined;
});

function showFlash(props: Record<string, unknown>): void {
    const message = (props.flash as { success?: string })?.success;
    if (message) {
        toast.success(message);
    }
}

// Fire on initial page load
onMounted(() => showFlash(page.props));

// Fire on every subsequent Inertia navigation
const removeListener = router.on('success', (event) => {
    showFlash(event.detail.page.props as Record<string, unknown>);
});

onUnmounted(removeListener);
</script>

<template>
    <Toaster
        position="top-right"
        :toast-options="{
            style: {
                background: 'white',
                border: '1px solid #e5e7eb',
                color: '#111827',
            },
        }"
    />
    <AppHeaderLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppHeaderLayout>
    <AiChatWidget
        :dealership-id="aiDealership?.id ?? null"
        :dealership-name="aiDealership?.name ?? null"
    />
</template>
