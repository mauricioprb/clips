import { ExecutionMethod } from 'appwrite';

export type EntrySource = 'manual' | 'schedule' | 'activity';

export type TimeEntry = {
  id: string;
  date: string;
  startMinute: number;
  endMinute: number;
  description: string;
  source: EntrySource;
};

export type EntryInput = Pick<TimeEntry, 'date' | 'startMinute' | 'endMinute' | 'description'>;

export type MonthDay = {
  date: string;
  isWorkday: boolean;
  holiday: string | null;
  minutes: number;
  entries: TimeEntry[];
};

export type MonthSummary = {
  month: string;
  days: MonthDay[];
  loggedMinutes: number;
  workdayMinutes: number;
  targetMinutes: number;
  dailyTargetMinutes: number;
  setup: { profile: boolean; schedule: boolean; activities: boolean };
  suggestions: string[];
};

export function useMonth() {
  const { user } = useAuth();
  const summary = useState<MonthSummary | null>('month-summary', () => null);
  const requestedMonth = useState<string | null>('month-requested', () => null);

  async function load(month: string) {
    const requestedBy = user.value?.$id;
    requestedMonth.value = month;
    const result = await callFunction<MonthSummary>(
      'timesheet',
      ExecutionMethod.GET,
      `/months/${month}`,
    );

    if (user.value?.$id === requestedBy && requestedMonth.value === month) {
      summary.value = result;
    }
  }

  async function saveEntry(entry: EntryInput, id?: string) {
    await callFunction(
      'timesheet',
      id ? ExecutionMethod.PUT : ExecutionMethod.POST,
      id ? `/entries/${id}` : '/entries',
      entry,
    );
    await load(entry.date.slice(0, 7));
  }

  async function removeEntry(entry: TimeEntry) {
    await callFunction('timesheet', ExecutionMethod.DELETE, `/entries/${entry.id}`);
    await load(entry.date.slice(0, 7));
  }

  async function fill(month: string): Promise<number> {
    const { created } = await callFunction<{ created: number }>(
      'timesheet',
      ExecutionMethod.POST,
      `/months/${month}/fill`,
    );
    await load(month);

    return created;
  }

  async function downloadReport(month: string) {
    const { fileName, content } = await callFunction<{ fileName: string; content: string }>(
      'timesheet',
      ExecutionMethod.GET,
      `/months/${month}/report`,
    );
    const bytes = Uint8Array.from(atob(content), (character) => character.charCodeAt(0));
    const url = URL.createObjectURL(new Blob([bytes], { type: 'application/pdf' }));
    const link = document.createElement('a');
    link.href = url;
    link.download = fileName;
    link.click();
    URL.revokeObjectURL(url);
  }

  return { summary, load, saveEntry, removeEntry, fill, downloadReport };
}
