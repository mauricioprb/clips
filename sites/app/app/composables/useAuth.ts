import { AppwriteException, Query, type Models } from 'appwrite';

const unauthorizedStatus = 401;
const notFoundStatus = 404;
export const teamId = 'clips';
export const adminRole = 'owner';

export function useAuth() {
  const user = useState<Models.User | null>('user', () => null);
  const access = useState<{ isMember: boolean; isAdmin: boolean }>('access', () => ({
    isMember: false,
    isAdmin: false,
  }));
  const { account, teams } = useAppwrite();

  function clearPrivateState() {
    clearNuxtState(undefined, { reset: true });
  }

  async function loadAccess(userId: string) {
    try {
      const { memberships } = await teams.listMemberships({
        teamId,
        queries: [Query.equal('userId', userId)],
      });
      const membership = memberships.find((item) => item.confirm);
      access.value = {
        isMember: membership !== undefined,
        isAdmin: membership?.roles.includes(adminRole) ?? false,
      };
    } catch (exception) {
      if (exception instanceof AppwriteException && exception.code === notFoundStatus) {
        access.value = { isMember: false, isAdmin: false };
        return;
      }
      throw exception;
    }
  }

  async function refresh() {
    try {
      user.value = await account.get();
    } catch (exception) {
      if (exception instanceof AppwriteException && exception.code === unauthorizedStatus) {
        user.value = null;
        access.value = { isMember: false, isAdmin: false };
        return;
      }
      throw exception;
    }

    await loadAccess(user.value.$id);
  }

  async function logout() {
    await account.deleteSession({ sessionId: 'current' });
    clearPrivateState();
  }

  async function login(email: string, password: string) {
    clearPrivateState();
    await account.createEmailPasswordSession({ email, password });
    await refresh();

    if (!access.value.isMember) {
      await logout();
      throw new Error('Esta conta não tem acesso ao Clips. Peça um convite à coordenação.');
    }
  }

  async function acceptInvitation(membershipId: string, userId: string, secret: string) {
    clearPrivateState();
    await teams.updateMembershipStatus({ teamId, membershipId, userId, secret });
    await refresh();
  }

  async function requestRecovery(email: string) {
    await account.createRecovery({ email, url: `${window.location.origin}/reset-password` });
  }

  async function resetPassword(userId: string, secret: string, password: string) {
    await account.updateRecovery({ userId, secret, password });
  }

  async function changePassword(password: string, oldPassword?: string) {
    await account.updatePassword({ password, oldPassword });
  }

  async function updateName(name: string) {
    user.value = await account.updateName({ name });
  }

  async function updateEmail(email: string, password: string) {
    user.value = await account.updateEmail({ email, password });
  }

  return {
    user,
    access,
    refresh,
    login,
    logout,
    acceptInvitation,
    requestRecovery,
    resetPassword,
    changePassword,
    updateName,
    updateEmail,
  };
}
