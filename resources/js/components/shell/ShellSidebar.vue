<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, ChevronsUpDown, PanelLeftClose, PanelLeftOpen } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import BrandMark from '@/components/shell/BrandMark.vue';
import ThemeToggle from '@/components/shell/ThemeToggle.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import type { ShellNavItem } from '@/composables/useShellNavigation';
import { useInitials } from '@/composables/useInitials';
import { useShellNavigation } from '@/composables/useShellNavigation';
import { dashboard } from '@/routes';
import type { User } from '@/types';

const props = defineProps<{
    collapsed?: boolean;
    /** Hide the collapse control, e.g. inside the mobile drawer. */
    hideCollapse?: boolean;
}>();

const emit = defineEmits<{
    (event: 'toggle-collapse'): void;
    (event: 'navigate'): void;
}>();

const page = usePage<{ auth: { user: User } }>();
const { sections, isActive, currentPath } = useShellNavigation();
const { getInitials } = useInitials();

const openGroups = ref<Record<string, boolean>>({});

function isGroupOpen(item: ShellNavItem): boolean {
    return openGroups.value[item.title] ?? isActive(item);
}

function toggleGroup(item: ShellNavItem): void {
    openGroups.value[item.title] = !isGroupOpen(item);
}

watch(currentPath, () => {
    openGroups.value = {};
});

function isChildActive(href: string): boolean {
    return !href.includes('#') && new URL(href, 'http://localhost').pathname === currentPath.value;
}

const itemBase =
    'group/nav flex h-10 w-full items-center gap-3 rounded-lg px-3 text-[14px] transition-colors focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:outline-none';

function itemClass(item: ShellNavItem): string {
    return isActive(item)
        ? `${itemBase} bg-sidebar-primary font-medium text-sidebar-primary-foreground shadow-sm`
        : `${itemBase} text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground`;
}
</script>

<template>
    <TooltipProvider :delay-duration="100">
        <div class="flex h-full flex-col">
            <div
                class="flex h-16 shrink-0 items-center border-b border-sidebar-border"
                :class="props.collapsed ? 'justify-center px-2' : 'justify-between px-5'"
            >
                <Link
                    v-if="!props.collapsed"
                    :href="dashboard()"
                    class="min-w-0 rounded-md focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:outline-none"
                    @click="emit('navigate')"
                >
                    <BrandMark />
                </Link>
                <button
                    v-if="!props.hideCollapse"
                    type="button"
                    class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-sidebar-accent hover:text-foreground focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:outline-none"
                    :aria-label="props.collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    @click="emit('toggle-collapse')"
                >
                    <PanelLeftOpen v-if="props.collapsed" class="size-4" />
                    <PanelLeftClose v-else class="size-4" />
                </button>
            </div>

            <nav
                class="flex-1 space-y-6 overflow-y-auto py-5"
                :class="props.collapsed ? 'px-2' : 'px-4'"
                aria-label="Main"
            >
                <div v-for="section in sections" :key="section.label">
                    <p
                        v-if="!props.collapsed"
                        class="mb-2 px-3 text-[11px] font-medium tracking-[0.08em] text-muted-foreground uppercase"
                    >
                        {{ section.label }}
                    </p>
                    <div v-else class="mx-auto mb-2 h-px w-6 bg-sidebar-border" />

                    <ul class="space-y-1">
                        <li v-for="item in section.items" :key="item.title">
                            <!-- Collapsed rail: icon links with tooltips -->
                            <Tooltip v-if="props.collapsed">
                                <TooltipTrigger as-child>
                                    <Link
                                        :href="item.href"
                                        :class="[itemClass(item), 'justify-center px-0']"
                                        :aria-label="item.title"
                                        :aria-current="isActive(item) ? 'page' : undefined"
                                    >
                                        <component :is="item.icon" class="size-[18px] shrink-0" />
                                    </Link>
                                </TooltipTrigger>
                                <TooltipContent side="right">{{ item.title }}</TooltipContent>
                            </Tooltip>

                            <!-- Expandable group -->
                            <template v-else-if="item.children">
                                <button
                                    type="button"
                                    :class="itemClass(item)"
                                    :aria-expanded="isGroupOpen(item)"
                                    @click="toggleGroup(item)"
                                >
                                    <component :is="item.icon" class="size-[18px] shrink-0" />
                                    <span class="flex-1 truncate text-left">{{ item.title }}</span>
                                    <ChevronDown
                                        class="size-4 shrink-0 opacity-70 transition-transform"
                                        :class="isGroupOpen(item) ? 'rotate-180' : ''"
                                    />
                                </button>
                                <ul
                                    v-show="isGroupOpen(item)"
                                    class="mt-1 space-y-0.5 pl-6"
                                >
                                    <li v-for="child in item.children" :key="child.title">
                                        <Link
                                            :href="child.href"
                                            class="flex h-9 items-center rounded-lg px-4 text-[14px] transition-colors focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:outline-none"
                                            :class="
                                                isChildActive(child.href)
                                                    ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground'
                                                    : 'text-muted-foreground hover:bg-sidebar-accent/60 hover:text-foreground'
                                            "
                                            :aria-current="isChildActive(child.href) ? 'page' : undefined"
                                            @click="emit('navigate')"
                                        >
                                            {{ child.title }}
                                        </Link>
                                    </li>
                                </ul>
                            </template>

                            <!-- Plain link -->
                            <Link
                                v-else
                                :href="item.href"
                                :class="itemClass(item)"
                                :aria-current="isActive(item) ? 'page' : undefined"
                                @click="emit('navigate')"
                            >
                                <component :is="item.icon" class="size-[18px] shrink-0" />
                                <span class="truncate">{{ item.title }}</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <div
                class="shrink-0 border-t border-sidebar-border"
                :class="props.collapsed ? 'p-2' : 'p-4'"
            >
                <div
                    :class="
                        props.collapsed
                            ? ''
                            : 'space-y-3 rounded-xl border border-sidebar-border bg-card p-3'
                    "
                >
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button
                                type="button"
                                class="flex w-full items-center gap-2.5 rounded-lg text-left transition-colors hover:bg-sidebar-accent focus-visible:ring-2 focus-visible:ring-sidebar-ring focus-visible:outline-none data-[state=open]:bg-sidebar-accent"
                                :class="props.collapsed ? 'justify-center p-1.5' : 'p-1'"
                                data-test="sidebar-menu-button"
                            >
                                <Avatar v-if="props.collapsed" class="size-8 rounded-lg">
                                    <AvatarImage
                                        v-if="page.props.auth.user.avatar"
                                        :src="page.props.auth.user.avatar"
                                        :alt="page.props.auth.user.name"
                                    />
                                    <AvatarFallback class="rounded-lg text-xs">
                                        {{ getInitials(page.props.auth.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <UserInfo v-else :user="page.props.auth.user" :show-email="true" />
                                <ChevronsUpDown
                                    v-if="!props.collapsed"
                                    class="ml-auto size-4 shrink-0 text-muted-foreground"
                                />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            class="min-w-60 rounded-lg"
                            :side="props.collapsed ? 'right' : 'top'"
                            align="end"
                            :side-offset="8"
                        >
                            <UserMenuContent :user="page.props.auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <ThemeToggle v-if="!props.collapsed" />
                    <div v-else class="mt-2 flex justify-center">
                        <ThemeToggle cycle />
                    </div>
                </div>
            </div>
        </div>
    </TooltipProvider>
</template>
