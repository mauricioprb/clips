const guestRoutes = ['/login', '/forgot-password', '/reset-password'];
const openRoutes = ['/invitation'];
const serviceUnavailableStatus = 503;

export default defineNuxtRouteMiddleware(async (to) => {
  if (openRoutes.includes(to.path)) {
    return;
  }

  const { user, access, refresh } = useAuth();

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
  const signedIn = user.value !== null && access.value.isMember;

  if (!signedIn) {
    return isGuestRoute ? undefined : navigateTo('/login');
  }

  if (isGuestRoute) {
    return navigateTo('/');
  }

  if (to.meta.adminOnly && !access.value.isAdmin) {
    return navigateTo('/');
  }
});
