import { usePage } from '@inertiajs/vue3';
import {
    Building2,
    LayoutDashboard,
    ListChecks,
    Settings2,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import type { Component, ComputedRef } from 'vue';
import { computed } from 'vue';
import { toUrl } from '@/lib/utils';
import { dashboard } from '@/routes';
import dealerships from '@/routes/dealerships';
import dealershipImport from '@/routes/dealerships/import';
import sales from '@/routes/sales';
import { edit as appearanceEdit } from '@/routes/settings/appearance';
import { edit as profileEdit } from '@/routes/settings/profile';
import { edit as securityEdit } from '@/routes/settings/security';
import tasks from '@/routes/tasks';
import users from '@/routes/users';
import type { BreadcrumbItem } from '@/types';

export type ShellNavChild = {
    title: string;
    href: string;
};

export type ShellNavItem = {
    title: string;
    icon: Component;
    href: string;
    /** Path prefix that marks this item active, when broader than `href`. */
    match?: string;
    children?: ShellNavChild[];
};

export type ShellNavSection = {
    label: string;
    items: ShellNavItem[];
};

export type ShellCrumb = BreadcrumbItem & { icon?: Component };

type ShellPageProps = {
    auth: { roles?: string[] };
    dealership?: { id: number; name: string };
    user?: { id: number; name: string };
};

function pathOf(url: string): string {
    return new URL(url, 'http://localhost').pathname;
}

export function useShellNavigation(): {
    sections: ComputedRef<ShellNavSection[]>;
    currentPath: ComputedRef<string>;
    isActive: (item: ShellNavItem | ShellNavChild) => boolean;
    breadcrumbs: ComputedRef<ShellCrumb[]>;
} {
    const page = usePage<ShellPageProps>();

    const currentPath = computed(() => pathOf(page.url));

    const sections = computed<ShellNavSection[]>(() => {
        const workspace: ShellNavItem[] = [
            {
                title: 'Dashboard',
                icon: LayoutDashboard,
                href: toUrl(dashboard()),
            },
            {
                title: 'Dealerships',
                icon: Building2,
                href: `${toUrl(dashboard())}#dealerships`,
                match: '/dealerships',
                children: [
                    { title: 'All dealerships', href: `${toUrl(dashboard())}#dealerships` },
                    { title: 'New dealership', href: toUrl(dealerships.create()) },
                    { title: 'Import', href: toUrl(dealershipImport.create()) },
                ],
            },
            { title: 'Tasks', icon: ListChecks, href: toUrl(tasks.index()) },
            { title: 'Sales', icon: TrendingUp, href: toUrl(sales.index()) },
        ];

        const admin: ShellNavItem[] = [];

        if (page.props.auth?.roles?.includes('super_admin')) {
            admin.push({ title: 'Users', icon: Users, href: toUrl(users.index()) });
        }

        admin.push({
            title: 'Settings',
            icon: Settings2,
            href: toUrl(profileEdit()),
            match: '/settings',
            children: [
                { title: 'Profile', href: toUrl(profileEdit()) },
                { title: 'Security', href: toUrl(securityEdit()) },
                { title: 'Appearance', href: toUrl(appearanceEdit()) },
            ],
        });

        return [
            { label: 'Workspace', items: workspace },
            { label: 'Account', items: admin },
        ];
    });

    function isActive(item: ShellNavItem | ShellNavChild): boolean {
        const path = currentPath.value;
        const prefix = 'match' in item && item.match ? item.match : null;

        if (prefix) {
            return path === prefix || path.startsWith(`${prefix}/`);
        }

        const target = pathOf(item.href);

        return path === target || path.startsWith(`${target}/`);
    }

    const breadcrumbs = computed<ShellCrumb[]>(() => {
        const trail: ShellCrumb[] = [];
        const items = sections.value.flatMap((section) => section.items);
        const parent = items.find((item) => isActive(item));

        if (!parent) {
            return trail;
        }

        trail.push({ title: parent.title, href: parent.href, icon: parent.icon });

        const child = parent.children?.find(
            (candidate) => pathOf(candidate.href) === currentPath.value,
        );

        if (child) {
            trail.push({ title: child.title, href: child.href });
        } else if (parent.match === '/dealerships' && page.props.dealership?.name) {
            trail.push({ title: page.props.dealership.name, href: page.url });
        } else if (parent.title === 'Users' && currentPath.value !== pathOf(parent.href)) {
            trail.push({
                title: page.props.user?.name ?? (currentPath.value.endsWith('/create') ? 'New user' : 'Edit'),
                href: page.url,
            });
        }

        return trail;
    });

    return { sections, currentPath, isActive, breadcrumbs };
}
