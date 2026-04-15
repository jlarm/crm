<script setup>
import { ref } from 'vue'

const count = ref(0)
</script>

<template>
    <div style="font-family: sans-serif; max-width: 600px; margin: 80px auto; text-align: center;">
        <h1>Inertia + Vue is working!</h1>
        <p>If you can see this page, the server-side and client-side setup is complete.</p>
        <hr style="margin: 32px 0;">
        <p>Vue reactivity test:</p>
        <button @click="count++" style="padding: 8px 20px; font-size: 16px; cursor: pointer;">
            Clicked {{ count }} {{ count === 1 ? 'time' : 'times' }}
        </button>
    </div>
</template>
