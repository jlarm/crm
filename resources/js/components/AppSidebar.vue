<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BarChart2, CheckSquare, LayoutGrid, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import sales from '@/routes/sales';
import tasks from '@/routes/tasks';
import type { NavItem } from '@/types';

const page = usePage<{ auth: { roles: string[] } }>();

const canManageUsers = computed(
    () => page.props.auth?.roles?.includes('super_admin') ?? false,
);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Sales',
            href: sales.index(),
            icon: BarChart2,
        },
        {
            title: 'Tasks',
            href: tasks.index(),
            icon: CheckSquare,
        },
    ];

    if (canManageUsers.value) {
        items.push({
            title: 'Users',
            href: '/users',
            icon: Users,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
