export interface TaskUser {
    id: number;
    name: string;
}

export interface TaskDealership {
    id: number;
    name: string;
}

export interface TaskContact {
    id: number;
    name: string;
}

export interface Task {
    id: number;
    title: string;
    description: string | null;
    type: string;
    typeLabel: string;
    priority: string;
    priorityLabel: string;
    dueDate: string | null;
    completedAt: string | null;
    isCompleted: boolean;
    isOverdue: boolean;
    assignedTo: TaskUser;
    createdBy: TaskUser;
    dealership: TaskDealership | null;
    contact: TaskContact | null;
    dealershipId: number | null;
    contactId: number | null;
    userId: number;
    createdAt: string;
}

export interface FilterOption {
    value: string;
    label: string;
}
