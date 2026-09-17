const compactCurrency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    notation: 'compact',
    maximumFractionDigits: 1,
});

export function formatCompactCurrency(value: number): string {
    return compactCurrency.format(value);
}

/**
 * Human label for a Y-m-d date relative to today, e.g. "Today", "3d ago", "Mar 4".
 */
export function formatRelativeDay(date: string): string {
    const [year, month, day] = date.split('-').map(Number);
    const target = new Date(year, month - 1, day);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const days = Math.round((today.getTime() - target.getTime()) / 86_400_000);

    if (days === 0) {
        return 'Today';
    }
    if (days === 1) {
        return 'Yesterday';
    }
    if (days > 1 && days < 7) {
        return `${days}d ago`;
    }

    return target.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        ...(target.getFullYear() !== today.getFullYear() ? { year: 'numeric' } : {}),
    });
}
