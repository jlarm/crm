import type { Component } from 'vue'

export type BreadcrumbItem = {
    title: string
    href?: string
}

export type User = {
    id: number
    name: string
    email: string
    avatar?: string
    email_verified_at?: string | null
    created_at?: string
    updated_at?: string
}

export type NavItem = {
    title: string
    href: string | { url: string; method: string }
    icon?: Component
    label?: string
}

export type AppVariant = 'sidebar' | 'header'

export type Appearance = 'light' | 'dark' | 'system'

export type ResolvedAppearance = 'light' | 'dark'

export type TwoFactorConfigContent = {
    title: string
    description: string
    buttonText: string
}

export type SharedData = {
    name: string
    auth: {
        user: User
    }
}
