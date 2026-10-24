import { mockNuxtImport } from '@nuxt/test-utils/runtime';
import { AppwriteException, ExecutionStatus, type Models } from 'appwrite';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const { account, functions } = vi.hoisted(() => ({
  account: {
    get: vi.fn(),
    createEmailPasswordSession: vi.fn(),
    create: vi.fn(),
    deleteSession: vi.fn(),
  },
  functions: { createExecution: vi.fn() },
}));

mockNuxtImport('useAppwrite', () => () => ({ account, functions }));

function execution(
  responseStatusCode: number,
  responseBody: string,
  status = ExecutionStatus.Completed,
) {
  return { status, responseStatusCode, responseBody };
}

function signIn(userId: string) {
  useAuth().user.value = { $id: userId } as Models.User;
}

beforeEach(() => {
  vi.resetAllMocks();
  clearNuxtState(undefined, { reset: true });
});

describe('useAuth', () => {
  it('treats 401 as signed out', async () => {
    account.get.mockRejectedValue(new AppwriteException('Sem sessão.', 401, 'user_unauthorized'));
    const { user, refresh } = useAuth();

    await refresh();

    expect(user.value).toBeNull();
  });

  it('does not treat an unavailable server as signed out', async () => {
    account.get.mockRejectedValue(new AppwriteException('Falha de rede.', 0));

    await expect(useAuth().refresh()).rejects.toThrow();
  });

  it('clears private state on logout', async () => {
    signIn('ana');
    useNotes().notes.value = [{ id: 'nota', title: 'Privada', createdAt: '' }];
    account.deleteSession.mockResolvedValue({});

    await useAuth().logout();

    expect(useNotes().notes.value).toEqual([]);
    expect(useAuth().user.value).toBeNull();
  });

  it('keeps private state when logout fails', async () => {
    signIn('ana');
    account.deleteSession.mockRejectedValue(new AppwriteException('Falha de rede.', 0));

    await expect(useAuth().logout()).rejects.toThrow();

    expect(useAuth().user.value?.$id).toBe('ana');
  });
});

describe('useNotes', () => {
  it('loads the notes returned by the function', async () => {
    signIn('ana');
    const notes = [{ id: 'nota', title: 'Primeira', createdAt: '' }];
    functions.createExecution.mockResolvedValue(execution(200, JSON.stringify({ notes })));

    await useNotes().load();

    expect(useNotes().notes.value).toEqual(notes);
  });

  it('reports a failed execution', async () => {
    functions.createExecution.mockResolvedValue(execution(0, '', ExecutionStatus.Failed));

    await expect(useNotes().load()).rejects.toThrow('O serviço de notas não respondeu');
  });

  it('reports a response that is not JSON', async () => {
    functions.createExecution.mockResolvedValue(execution(200, '<html>'));

    await expect(useNotes().load()).rejects.toThrow('respondeu de forma inesperada');
  });

  it('shows the error message sent by the function', async () => {
    functions.createExecution.mockResolvedValue(
      execution(422, JSON.stringify({ error: 'Informe um título com até 255 caracteres.' })),
    );

    await expect(useNotes().create('')).rejects.toThrow(
      'Informe um título com até 255 caracteres.',
    );
  });

  it('ignores a response that arrives after the user changed', async () => {
    signIn('ana');
    let respond: (value: unknown) => void = () => {};
    functions.createExecution.mockReturnValue(new Promise((resolve) => (respond = resolve)));

    const loading = useNotes().load();
    signIn('bia');
    respond(
      execution(200, JSON.stringify({ notes: [{ id: 'nota', title: 'Da Ana', createdAt: '' }] })),
    );
    await loading;

    expect(useNotes().notes.value).toEqual([]);
  });
});
