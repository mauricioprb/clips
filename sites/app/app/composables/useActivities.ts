import { ExecutionMethod } from 'appwrite';

export type Priority = 'high' | 'medium' | 'low';

export type DefaultActivity = { id: string; description: string; priority: Priority };

export function useActivities() {
  const { user } = useAuth();
  const activities = useState<DefaultActivity[]>('default-activities', () => []);

  async function load() {
    const requestedBy = user.value?.$id;
    const result = await callFunction<{ activities: DefaultActivity[] }>(
      'timesheet',
      ExecutionMethod.GET,
      '/default-activities',
    );

    if (user.value?.$id === requestedBy) {
      activities.value = result.activities;
    }
  }

  async function save(activity: Omit<DefaultActivity, 'id'>, id?: string) {
    await callFunction(
      'timesheet',
      id ? ExecutionMethod.PUT : ExecutionMethod.POST,
      id ? `/default-activities/${id}` : '/default-activities',
      activity,
    );
    await load();
  }

  async function remove(id: string) {
    await callFunction('timesheet', ExecutionMethod.DELETE, `/default-activities/${id}`);
    await load();
  }

  return { activities, load, save, remove };
}
