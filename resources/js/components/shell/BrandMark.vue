<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    compact?: boolean;
}>();
</script>

<template>
    <span class="flex items-center gap-2.5">
        <AppLogoIcon class="size-7 shrink-0 object-contain" alt="" />
        <span
            v-if="!compact"
            class="truncate text-[17px] font-semibold tracking-tight text-foreground"
        >
            ARMP<span class="font-normal text-muted-foreground"> CRM</span>
        </span>
    </span>
</template>
