export type EntrySource = 'manual' | 'schedule' | 'activity';

export type Priority = 'high' | 'medium' | 'low';

export type HolidayRecurrence = 'yearly' | 'once' | 'corpus_christi';

export interface AuthUser {
    id: string;
    name: string;
    email: string;
    isAdmin: boolean;
}

export type UserStatus = 'active' | 'invitation_pending' | 'blocked';

export interface ManagedUser {
    id: string;
    name: string;
    email: string;
    isAdmin: boolean;
    isSelf: boolean;
    status: UserStatus;
    invitationSentAt: string | null;
}

export interface TimeEntry {
    id: string;
    startMinute: number;
    endMinute: number;
    description: string;
    source: EntrySource;
}

export interface MonthDay {
    date: string;
    isWorkday: boolean;
    holiday: string | null;
    minutes: number;
    entries: TimeEntry[];
}

export interface WeeklySlot {
    id: string;
    weekday: number;
    startMinute: number;
    endMinute: number;
    description: string;
    validFrom: string | null;
    validUntil: string | null;
}

export interface DefaultActivity {
    id: string;
    description: string;
    priority: Priority;
}

export interface Holiday {
    id: string;
    name: string;
    recurrence: HolidayRecurrence;
    date: string | null;
}
