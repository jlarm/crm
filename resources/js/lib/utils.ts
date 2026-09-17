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
    active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    inactive: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
};

const ratingClasses: Record<string, string> = {
    hot: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    warm: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    cold: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
};

const fallbackClass = 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';

export function statusClass(status: string): string {
    return statusClasses[status] ?? fallbackClass;
}

export function ratingClass(rating: string): string {
    return ratingClasses[rating] ?? fallbackClass;
}
