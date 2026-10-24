import { Account, Client, Functions, Teams } from 'appwrite';

let services: { account: Account; functions: Functions; teams: Teams } | undefined;

export function useAppwrite() {
  if (!services) {
    const { public: config } = useRuntimeConfig();
    const client = new Client()
      .setEndpoint(config.appwriteEndpoint)
      .setProject(config.appwriteProjectId)
      .setLocale('pt-BR');

    services = {
      account: new Account(client),
      functions: new Functions(client),
      teams: new Teams(client),
    };
  }

  return services;
}
