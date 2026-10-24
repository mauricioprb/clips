import { Account, Client, Functions } from 'appwrite';

let services: { account: Account; functions: Functions } | undefined;

export function useAppwrite() {
  if (!services) {
    const { public: config } = useRuntimeConfig();
    const client = new Client()
      .setEndpoint(config.appwriteEndpoint)
      .setProject(config.appwriteProjectId);

    services = { account: new Account(client), functions: new Functions(client) };
  }

  return services;
}
