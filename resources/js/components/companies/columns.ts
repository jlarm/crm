import { Button } from '@/components/ui/button';
import { ratingClass, statusClass } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown, ChevronRight } from 'lucide-vue-next';
import { h } from 'vue';

export interface Dealership {
    id: number;
    name: string;
    city: string;
    state: string;
    status: string;
    statusLabel: string;
    rating: string;
    ratingLabel: string;
    openTasksCount: number;
}

export function createColumns(
    onSort: (column: string) => void,
): ColumnDef<Dealership>[] {
    return [
        {
            accessorKey: 'name',
            size: 400,
            header: () => {
                return h(
                    Button,
                    {
                        variant: 'ghost',
                        class: 'h-auto p-0 hover:bg-transparent font-medium',
                        onClick: () => onSort('name'),
                    },
                    () => [
                        'Name',
                        h(ArrowUpDown, { class: 'ml-2 h-4 w-4 opacity-50' }),
                    ],
                );
            },
            cell: ({ row }) => {
                const dealership = row.original;
                const children: ReturnType<typeof h>[] = [
                    h('span', {}, dealership.name),
                ];

                if (dealership.openTasksCount > 0) {
                    children.push(
                        h(
                            'span',
                            {
                                class: 'ml-2 inline-flex items-center rounded-full bg-orange-100 px-1.5 py-0.5 text-xs font-medium text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                title: `${dealership.openTasksCount} open task${dealership.openTasksCount === 1 ? '' : 's'}`,
                            },
                            String(dealership.openTasksCount),
                        ),
                    );
                }

                return h('div', { class: 'flex items-center font-medium' }, children);
            },
        },
        {
            accessorKey: 'city',
            size: 150,
            header: 'City',
            cell: ({ row }) => {
                return h('div', {}, row.getValue('city'));
            },
        },
        {
            accessorKey: 'state',
            size: 100,
            header: 'State',
            cell: ({ row }) => {
                return h('div', {}, row.getValue('state'));
            },
        },
        {
            accessorKey: 'status',
            size: 150,
            header: () => {
                return h(
                    Button,
                    {
                        variant: 'ghost',
                        class: 'h-auto p-0 hover:bg-transparent font-medium',
                        onClick: () => onSort('status'),
                    },
                    () => [
                        'Status',
                        h(ArrowUpDown, { class: 'ml-2 h-4 w-4 opacity-50' }),
                    ],
                );
            },
            cell: ({ row }) => {
                const dealership = row.original;
                return h(
                    'span',
                    {
                        class: `inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${statusClass(dealership.status)}`,
                    },
                    dealership.statusLabel,
                );
            },
        },
        {
            accessorKey: 'rating',
            size: 150,
            header: () => {
                return h(
                    Button,
                    {
                        variant: 'ghost',
                        class: 'h-auto p-0 hover:bg-transparent font-medium',
                        onClick: () => onSort('rating'),
                    },
                    () => [
                        'Rating',
                        h(ArrowUpDown, { class: 'ml-2 h-4 w-4 opacity-50' }),
                    ],
                );
            },
            cell: ({ row }) => {
                const dealership = row.original;
                return h(
                    'span',
                    {
                        class: `inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${ratingClass(dealership.rating)}`,
                    },
                    dealership.ratingLabel,
                );
            },
        },
        {
            accessorKey: 'actions',
            size: 56,
            header: '',
            cell: ({ row }) => {
                const dealership = row.original;
                return h(
                    Link,
                    {
                        href: `/dealerships/${dealership.id}`,
                        class: 'inline-flex',
                        'aria-label': `View ${dealership.name}`,
                    },
                    () =>
                        h(
                            Button,
                            { variant: 'ghost', size: 'icon-sm', as: 'span' },
                            () => h(ChevronRight, { class: 'h-4 w-4' }),
                        ),
                );
            },
        },
    ];
}
