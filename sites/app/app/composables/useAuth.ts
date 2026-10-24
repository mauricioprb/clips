import { AppwriteException, ID, type Models } from 'appwrite';

const unauthorizedStatus = 401;

export function useAuth() {
  const user = useState<Models.User | null>('user', () => null);
  const { account } = useAppwrite();

  function clearPrivateState() {
    clearNuxtState(undefined, { reset: true });
  }

  async function refresh() {
    try {
      user.value = await account.get();
    } catch (exception) {
      if (exception instanceof AppwriteException && exception.code === unauthorizedStatus) {
        user.value = null;
        return;
      }
      throw exception;
    }
  }

  async function login(email: string, password: string) {
    clearPrivateState();
    await account.createEmailPasswordSession({ email, password });
    await refresh();
  }

  async function signup(name: string, email: string, password: string) {
    await account.create({ userId: ID.unique(), email, password, name });
    await login(email, password);
  }

  async function logout() {
    await account.deleteSession({ sessionId: 'current' });
    clearPrivateState();
  }

  return { user, refresh, login, signup, logout };
}
