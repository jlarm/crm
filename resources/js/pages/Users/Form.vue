<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import type { UserListItem, UserRole } from './types';

interface Props {
    user?: UserListItem | null;
    roles: UserRole[];
    submitUrl: string;
    method: 'post' | 'put';
}

const props = defineProps<Props>();

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    phone: props.user?.phone ?? '',
    timezone: props.user?.timezone ?? '',
    password: '',
    password_confirmation: '',
    roles: props.user?.roles?.map((role) => role.id) ?? [],
});

function toggleRole(roleId: number, checked: boolean | 'indeterminate'): void {
    if (checked === true) {
        if (!form.roles.includes(roleId)) {
            form.roles = [...form.roles, roleId];
        }
    } else {
        form.roles = form.roles.filter((id) => id !== roleId);
    }
}

function submit(): void {
    if (props.method === 'post') {
        form.post(props.submitUrl);
    } else {
        form.put(props.submitUrl);
    }
}
</script>

<template>
    <form class="grid grid-cols-1 gap-5 md:grid-cols-3" @submit.prevent="submit">
        <div class="col-span-2 space-y-5">
            <Card>
                <CardHeader>
                    <CardTitle>Account</CardTitle>
                </CardHeader>
                <CardContent>
                    <FieldGroup>
                        <div class="grid grid-cols-2 gap-5">
                            <Field class="col-span-2">
                                <FieldLabel for="name">Name</FieldLabel>
                                <Input id="name" v-model="form.name" required autofocus />
                                <InputError :message="form.errors.name" />
                            </Field>

                            <Field class="col-span-2">
                                <FieldLabel for="email">Email</FieldLabel>
                                <Input id="email" v-model="form.email" type="email" required />
                                <InputError :message="form.errors.email" />
                            </Field>

                            <Field class="col-span-1">
                                <FieldLabel for="phone">Phone</FieldLabel>
                                <Input id="phone" v-model="form.phone" />
                                <InputError :message="form.errors.phone" />
                            </Field>

                            <Field class="col-span-1">
                                <FieldLabel for="timezone">Timezone</FieldLabel>
                                <Input
                                    id="timezone"
                                    v-model="form.timezone"
                                    placeholder="America/New_York"
                                />
                                <InputError :message="form.errors.timezone" />
                            </Field>

                            <Field class="col-span-2">
                                <FieldLabel for="password">
                                    Password
                                    <span v-if="user" class="ml-1 text-xs text-slate-500">
                                        (leave blank to keep current)
                                    </span>
                                </FieldLabel>
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    :required="!user"
                                    autocomplete="new-password"
                                />
                                <InputError :message="form.errors.password" />
                            </Field>

                            <Field class="col-span-2">
                                <FieldLabel for="password_confirmation">Confirm Password</FieldLabel>
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    :required="!user"
                                    autocomplete="new-password"
                                />
                            </Field>
                        </div>
                    </FieldGroup>
                </CardContent>
            </Card>
        </div>

        <div class="self-start space-y-5">
            <Card>
                <CardHeader>
                    <CardTitle>Roles &amp; Permissions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="roles.length === 0" class="text-sm text-slate-500">
                        No roles defined.
                    </div>
                    <div v-else class="space-y-3">
                        <label
                            v-for="role in roles"
                            :key="role.id"
                            class="flex cursor-pointer items-center gap-3 rounded-md border border-transparent px-2 py-1.5 hover:border-slate-200 hover:bg-slate-50"
                        >
                            <Checkbox
                                :model-value="form.roles.includes(role.id)"
                                @update:model-value="toggleRole(role.id, $event)"
                            />
                            <span class="text-sm capitalize">
                                {{ role.name.replace(/_/g, ' ') }}
                            </span>
                        </label>
                    </div>
                    <InputError :message="form.errors.roles" class="mt-2" />
                </CardContent>
            </Card>
        </div>

        <div class="col-span-full flex justify-end">
            <Button type="submit" :disabled="form.processing">
                <Save />
                {{ user ? 'Save Changes' : 'Create User' }}
            </Button>
        </div>
    </form>
</template>
