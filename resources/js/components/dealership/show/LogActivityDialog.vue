<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldLabel } from '@/components/ui/field';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { Dealership, ProgressCategory } from '@/pages/Dealership/types';
import { store } from '@/routes/dealerships/progress';
import { useForm } from '@inertiajs/vue3';
import { Mail, Phone, StickyNote, Users } from 'lucide-vue-next';
import { watch, type Component } from 'vue';

const props = defineProps<{
    dealership: Dealership;
    categories: ProgressCategory[];
}>();

const emit = defineEmits<{
    (event: 'logged'): void;
}>();

const open = defineModel<boolean>('open', { default: false });

const NO_CONTACT = 'none';

const categoryIcons: Record<string, Component> = {
    Phone,
    Email: Mail,
    'In Person': Users,
};

function today(): string {
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');

    return `${now.getFullYear()}-${month}-${day}`;
}

const form = useForm({
    progress_category_id: null as number | null,
    contact_id: NO_CONTACT as string,
    date: today() as string | null,
    details: '',
});

watch(open, (isOpen) => {
    if (isOpen) {
        form.reset();
        form.clearErrors();
        form.progress_category_id = props.categories[0]?.id ?? null;
        form.date = today();
    }
});

function submit(): void {
    form
        .transform((data) => ({
            ...data,
            contact_id: data.contact_id === NO_CONTACT ? null : Number(data.contact_id),
        }))
        .submit(store(props.dealership.id), {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                emit('logged');
            },
        });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Log activity</DialogTitle>
                <DialogDescription>Record a call, email or meeting that happened outside the CRM.</DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <Field>
                    <FieldLabel>Type</FieldLabel>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="category in categories"
                            :key="category.id"
                            type="button"
                            class="flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition-colors"
                            :class="
                                form.progress_category_id === category.id
                                    ? 'border-orange-500 bg-orange-50 text-orange-700 dark:bg-orange-950 dark:text-orange-300'
                                    : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            :aria-pressed="form.progress_category_id === category.id"
                            @click="form.progress_category_id = category.id"
                        >
                            <component :is="categoryIcons[category.name] ?? StickyNote" class="h-3 w-3" />
                            {{ category.name }}
                        </button>
                    </div>
                    <InputError :message="form.errors.progress_category_id" />
                </Field>

                <div class="grid grid-cols-2 gap-4">
                    <Field>
                        <FieldLabel for="activity_contact">Contact</FieldLabel>
                        <Select v-model="form.contact_id">
                            <SelectTrigger id="activity_contact" class="w-full">
                                <SelectValue placeholder="No contact" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="NO_CONTACT">No contact</SelectItem>
                                <SelectItem
                                    v-for="contact in dealership.contacts"
                                    :key="contact.id"
                                    :value="String(contact.id)"
                                >
                                    {{ contact.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.contact_id" />
                    </Field>

                    <Field>
                        <FieldLabel>Date</FieldLabel>
                        <DatePicker v-model="form.date" placeholder="Pick a date" />
                        <InputError :message="form.errors.date" />
                    </Field>
                </div>

                <Field>
                    <FieldLabel for="activity_details">Notes</FieldLabel>
                    <Textarea
                        id="activity_details"
                        v-model="form.details"
                        :rows="4"
                        placeholder="What was discussed?"
                        required
                    />
                    <InputError :message="form.errors.details" />
                </Field>

                <DialogFooter>
                    <Button type="submit" :disabled="form.processing">Log activity</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
