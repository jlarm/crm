export type DealershipShowTab = 'details' | 'stores' | 'contacts' | 'tasks' | 'opportunities' | 'activity';

export interface ActivityFeedItem {
    id: string;
    category: string;
    icon: string;
    title: string;
    description: string | null;
    actor: { id: number; name: string } | null;
    occurredAt: string;
}

export interface ActivityFeedMeta {
    currentPage: number;
    perPage: number;
    total: number;
    hasMore: boolean;
}

export interface User {
    id: number;
    name: string;
}

export interface Store {
    id: number;
    name: string;
    address: string | null;
    city: string | null;
    state: string | null;
    zipCode: string | null;
    phone: string | null;
    currentSolutionName: string | null;
    currentSolutionUse: string | null;
}

export interface Contact {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    position: string | null;
    linkedinLink: string | null;
    primaryContact: boolean;
}

export interface Activity {
    id: number;
    type: 'call' | 'note' | 'email';
    typeLabel: string;
    details: string;
    occurredAt: string | null;
    createdAt: string;
    user: { id: number; name: string };
}

export interface Opportunity {
    id: number;
    name: string;
    stage: string;
    stageLabel: string;
    estimatedValue: number;
    probability: number | null;
    expectedCloseDate: string | null;
    nextAction: string | null;
    activities: Activity[];
}

export interface Dealership {
    id: number;
    name: string;
    address: string | null;
    city: string;
    state: string;
    zipCode: string | null;
    phone: string | null;
    notes: string | null;
    currentSolutionName: string | null;
    currentSolutionUse: string | null;
    status: string;
    rating: string;
    stores: Store[];
    contacts: Contact[];
    users: User[];
    opportunities: Opportunity[];
}
