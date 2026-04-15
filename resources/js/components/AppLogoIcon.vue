<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/img/logo.svg"
        alt="Eagles logo"
        :class="className"
        v-bind="$attrs"
    />
</template>

