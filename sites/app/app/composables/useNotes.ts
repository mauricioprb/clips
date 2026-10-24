import { ExecutionMethod, ExecutionStatus } from 'appwrite';

export type Note = { id: string; title: string; createdAt: string };

export function useNotes() {
  const { functions } = useAppwrite();
  const { user } = useAuth();
  const notes = useState<Note[]>('notes', () => []);

  async function call<T>(method: ExecutionMethod, path = '/', body?: object): Promise<T> {
    const execution = await functions.createExecution({
      functionId: 'notes',
      method,
      xpath: path,
      body: body ? JSON.stringify(body) : undefined,
      headers: { 'content-type': 'application/json' },
    });

    if (execution.status !== ExecutionStatus.Completed) {
      throw new Error('O serviço de notas não respondeu. Tente de novo em instantes.');
    }

    let data: { error?: string } | null;
    try {
      data = execution.responseBody ? JSON.parse(execution.responseBody) : null;
    } catch {
      throw new Error('O serviço de notas respondeu de forma inesperada. Tente de novo.');
    }

    if (execution.responseStatusCode >= 400) {
      throw new Error(data?.error ?? 'Não foi possível concluir a operação.');
    }

    return data as T;
  }

  async function load() {
    const requestedBy = user.value?.$id;
    notes.value = [];
    const result = await call<{ notes: Note[] }>(ExecutionMethod.GET);
    if (user.value?.$id === requestedBy) {
      notes.value = result.notes;
    }
  }

  async function create(title: string) {
    await call(ExecutionMethod.POST, '/', { title });
    await load();
  }

  async function remove(id: string) {
    await call(ExecutionMethod.DELETE, `/${id}`);
    await load();
  }

  return { notes, load, create, remove };
}
