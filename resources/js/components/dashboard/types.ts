export interface QuietDealership {
    id: number;
    name: string;
    city: string | null;
    state: string | null;
    rating: string;
    ratingLabel: string;
    lastTouchAt: string | null;
    daysSinceTouch: number | null;
}

export interface TaskStats {
    incomplete: number;
    overdue: number;
    dueToday: number;
    completedThisWeek: number;
}

export interface BookSummary {
    total: number;
    hot: number;
    warm: number;
    cold: number;
}

export interface PipelineStage {
    stage: string;
    label: string;
    count: number;
    value: number;
}

export interface PipelineSummary {
    openValue: number;
    openCount: number;
    weightedValue: number;
    closingThisMonthCount: number;
    wonThisMonthCount: number;
    wonThisMonthValue: number;
    stages: PipelineStage[];
}

export interface ActivityEntry {
    id: number;
    details: string;
    date: string;
    category: string | null;
    author: string | null;
    dealership: { id: number; name: string } | null;
}

export interface ActivitySummary {
    thisWeek: number;
    lastWeek: number;
    recent: ActivityEntry[];
}
