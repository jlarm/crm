export type DealershipShowTab = 'details' | 'stores' | 'contacts';

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
}
