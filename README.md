# Exemplo

## O que é

Aplicação de referência. Qualquer pessoa cria uma conta, entra e mantém uma lista de notas que só ela enxerga. Serve de base para o repositório-modelo dos clientes.

- Usuários: pessoas com conta própria, criada por cadastro aberto.
- Contato responsável: equipe técnica da fábrica.

## Estrutura

- `appwrite.config.json`: projeto Appwrite (banco, tabelas, índices, buckets, functions e sites).
- `functions/notes/`: API das notas em PHP 8.4, com testes Pest.
- `functions/user-cleanup/`: apaga os dados de um usuário quando a conta dele é apagada.
- `sites/app/`: site Nuxt 4 em modo SPA, com testes Vitest.
- `docs/dados.md`: dicionário de dados.
- `docs/decisions/`: decisões deste projeto (ADR no formato MADR), a partir de `modelo.md`.
- `AGENTS.md`: regras para agentes de IA. Skills ficam em `.agents/skills`.

## Pré-requisitos

- Appwrite 2.6.1 rodando (local ou homologação) e o CLI do Appwrite 28.9.0, com `appwrite login --endpoint <endpoint>/v1`.
- PHP 8.4 ou superior com Composer, Node.js 24 (LTS) com npm. O `.nvmrc` do site fixa a versão.
- Git com suporte a links simbólicos (`.claude/skills` aponta para `.agents/skills`).

## Primeira execução

Primeira vez num projeto da fábrica? Siga o [guia do desenvolvedor](https://docs.overclk.tech): do computador vazio ao app publicado.

Se o projeto já existe e você só vai rodar na sua máquina:

```bash
for function in functions/*/; do (cd "$function" && composer install); done
cd sites/app && cp .env.example .env && npm ci
```

Preencha `NUXT_PUBLIC_APPWRITE_PROJECT_ID` no `sites/app/.env`.

## Publicar

```bash
appwrite push table --all --force
appwrite push function --all --activate --force
appwrite push site --site-id app --activate --with-variables --force
```

A ordem importa: primeiro o esquema, depois as functions, por último o site.

O `appwrite.config.json` aponta para o projeto de desenvolvimento. Os IDs do banco, das tabelas e das functions são iguais em todos os ambientes; muda só o servidor e o projeto. Para publicar em outro ambiente, as variáveis do CLI têm prioridade sobre o arquivo:

```bash
export APPWRITE_ENDPOINT=https://<servidor-de-homologacao>/v1
export APPWRITE_PROJECT_ID=exemplo-hml
```

O `sites/app/.env` precisa ter os valores do mesmo ambiente antes do `push site`. No CI, o pipeline gera esse arquivo a partir dos segredos do ambiente. Os valores `NUXT_PUBLIC_*` ficam visíveis no navegador, então nunca guardam segredo.

## Componentes de interface

A FUSE UI é usada somente pelo registry/CLI: o fonte dos componentes é copiado para `sites/app/app/ui/`, os estilos para `app/assets/css/`, os utilitários para `app/lib/` e os composables para `app/composables/`. O aplicativo não depende de `@overclock/fuse-ui` em execução.

Login e cadastro usam adaptações locais dos componentes de autenticação. A área de notas usa AppShellStacked, PageHeader e os componentes copiados. O plugin `fuse-locale` seleciona português do Brasil. O cabeçalho mantém Logoipsum, avatar e seletor de tema, sem item de navegação redundante.

O seletor oferece claro, escuro e preferência do sistema, persistidos no navegador. A paleta de `app/assets/css/theme.css` vem do gerador da FUSE, com marca `#a4472f`, neutros `trail-print` e raio `0.5rem`. `main.css` importa os estilos de base e depois as personalizações.

O CLI vem do pacote npm `@overclock/fuse-ui`, fixado em `devDependencies` (hoje 0.4.0), e usa o `components.json`, que aponta para `app/`. Dentro de `sites/app`:

```bash
npx fuse-ui list
npx fuse-ui add select
npx fuse-ui diff
```

O `add` copia o componente e as dependências dele e preserva arquivos que já existem. O `diff` mostra o que mudou entre a cópia do projeto e o registry da versão instalada; diferenças só de formatação vêm do Prettier do projeto. Para atualizar, suba a versão do pacote, rode o `diff`, revise e substitua com `npx fuse-ui add <nome> --overwrite` só o que não foi personalizado. Hoje são personalizados `AuthLogin`, `AuthSignup`, `AuthCard`, `AppShellStacked` e `Label`, que não mostra asterisco em campo obrigatório.

A licença está em `sites/app/FUSE-UI-LICENSE`, e a decisão em [FUSE UI com fonte local](docs/decisions/0001-fuse-ui-com-fonte-local.md).

## Verificações

```bash
cd functions/notes
composer lint
composer test

cd functions/user-cleanup
composer lint

cd sites/app
npm run lint
npm run format:check
npm run typecheck
npm test
```

Ao commitar, um hook (Husky e lint-staged) formata os arquivos alterados com Pint, ESLint e Prettier e roda o typecheck e os testes do site. O CI (`.github/workflows/ci.yml`) repete as verificações em todo pull request e push na `main`, e confere o padrão de agentes. No VS Code, aceite as extensões recomendadas: o editor formata ao salvar.

O `composer test` roda testes de integração contra um projeto Appwrite descartável, nunca contra produção. Ele precisa de três variáveis. A chave é efêmera (no máximo uma hora) e é criada com a sua sessão do CLI:

```bash
export APPWRITE_TEST_ENDPOINT=http://localhost/v1
export APPWRITE_TEST_PROJECT_ID=<projeto-de-teste>
export APPWRITE_TEST_KEY=$(appwrite project create-ephemeral-key --scopes users.read --scopes users.write --scopes rows.read --duration 900 --json --show-secrets | jq -r .secret)
```

## Operação

- Backup e restauração: scripts `backup.sh` e `restore.sh` da infraestrutura. O backup é cifrado e conferido por checksum.
- Atualização do Appwrite: backup, atualização da homologação, `migrate`, testes, e só então produção.
- Limites do produto: até 100 notas por usuário; títulos de até 255 caracteres.

## Decisões

As decisões deste projeto ficam em `docs/decisions/`; para registrar uma nova, copie `modelo.md` com a próxima numeração. Os contornos herdados do modelo, com o motivo de cada um, estão no `AGENTS.md`.
