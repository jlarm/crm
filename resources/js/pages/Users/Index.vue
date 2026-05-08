<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, RotateCcw, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import type { UserListItem, UserRole } from './types';

interface Props {
    users: {
        data: UserListItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search: string;
        filter: string;
    };
    roles: UserRole[];
}

const props = defineProps<Props>();

const page = usePage();
const currentUserId = computed(
    () => (page.props.auth as { user: { id: number } }).user.id,
);

const search = ref(props.filters.search);
const filter = ref(props.filters.filter);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(search, (value) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        applyFilters({ search: value });
    }, 300);
});

watch(filter, (value) => {
    applyFilters({ filter: value });
});

function applyFilters(overrides: Partial<{ search: string; filter: string }>): void {
    router.get(
        '/users',
        {
            search: overrides.search ?? search.value,
            filter: overrides.filter ?? filter.value,
        },
        { preserveState: true, replace: true },
    );
}

const userToDelete = ref<UserListItem | null>(null);

function confirmDelete(user: UserListItem): void {
    userToDelete.value = user;
}

function cancelDelete(): void {
    userToDelete.value = null;
}

function deleteUser(): void {
    if (!userToDelete.value) {
        return;
    }
    router.delete(`/users/${userToDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            userToDelete.value = null;
        },
    });
}

function restoreUser(user: UserListItem): void {
    router.patch(`/users/${user.id}/restore`, {}, { preserveScroll: true });
}

function formatRole(name: string): string {
    return name.replace(/_/g, ' ');
}

function formatLastLogin(iso: string | null): string {
    if (!iso) {
        return 'Never';
    }
    return new Date(iso).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Users" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">Users</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Manage user accounts, roles, and permissions.
                </p>
            </div>
            <Button as-child>
                <Link href="/users/create">
                    <Plus class="size-4" />
                    New User
                </Link>
            </Button>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <Input
                v-model="search"
                placeholder="Search by name or email..."
                class="w-72"
            />
            <Select v-model="filter">
                <SelectTrigger class="w-44">
                    <SelectValue placeholder="Filter" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="active">Active users</SelectItem>
                    <SelectItem value="trashed">Deleted users</SelectItem>
                    <SelectItem value="all">All users</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Roles</TableHead>
                        <TableHead>Last login</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="users.data.length === 0">
                        <TableCell colspan="6" class="py-12 text-center text-sm text-slate-400">
                            No users found.
                        </TableCell>
                    </TableRow>
                    <TableRow v-for="user in users.data" :key="user.id">
                        <TableCell class="font-medium">{{ user.name }}</TableCell>
                        <TableCell class="text-slate-600">{{ user.email }}</TableCell>
                        <TableCell>
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    variant="secondary"
                                    class="capitalize"
                                >
                                    {{ formatRole(role.name) }}
                                </Badge>
                                <span
                                    v-if="user.roles.length === 0"
                                    class="text-xs text-slate-400"
                                >
                                    No roles
                                </span>
                            </div>
                        </TableCell>
                        <TableCell class="text-sm text-slate-600">
                            <span :class="{ 'text-slate-400': !user.lastLoginAt }">
                                {{ formatLastLogin(user.lastLoginAt) }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <Badge v-if="user.deletedAt" variant="destructive">Deleted</Badge>
                            <Badge v-else variant="outline">Active</Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <Button
                                    v-if="!user.deletedAt"
                                    variant="ghost"
                                    size="sm"
                                    as-child
                                >
                                    <Link :href="`/users/${user.id}/edit`">
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                                <Button
                                    v-if="!user.deletedAt && user.id !== currentUserId"
                                    variant="ghost"
                                    size="sm"
                                    @click="confirmDelete(user)"
                                >
                                    <Trash2 class="size-4 text-red-600" />
                                </Button>
                                <Button
                                    v-if="user.deletedAt"
                                    variant="ghost"
                                    size="sm"
                                    @click="restoreUser(user)"
                                >
                                    <RotateCcw class="size-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div
            v-if="users.last_page > 1"
            class="flex items-center justify-between text-sm text-slate-500"
        >
            <span>{{ users.from }}–{{ users.to }} of {{ users.total }}</span>
            <div class="flex gap-2">
                <Button
                    v-for="link in users.links"
                    :key="link.label"
                    variant="outline"
                    size="sm"
                    :disabled="!link.url"
                    :class="{ 'font-semibold': link.active }"
                    @click="link.url && router.get(link.url)"
                    v-html="link.label"
                />
            </div>
        </div>
    </div>

    <Dialog :open="userToDelete !== null" @update:open="(value) => !value && cancelDelete()">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete user</DialogTitle>
                <DialogDescription>
                    {{ userToDelete?.name }} will be soft deleted. They can be restored later.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="cancelDelete">Cancel</Button>
                <Button variant="destructive" @click="deleteUser">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
