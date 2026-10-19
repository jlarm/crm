export interface UserRole {
    id: number;
    name: string;
}

export interface UserListItem {
    id: number;
    name: string;
    email: string;
    title: string | null;
    phone: string | null;
    phoneExtension: string | null;
    timezone: string | null;
    createdAt: string | null;
    deletedAt: string | null;
    lastLoginAt: string | null;
    roles: UserRole[];
}
