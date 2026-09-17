import { Button } from '@/components/ui/button';
import { ratingClass, statusClass } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { ArrowDown, ArrowUp, ArrowUpDown, ChevronRight } from 'lucide-vue-next';
import { h } from 'vue';

export interface Dealership {
    id: number;
    name: string;
    city: string;
    state: string;
    type: string;
    status: string;
    statusLabel: string;
    rating: string;
    ratingLabel: string;
    openTasksCount: number;
}

type SortState = () => { column: string; direction: 'asc' | 'desc' };

function sortHeader(label: string, column: string, onSort: (column: string) => void, sortState?: SortState) {
    return () => {
        const state = sortState?.();
        const isSorted = state?.column === column;
        const icon = !isSorted ? ArrowUpDown : state?.direction === 'desc' ? ArrowDown : ArrowUp;

        return h(
            'button',
            {
                type: 'button',
                class: `inline-flex items-center gap-1.5 uppercase tracking-[0.06em] transition-colors hover:text-foreground ${isSorted ? 'text-foreground' : ''}`,
                'aria-label': `Sort by ${label}`,
                onClick: () => onSort(column),
            },
            [label, h(icon, { class: `size-3.5 ${isSorted ? '' : 'opacity-50'}` })],
        );
    };
}

function pill(label: string, tone: string) {
    return h(
        'span',
        {
            class: `inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium ${tone}`,
        },
        [h('span', { class: 'size-1.5 rounded-full bg-current' }), label],
    );
}

export function createColumns(
    onSort: (column: string) => void,
    sortState?: SortState,
): ColumnDef<Dealership>[] {
    return [
        {
            accessorKey: 'name',
            size: 320,
            header: sortHeader('Name', 'name', onSort, sortState),
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
                                class: 'ml-2 inline-flex items-center rounded-md border border-brand/30 bg-brand/10 px-1.5 py-px text-[11px] font-medium text-brand tabular-nums',
                                title: `${dealership.openTasksCount} open task${dealership.openTasksCount === 1 ? '' : 's'}`,
                            },
                            String(dealership.openTasksCount),
                        ),
                    );
                }

                return h('div', { class: 'flex items-center font-medium text-foreground' }, children);
            },
        },
        {
            accessorKey: 'city',
            size: 150,
            header: sortHeader('City', 'city', onSort, sortState),
            cell: ({ row }) => {
                return h('div', { class: 'text-muted-foreground' }, row.getValue('city'));
            },
        },
        {
            accessorKey: 'state',
            size: 100,
            header: sortHeader('State', 'state', onSort, sortState),
            cell: ({ row }) => {
                return h('div', { class: 'text-muted-foreground' }, row.getValue('state'));
            },
        },
        {
            accessorKey: 'type',
            size: 140,
            header: 'Type',
            cell: ({ row }) => {
                const type = row.original.type;

                return type
                    ? h('div', {}, type)
                    : h('div', { class: 'text-muted-foreground' }, '—');
            },
        },
        {
            accessorKey: 'status',
            size: 150,
            header: sortHeader('Status', 'status', onSort, sortState),
            cell: ({ row }) => {
                const dealership = row.original;
                return pill(dealership.statusLabel, statusClass(dealership.status));
            },
        },
        {
            accessorKey: 'rating',
            size: 150,
            header: sortHeader('Rating', 'rating', onSort, sortState),
            cell: ({ row }) => {
                const dealership = row.original;
                return pill(dealership.ratingLabel, ratingClass(dealership.rating));
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
