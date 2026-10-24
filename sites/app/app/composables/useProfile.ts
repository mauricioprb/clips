import { ExecutionMethod } from 'appwrite';

export type ScholarshipProfile = {
  advisorName: string | null;
  scholarshipName: string | null;
  laboratories: string[];
  weeklyWorkloadMinutes: number;
};

export function useProfile() {
  const { user } = useAuth();
  const profile = useState<ScholarshipProfile | null>('profile', () => null);

  async function load() {
    const requestedBy = user.value?.$id;
    const result = await callFunction<ScholarshipProfile>(
      'timesheet',
      ExecutionMethod.GET,
      '/profile',
    );

    if (user.value?.$id === requestedBy) {
      profile.value = result;
    }
  }

  async function save(changes: ScholarshipProfile) {
    profile.value = await callFunction<ScholarshipProfile>(
      'timesheet',
      ExecutionMethod.PUT,
      '/profile',
      changes,
    );
  }

  return { profile, load, save };
}
