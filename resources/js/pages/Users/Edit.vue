<script setup lang="ts">
import UserForm from '@/pages/Users/Form.vue';
import type { UserListItem, UserRole } from '@/pages/Users/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    user: UserListItem;
    roles: UserRole[];
}>();
</script>

<template>
    <Head :title="`Edit ${props.user.name}`" />

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
            Edit {{ props.user.name }}
        </h1>

        <div class="mx-auto mt-5 w-full">
            <UserForm
                :user="props.user"
                :roles="props.roles"
                :submit-url="`/users/${props.user.id}`"
                method="put"
            />
        </div>
    </div>
</template>
