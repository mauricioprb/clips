import { ExecutionMethod } from 'appwrite';

export type MemberStatus = 'active' | 'invitation_pending' | 'blocked';

export type Member = {
  userId: string;
  membershipId: string;
  name: string;
  email: string;
  isAdmin: boolean;
  isSelf: boolean;
  status: MemberStatus;
  invitedAt: string;
};

export function useMembers() {
  const { user } = useAuth();
  const { teams } = useAppwrite();
  const members = useState<Member[]>('members', () => []);

  function invitationUrl() {
    return `${window.location.origin}/invitation`;
  }

  async function load() {
    const requestedBy = user.value?.$id;
    const result = await callFunction<{ members: Member[] }>('members', ExecutionMethod.GET, '/');

    if (user.value?.$id === requestedBy) {
      members.value = result.members;
    }
  }

  async function invite(name: string, email: string, isAdmin: boolean) {
    await teams.createMembership({
      teamId,
      roles: isAdmin ? [adminRole] : [],
      email,
      name,
      url: invitationUrl(),
    });
    await load();
  }

  async function resendInvitation(member: Member) {
    await teams.createMembership({
      teamId,
      roles: member.isAdmin ? [adminRole] : [],
      email: member.email,
      name: member.name,
      url: invitationUrl(),
    });
    await load();
  }

  async function setAdmin(member: Member, isAdmin: boolean) {
    await teams.updateMembership({
      teamId,
      membershipId: member.membershipId,
      roles: isAdmin ? [adminRole] : [],
    });
    await load();
  }

  async function setActive(member: Member, active: boolean) {
    await callFunction('members', ExecutionMethod.PATCH, `/${member.userId}`, { active });
    await load();
  }

  async function deleteOwnAccount(email: string) {
    await callFunction('members', ExecutionMethod.DELETE, '/me', { email });
    clearNuxtState(undefined, { reset: true });
  }

  return { members, load, invite, resendInvitation, setAdmin, setActive, deleteOwnAccount };
}
