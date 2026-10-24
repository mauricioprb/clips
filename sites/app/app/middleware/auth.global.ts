const guestRoutes = ['/login', '/signup'];
const serviceUnavailableStatus = 503;

export default defineNuxtRouteMiddleware(async (to) => {
  const { user, refresh } = useAuth();

  if (!user.value) {
    try {
      await refresh();
    } catch {
      return abortNavigation(
        createError({
          statusCode: serviceUnavailableStatus,
          statusMessage: 'Não foi possível falar com o servidor. Tente de novo em instantes.',
        }),
      );
    }
  }

  const isGuestRoute = guestRoutes.includes(to.path);

  if (!user.value && !isGuestRoute) {
    return navigateTo('/login');
  }

  if (user.value && isGuestRoute) {
    return navigateTo('/');
  }
});
