<script setup lang="ts">
import UserForm from '@/pages/Users/Form.vue';
import type { UserRole } from '@/pages/Users/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{
    roles: UserRole[];
}>();
</script>

<template>
    <Head title="New User" />

    <div class="px-8 py-3">
        <div class="flex shrink-0 items-center gap-4">
            <Link
                href="/users"
                class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-900"
            >
                <ArrowLeft class="size-4" />
                Users
            </Link>
        </div>
        <h1 class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">
            New User
        </h1>

        <div class="mx-auto mt-5 w-full">
            <UserForm
                :roles="roles"
                submit-url="/users"
                method="post"
            />
        </div>
    </div>
</template>
