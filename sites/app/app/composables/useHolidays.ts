import { ExecutionMethod } from 'appwrite';

export type HolidayRecurrence = 'yearly' | 'once' | 'corpus_christi';

export type Holiday = {
  id: string;
  name: string;
  recurrence: HolidayRecurrence;
  date: string | null;
};

export type HolidayList = {
  year: number;
  holidays: Holiday[];
  calendar: { date: string; name: string }[];
};

export function useHolidays() {
  const { user } = useAuth();
  const list = useState<HolidayList | null>('holidays', () => null);

  async function load(year: number) {
    const requestedBy = user.value?.$id;
    const result = await callFunction<HolidayList>(
      'timesheet',
      ExecutionMethod.GET,
      `/holidays/${year}`,
    );

    if (user.value?.$id === requestedBy) {
      list.value = result;
    }
  }

  async function create(holiday: Omit<Holiday, 'id'>, year: number) {
    await callFunction('timesheet', ExecutionMethod.POST, '/holidays', holiday);
    await load(year);
  }

  async function remove(id: string, year: number) {
    await callFunction('timesheet', ExecutionMethod.DELETE, `/holidays/${id}`);
    await load(year);
  }

  return { list, load, create, remove };
}
