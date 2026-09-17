import { clsx } from "clsx"
import { twMerge } from "tailwind-merge"
import type { ClassValue } from "clsx"

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

export function toUrl(url: string | { url: string }): string {
    return typeof url === 'string' ? url : url.url
}

const statusClasses: Record<string, string> = {
    active: 'bg-success/12 text-success',
    inactive: 'bg-muted text-muted-foreground',
};

const ratingClasses: Record<string, string> = {
    hot: 'bg-destructive/12 text-destructive',
    warm: 'bg-warning/15 text-warning',
    cold: 'bg-info/12 text-info',
};

const fallbackClass = 'bg-muted text-muted-foreground';

export function statusClass(status: string): string {
    return statusClasses[status] ?? fallbackClass;
}

export function ratingClass(rating: string): string {
    return ratingClasses[rating] ?? fallbackClass;
}
