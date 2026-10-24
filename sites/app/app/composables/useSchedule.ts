import { ExecutionMethod } from 'appwrite';

export type WeeklySlot = {
  id: string;
  weekday: number;
  startMinute: number;
  endMinute: number;
  description: string;
  validFrom: string | null;
  validUntil: string | null;
};

export type WeeklySlotInput = Omit<WeeklySlot, 'id'>;

export function useSchedule() {
  const { user } = useAuth();
  const slots = useState<WeeklySlot[]>('weekly-slots', () => []);

  async function load() {
    const requestedBy = user.value?.$id;
    const result = await callFunction<{ slots: WeeklySlot[] }>(
      'timesheet',
      ExecutionMethod.GET,
      '/weekly-slots',
    );

    if (user.value?.$id === requestedBy) {
      slots.value = result.slots;
    }
  }

  async function save(slot: WeeklySlotInput, id?: string) {
    await callFunction(
      'timesheet',
      id ? ExecutionMethod.PUT : ExecutionMethod.POST,
      id ? `/weekly-slots/${id}` : '/weekly-slots',
      slot,
    );
    await load();
  }

  async function remove(id: string) {
    await callFunction('timesheet', ExecutionMethod.DELETE, `/weekly-slots/${id}`);
    await load();
  }

  return { slots, load, save, remove };
}
