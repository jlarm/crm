<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { User } from '@/pages/Dealership/types';
import DealershipImportController from '@/actions/App/Http/Controllers/DealershipImportController';
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Download, Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface PreviewRow {
    line: number;
    rowType: 'dealership' | 'store' | 'contact';
    resolved: Record<string, string | number | boolean | null>;
    errors: Record<string, string[]>;
    parentRef: string | null;
}

interface ParseError {
    line: number;
    message: string;
}

interface Preview {
    token: string;
    defaults: { status: string; rating: string; type: string };
    defaultUserIds: number[];
    options: {
        sync_mailcoach: boolean;
        update_existing: boolean;
        transactional: boolean;
    };
    summary: {
        dealerships: number;
        stores: number;
        contacts: number;
        errors: number;
        autoCreatedDealerships: number;
    };
    parseErrors: ParseError[];
    rows: PreviewRow[];
}

interface Props {
    allUsers: User[];
    preview: Preview | null;
}

const props = defineProps<Props>();

const defaultStatus = ref('active');
const defaultRating = ref('warm');
const defaultType = ref('Automotive');
const syncMailcoach = ref(false);
const updateExisting = ref(false);
const transactional = ref(true);

const consultantSearch = ref('');
const selectedConsultantIds = ref<number[]>([]);

const selectedConsultants = computed(() =>
    props.allUsers.filter((u) => selectedConsultantIds.value.includes(u.id)),
);

const filteredConsultants = computed(() => {
    const search = consultantSearch.value.trim().toLowerCase();
    const available = props.allUsers.filter(
        (u) => !selectedConsultantIds.value.includes(u.id),
    );
    if (!search) {
        return available;
    }
    return available.filter((u) => u.name.toLowerCase().includes(search));
});

function toggleConsultant(userId: number, add: boolean): void {
    if (add) {
        if (!selectedConsultantIds.value.includes(userId)) {
            selectedConsultantIds.value = [...selectedConsultantIds.value, userId];
        }
    } else {
        selectedConsultantIds.value = selectedConsultantIds.value.filter(
            (id) => id !== userId,
        );
    }
}

const errorRows = computed(
    () => props.preview?.rows.filter((r) => Object.keys(r.errors).length > 0) ?? [],
);

const validRowCount = computed(() => {
    if (!props.preview) {
        return 0;
    }
    return props.preview.rows.length - errorRows.value.length;
});

function formatErrors(errors: Record<string, string[]>): string {
    return Object.entries(errors)
        .map(([field, messages]) => `${field}: ${messages.join('; ')}`)
        .join(' | ');
}
</script>

