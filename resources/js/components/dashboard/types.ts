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

export interface WeeklyDigestMove {
    id: number;
    opportunityId: number;
    name: string;
    dealership: { id: number; name: string };
    from: { value: string; label: string } | null;
    to: { value: string; label: string };
    direction: 'new' | 'advanced' | 'slipped' | 'won' | 'lost';
    changedAt: string;
    changedBy: string | null;
}

export interface WeeklyDigestQuietDeal {
    id: number;
    name: string;
    dealership: { id: number; name: string };
    stageLabel: string;
    estimatedValue: number;
    daysSinceActivity: number | null;
}

export interface WeeklyDigest {
    periodStart: string;
    periodEnd: string;
    counts: {
        newDeals: number;
        advanced: number;
        slipped: number;
        won: number;
        lost: number;
        wonValue: number;
        activitiesLogged: number;
        tasksCompleted: number;
        tasksDueThisWeek: number;
        tasksOverdue: number;
    };
    moves: WeeklyDigestMove[];
    quietDeals: WeeklyDigestQuietDeal[];
}

export interface EmailReplyAwaiting {
    id: number;
    threadId: number;
    dealership: { id: number; name: string };
    from: string;
    subject: string;
    snippet: string;
    receivedAt: string;
    unread: boolean;
}

export interface EmailRepliesAwaiting {
    total: number;
    items: EmailReplyAwaiting[];
}
