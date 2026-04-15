<script setup lang="ts">
import { type PopoverTriggerProps, PopoverTrigger } from 'reka-ui';

defineProps<PopoverTriggerProps>();
</script>

<template>
    <PopoverTrigger v-bind="$props">
        <slot />
    </PopoverTrigger>
</template>
