export type DealershipShowTab = 'details' | 'stores' | 'contacts' | 'tasks' | 'opportunities' | 'emails' | 'activity';

export interface ActivityFeedItem {
    id: string;
    category: string;
    icon: string;
    title: string;
    description: string | null;
    actor: { id: number; name: string } | null;
    occurredAt: string;
    /** Back-dated entries carry a date but no meaningful time. */
    dateOnly?: boolean;
    /** Set on activities logged by hand, which can be deleted. */
    progressId?: number;
}

export interface ProgressCategory {
    id: number;
    name: string;
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
    website: string | null;
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

export interface CallNoteAnalysis {
    summary: string;
    tasks: {
        title: string;
        type: string;
        priority: string;
        dueDate: string | null;
        contactId: number | null;
    }[];
    stage: { value: string; label: string; reason: string } | null;
    rating: { value: string; label: string; reason: string } | null;
    newContacts: { name: string; position: string; email: string; phone: string }[];
}

export type EmailStatusTone = 'success' | 'neutral' | 'warning' | 'danger';

export interface EmailAttachment {
    id: number;
    filename: string;
    size: number;
    url: string;
}

export interface EmailEvent {
    id: number;
    event: string;
    severity: string | null;
    description: string | null;
    occurredAt: string;
}

export interface DealershipEmail {
    id: number;
    parentId: number | null;
    direction: 'outbound' | 'inbound';
    from: { email: string; name: string | null };
    to: string;
    contact: { id: number; name: string } | null;
    sentBy: { id: number; name: string } | null;
    subject: string;
    body: string;
    /** Sanitized HTML of an email sent from the CRM; null for replies and older plain-text emails. */
    bodyHtml: string | null;
    status: string;
    statusLabel: string;
    statusTone: EmailStatusTone;
    statusReason: string | null;
    statusDetail: string | null;
    sentAt: string | null;
    deliveredAt: string | null;
    openedAt: string | null;
    failedAt: string | null;
    createdAt: string;
    attachments: EmailAttachment[];
    events: EmailEvent[];
}

/** The email that started a conversation, then every reply on both sides, oldest first. */
export type EmailThread = DealershipEmail[];
