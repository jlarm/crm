export interface UserRole {
    id: number;
    name: string;
}

export interface UserListItem {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    timezone: string | null;
    createdAt: string | null;
    deletedAt: string | null;
    roles: UserRole[];
}
