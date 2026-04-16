<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import SearchableSelect from '@/components/ui/searchable-select/SearchableSelect.vue';
import { Textarea } from '@/components/ui/textarea';
import type { FilterOption, Task } from '@/pages/Tasks/types';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

interface User {
    id: number;
    name: string;
}

interface Dealership {
    id: number;
    name: string;
}

const props = defineProps<{
    open: boolean;
    task?: Task | null;
    allUsers: User[];
    allDealerships?: Dealership[];
    types: FilterOption[];
    priorities: FilterOption[];
    dealershipId?: number | null;
    currentUserId: number;
}>();

const emit = defineEmits<{
    (event: 'update:open', value: boolean): void;
}>();

const form = useForm({
    title: '',
    description: '',
    type: 'call',
    priority: 'medium',
    due_date: '',
    user_id: props.currentUserId,
    dealership_id: props.dealershipId ?? null as number | null,
    contact_id: null as number | null,
});

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            return;
        }

        if (props.task) {
            form.title = props.task.title;
            form.description = props.task.description ?? '';
            form.type = props.task.type;
            form.priority = props.task.priority;
            form.due_date = props.task.dueDate ?? '';
            form.user_id = props.task.userId;
            form.dealership_id = props.task.dealershipId;
            form.contact_id = props.task.contactId;
        } else {
            form.reset();
            form.user_id = props.currentUserId;
            form.dealership_id = props.dealershipId ?? null;
        }
    },
);

function submit(): void {
    if (props.task) {
        form.put(`/tasks/${props.task.id}`, {
            preserveScroll: true,
            onSuccess: () => emit('update:open', false),
        });
    } else {
        form.post('/tasks', {
            preserveScroll: true,
            onSuccess: () => {
                emit('update:open', false);
                form.reset();
                form.user_id = props.currentUserId;
                form.dealership_id = props.dealershipId ?? null;
            },
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ task ? 'Edit Task' : 'New Task' }}</DialogTitle>
            </DialogHeader>

            <form class="grid grid-cols-2 gap-4" @submit.prevent="submit">
                <Field class="col-span-2">
                    <FieldLabel for="task_title">Title</FieldLabel>
                    <Input
                        id="task_title"
                        v-model="form.title"
                        placeholder="e.g. Follow up call with manager"
                        required
                    />
                    <InputError :message="form.errors.title" />
                </Field>

                <Field class="col-span-2">
                    <FieldLabel for="task_description">Description</FieldLabel>
                    <Textarea
                        id="task_description"
                        v-model="form.description"
                        rows="2"
                        placeholder="Optional notes..."
                    />
                </Field>

                <Field>
                    <FieldLabel for="task_type">Type</FieldLabel>
                    <Select v-model="form.type">
                        <SelectTrigger id="task_type">
                            <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in types"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.type" />
                </Field>

                <Field>
                    <FieldLabel for="task_priority">Priority</FieldLabel>
                    <Select v-model="form.priority">
                        <SelectTrigger id="task_priority">
                            <SelectValue placeholder="Select priority" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in priorities"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.priority" />
                </Field>

                <Field>
                    <FieldLabel>Due Date</FieldLabel>
                    <DatePicker
                        :model-value="form.due_date || null"
                        @update:model-value="form.due_date = $event ?? ''"
                    />
                    <InputError :message="form.errors.due_date" />
                </Field>

                <Field>
                    <FieldLabel for="task_assigned_to">Assign To</FieldLabel>
                    <Select
                        :model-value="String(form.user_id)"
                        @update:model-value="form.user_id = Number($event)"
                    >
                        <SelectTrigger id="task_assigned_to">
                            <SelectValue placeholder="Select user" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="user in allUsers"
                                :key="user.id"
                                :value="String(user.id)"
                            >
                                {{ user.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.user_id" />
                </Field>

                <!-- Only show dealership picker when not locked to a specific dealership -->
                <Field
                    v-if="dealershipId == null && allDealerships && allDealerships.length > 0"
                    class="col-span-2"
                >
                    <FieldLabel>Dealership</FieldLabel>
                    <SearchableSelect
                        :model-value="form.dealership_id != null ? String(form.dealership_id) : null"
                        :options="allDealerships.map((d) => ({ value: String(d.id), label: d.name }))"
                        placeholder="No dealership"
                        @update:model-value="form.dealership_id = $event != null ? Number($event) : null"
                    />
                </Field>

                <DialogFooter class="col-span-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ task ? 'Save Changes' : 'Create Task' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
