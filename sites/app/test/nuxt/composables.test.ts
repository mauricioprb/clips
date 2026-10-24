import { mockNuxtImport } from '@nuxt/test-utils/runtime';
import { AppwriteException, ExecutionStatus, type Models } from 'appwrite';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import type { MonthSummary } from '~/composables/useMonth';

const { account, functions, teams } = vi.hoisted(() => ({
  account: {
    get: vi.fn(),
    createEmailPasswordSession: vi.fn(),
    deleteSession: vi.fn(),
  },
  functions: { createExecution: vi.fn() },
  teams: { listMemberships: vi.fn() },
}));

mockNuxtImport('useAppwrite', () => () => ({ account, functions, teams }));

function execution(
  responseStatusCode: number,
  responseBody: string,
  status = ExecutionStatus.Completed,
) {
  return { status, responseStatusCode, responseBody };
}

function membership(roles: string[], confirm = true) {
  return { memberships: [{ confirm, roles }] };
}

function signIn(userId: string) {
  useAuth().user.value = { $id: userId } as Models.User;
}

function monthOf(month: string): MonthSummary {
  return {
    month,
    days: [],
    loggedMinutes: 0,
    workdayMinutes: 0,
    targetMinutes: 0,
    dailyTargetMinutes: 240,
    setup: { profile: true, schedule: true, activities: true },
    suggestions: [],
  };
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

  it('reads the admin role from the team membership', async () => {
    account.get.mockResolvedValue({ $id: 'ana' });
    teams.listMemberships.mockResolvedValue(membership(['owner']));

    await useAuth().refresh();

    expect(useAuth().access.value).toEqual({ isMember: true, isAdmin: true });
  });

  it('does not count a pending invitation as access', async () => {
    account.get.mockResolvedValue({ $id: 'ana' });
    teams.listMemberships.mockResolvedValue(membership([], false));

    await useAuth().refresh();

    expect(useAuth().access.value).toEqual({ isMember: false, isAdmin: false });
  });

  it('signs out an account outside the team right after login', async () => {
    account.createEmailPasswordSession.mockResolvedValue({});
    account.get.mockResolvedValue({ $id: 'intrusa' });
    teams.listMemberships.mockRejectedValue(
      new AppwriteException('Equipe não encontrada.', 404, 'team_not_found'),
    );
    account.deleteSession.mockResolvedValue({});

    await expect(useAuth().login('intrusa@example.com', 'senha-de-teste')).rejects.toThrow(
      'não tem acesso ao Clips',
    );

    expect(account.deleteSession).toHaveBeenCalledWith({ sessionId: 'current' });
  });

  it('clears private state on logout', async () => {
    signIn('ana');
    useMonth().summary.value = monthOf('2026-03');
    account.deleteSession.mockResolvedValue({});

    await useAuth().logout();

    expect(useMonth().summary.value).toBeNull();
    expect(useAuth().user.value).toBeNull();
  });

  it('keeps private state when logout fails', async () => {
    signIn('ana');
    account.deleteSession.mockRejectedValue(new AppwriteException('Falha de rede.', 0));

    await expect(useAuth().logout()).rejects.toThrow();

    expect(useAuth().user.value?.$id).toBe('ana');
  });
});

describe('useMonth', () => {
  it('loads the month returned by the function', async () => {
    signIn('ana');
    functions.createExecution.mockResolvedValue(execution(200, JSON.stringify(monthOf('2026-03'))));

    await useMonth().load('2026-03');

    expect(useMonth().summary.value?.month).toBe('2026-03');
    expect(functions.createExecution).toHaveBeenCalledWith(
      expect.objectContaining({ functionId: 'timesheet', xpath: '/months/2026-03' }),
    );
  });

  it('reports a failed execution', async () => {
    functions.createExecution.mockResolvedValue(execution(0, '', ExecutionStatus.Failed));

    await expect(useMonth().load('2026-03')).rejects.toThrow('O Clips não respondeu');
  });

  it('reports a response that is not JSON', async () => {
    functions.createExecution.mockResolvedValue(execution(200, '<html>'));

    await expect(useMonth().load('2026-03')).rejects.toThrow('respondeu de forma inesperada');
  });

  it('shows the error message sent by the function', async () => {
    functions.createExecution.mockResolvedValue(
      execution(
        422,
        JSON.stringify({ error: 'Este horário se sobrepõe a outro lançamento do dia.' }),
      ),
    );

    await expect(
      useMonth().saveEntry({
        date: '2026-03-02',
        startMinute: 480,
        endMinute: 540,
        description: 'Reunião',
      }),
    ).rejects.toThrow('Este horário se sobrepõe a outro lançamento do dia.');
  });

  it('ignores a response that arrives after the user changed', async () => {
    signIn('ana');
    let respond: (value: unknown) => void = () => {};
    functions.createExecution.mockReturnValue(new Promise((resolve) => (respond = resolve)));

    const loading = useMonth().load('2026-03');
    signIn('bia');
    respond(execution(200, JSON.stringify(monthOf('2026-03'))));
    await loading;

    expect(useMonth().summary.value).toBeNull();
  });

  it('ignores a month that arrives after another month was requested', async () => {
    signIn('ana');
    const responses: ((value: unknown) => void)[] = [];
    functions.createExecution.mockImplementation(
      () => new Promise((resolve) => responses.push(resolve)),
    );

    const march = useMonth().load('2026-03');
    const april = useMonth().load('2026-04');
    responses[1]?.(execution(200, JSON.stringify(monthOf('2026-04'))));
    responses[0]?.(execution(200, JSON.stringify(monthOf('2026-03'))));
    await Promise.all([march, april]);

    expect(useMonth().summary.value?.month).toBe('2026-04');
  });
});