<template>
    <Head title="Import Dealerships" />

    <div class="px-8 py-3">
        <div class="flex shrink-0 items-center gap-4">
            <Link href="/dashboard">
                <Button variant="ghost" size="icon">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
            </Link>
            <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">
                Import Dealerships
            </h1>
        </div>

        <!-- Step 1: upload form (always shown; preview shown below if present) -->
        <div class="mx-auto mt-5 w-full">
            <Form
                :action="DealershipImportController.preview.url()"
                method="post"
                enctype="multipart/form-data"
                class="grid grid-cols-1 gap-5 md:grid-cols-3"
                v-slot="{ errors, processing }"
            >
                <div class="col-span-2 space-y-5">
                    <Card>
                        <CardHeader>
                            <CardTitle>CSV file</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <FieldGroup>
                                <Field>
                                    <FieldLabel for="file">CSV file</FieldLabel>
                                    <Input
                                        id="file"
                                        name="file"
                                        type="file"
                                        accept=".csv,text/csv"
                                        required
                                    />
                                    <InputError :message="errors.file" />
                                    <p class="mt-2 text-xs text-slate-500">
                                        Required column: <code>name</code> (or <code>First Name</code> + <code>Last Name</code> for contacts).
                                        Common headers like <code>Email</code>, <code>jobTitle</code>, <code>linkedIn</code>, <code>companyName</code>,
                                        <code>Phone</code>, and <code>Address</code> are recognized automatically.
                                        Contact-only CSVs with a company column will auto-create dealerships using the defaults below.
                                    </p>
                                    <a
                                        href="/examples/dealership-import-example.csv"
                                        download
                                        class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100"
                                    >
                                        <Download class="h-3.5 w-3.5" />
                                        Download example CSV
                                    </a>
                                </Field>
                            </FieldGroup>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Options</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <label class="flex items-center gap-3 text-sm">
                                <Checkbox
                                    :model-value="syncMailcoach"
                                    @update:model-value="(v) => (syncMailcoach = v === true)"
                                />
                                <input type="hidden" name="sync_mailcoach" :value="syncMailcoach ? 1 : 0" />
                                <span>Sync new contacts to Mailcoach (off by default)</span>
                            </label>

                            <label class="flex items-center gap-3 text-sm">
                                <Checkbox
                                    :model-value="updateExisting"
                                    @update:model-value="(v) => (updateExisting = v === true)"
                                />
                                <input type="hidden" name="update_existing" :value="updateExisting ? 1 : 0" />
                                <span>Update existing dealerships matched by name</span>
                            </label>

                            <label class="flex items-center gap-3 text-sm">
                                <Checkbox
                                    :model-value="transactional"
                                    @update:model-value="(v) => (transactional = v === true)"
                                />
                                <input type="hidden" name="transactional" :value="transactional ? 1 : 0" />
                                <span>All-or-nothing: roll back the entire import on any error</span>
                            </label>
                        </CardContent>
                    </Card>
                </div>

                <div class="self-start space-y-5">
                    <Card>
                        <CardHeader>
                            <CardTitle>Defaults</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <Field>
                                    <FieldLabel for="default_type">Type</FieldLabel>
                                    <Select v-model="defaultType" name="default_type">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Automotive">Automotive</SelectItem>
                                            <SelectItem value="RV">RV</SelectItem>
                                            <SelectItem value="Motorsports">Motorsports</SelectItem>
                                            <SelectItem value="Maritime">Maritime</SelectItem>
                                            <SelectItem value="Association">Association</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <input type="hidden" name="default_type" :value="defaultType" />
                                    <InputError :message="errors.default_type" />
                                </Field>

                                <Field>
                                    <FieldLabel for="default_status">Status</FieldLabel>
                                    <Select v-model="defaultStatus" name="default_status">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="inactive">Inactive</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <input type="hidden" name="default_status" :value="defaultStatus" />
                                    <InputError :message="errors.default_status" />
                                </Field>

                                <Field>
                                    <FieldLabel for="default_rating">Rating</FieldLabel>
                                    <Select v-model="defaultRating" name="default_rating">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select rating" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="hot">Hot</SelectItem>
                                            <SelectItem value="warm">Warm</SelectItem>
                                            <SelectItem value="cold">Cold</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <input type="hidden" name="default_rating" :value="defaultRating" />
                                    <InputError :message="errors.default_rating" />
                                </Field>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Default consultants</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <input
                                    v-for="id in selectedConsultantIds"
                                    :key="id"
                                    type="hidden"
                                    name="default_user_ids[]"
                                    :value="id"
                                />
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" class="w-full justify-between">
                                            <span>Select consultants</span>
                                            <span class="text-xs text-slate-500">
                                                {{ selectedConsultantIds.length }}
                                            </span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent class="w-72">
                                        <div class="p-2">
                                            <Input
                                                v-model="consultantSearch"
                                                placeholder="Search consultants..."
                                            />
                                        </div>
                                        <div class="max-h-60 overflow-auto py-1">
                                            <DropdownMenuItem
                                                v-for="user in filteredConsultants"
                                                :key="user.id"
                                                @select.prevent="toggleConsultant(user.id, true)"
                                                @click="toggleConsultant(user.id, true)"
                                            >
                                                {{ user.name }}
                                            </DropdownMenuItem>
                                            <div
                                                v-if="filteredConsultants.length === 0"
                                                class="px-2 py-2 text-xs text-slate-500"
                                            >
                                                No consultants match that search.
                                            </div>
                                        </div>
                                    </DropdownMenuContent>
                                </DropdownMenu>

                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="user in selectedConsultants"
                                        :key="user.id"
                                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700"
                                    >
                                        {{ user.name }}
                                        <button
                                            type="button"
                                            class="text-slate-500 hover:text-slate-700"
                                            aria-label="Remove consultant"
                                            @click="toggleConsultant(user.id, false)"
                                        >
                                            ×
                                        </button>
                                    </span>
                                    <span
                                        v-if="selectedConsultants.length === 0"
                                        class="text-xs text-slate-500"
                                    >
                                        Importer is always attached. Add others to apply to every imported dealership.
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="col-span-full flex justify-end">
                    <Button type="submit" :disabled="processing">
                        <Upload class="mr-1.5 h-4 w-4" />
                        Preview Import
                    </Button>
                </div>
            </Form>
        </div>

        <!-- Step 2: preview -->
        <div v-if="preview" class="mt-8 space-y-5">
            <Separator />

            <Card>
                <CardHeader>
                    <CardTitle>Preview</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-6">
                        <div>
                            <div class="text-xs text-slate-500">Dealerships</div>
                            <div class="text-xl font-bold">{{ preview.summary.dealerships }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Auto-create</div>
                            <div class="text-xl font-bold text-blue-600">
                                {{ preview.summary.autoCreatedDealerships }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Stores</div>
                            <div class="text-xl font-bold">{{ preview.summary.stores }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Contacts</div>
                            <div class="text-xl font-bold">{{ preview.summary.contacts }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Errors</div>
                            <div class="text-xl font-bold text-red-600">{{ preview.summary.errors }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Will import</div>
                            <div class="text-xl font-bold text-green-600">{{ validRowCount }}</div>
                        </div>
                    </div>
                    <p
                        v-if="preview.summary.autoCreatedDealerships > 0"
                        class="mt-3 text-xs text-slate-600 dark:text-slate-400"
                    >
                        {{ preview.summary.autoCreatedDealerships }} dealership(s) will be auto-created
                        from contact references using the default type, status, and rating.
                    </p>

                    <div v-if="preview.parseErrors.length > 0" class="mt-4 rounded-md bg-red-50 p-3 text-sm">
                        <div class="font-semibold text-red-800">Parse errors</div>
                        <ul class="mt-1 list-disc pl-5 text-red-700">
                            <li v-for="err in preview.parseErrors" :key="err.line">
                                Line {{ err.line }}: {{ err.message }}
                            </li>
                        </ul>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="errorRows.length > 0">
                <CardHeader>
                    <CardTitle>Rows with errors ({{ errorRows.length }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-16">Line</TableHead>
                                <TableHead class="w-28">Type</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Errors</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="row in errorRows" :key="row.line">
                                <TableCell>{{ row.line }}</TableCell>
                                <TableCell class="capitalize">{{ row.rowType }}</TableCell>
                                <TableCell>{{ row.resolved.name ?? row.parentRef ?? '—' }}</TableCell>
                                <TableCell class="text-red-700">{{ formatErrors(row.errors) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <div class="flex items-center justify-end gap-3">
                <Link href="/dealerships/import">
                    <Button type="button" variant="outline">Re-upload</Button>
                </Link>
                <Form
                    :action="DealershipImportController.store.url()"
                    method="post"
                    v-slot="{ processing }"
                >
                    <input type="hidden" name="token" :value="preview.token" />
                    <Button type="submit" :disabled="processing || validRowCount === 0">
                        Confirm Import ({{ validRowCount }} rows)
                    </Button>
                </Form>
            </div>
        </div>
    </div>
</template>
