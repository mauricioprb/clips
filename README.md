# Clips

## O que é

Registro de horas e relatório mensal de bolsistas. O bolsista cadastra os dados da bolsa, a grade semanal e as atividades padrão, lança as atividades de cada dia, completa o mês automaticamente e baixa o relatório em PDF que o orientador assina.

- Usuários: bolsistas convidados pela coordenação. Não existe cadastro aberto.
- Administração: quem coordena as bolsas convida pessoas, reenvia convites, dá a administração a outras pessoas e bloqueia acessos.

Regras de negócio:

- A meta diária é a carga horária semanal dividida por 5. A meta do mês é a meta diária vezes os dias úteis.
- Dias úteis são de segunda a sexta, exceto os feriados nacionais e os feriados cadastrados pelo bolsista. Todo perfil novo começa com a Revolução Farroupilha e o Corpus Christi, que podem ser removidos.
- **Completar mês** lança a grade semanal nos dias em que ela vale e completa cada dia útil até a meta com as atividades padrão, primeiro de manhã (a partir das 08:00, até 4 horas) e depois à tarde (a partir das 14:00). Lançamentos manuais nunca são alterados nem sobrepostos.
- A prioridade das atividades padrão define quantas vezes cada uma entra no rodízio: alta 3, média 2 e baixa 1.
- O relatório em PDF lista só os dias úteis. Horas lançadas em fins de semana e feriados aparecem no calendário, mas ficam fora do total.

## Estrutura

- `appwrite.config.json`: projeto Appwrite (configurações de autenticação, equipe, banco, tabelas, functions e site).
- `functions/timesheet/`: API do bolsista em PHP 8.4 (mês, lançamentos, completar mês, PDF, perfil, grade, atividades e feriados). As regras ficam em classes de domínio em `src/`, com testes unitários e de integração.
- `functions/members/`: lista e bloqueia membros e exclui a própria conta.
- `functions/user-cleanup/`: apaga os dados de um usuário quando a conta dele é apagada.
- `sites/app/`: site Nuxt 4 em modo SPA com FUSE UI, com testes Vitest.
- `emails/`: modelos dos e-mails de convite e de recuperação de senha, feitos a partir do bloco `action` do item `emails` da FUSE UI (logo, azul e textos do Clips, com as variáveis do Appwrite). São aplicados no projeto de produção pelo CLI, nos idiomas `pt-br` e `en` ([Configurar a publicação](https://docs.overclk.tech/guia/configurar-publicacao), passo 8). O logo usado neles fica em `sites/app/public/email/`.
- `docs/dados.md`: dicionário de dados.
- `docs/decisions/`: decisões deste projeto (ADR no formato MADR), a partir de `modelo.md`.
- `AGENTS.md`: regras para agentes de IA. Skills ficam em `.agents/skills`.

## Pré-requisitos

- Docker com Docker Compose 2 e pelo menos 4 GB de RAM livres, para o Appwrite 2.6.1 local (a mesma versão do servidor).
- CLI do Appwrite 28.9.0.
- PHP 8.4 ou superior com Composer, Node.js 24 (LTS) com npm. O `.nvmrc` do site fixa a versão.
- Git com suporte a links simbólicos (`.claude/skills` aponta para `.agents/skills`).

## Primeira execução

Primeira vez num projeto da fábrica? Siga o [guia do desenvolvedor](https://docs.overclk.tech): do computador vazio ao app publicado. Ele explica como subir o Appwrite local.

O Clips é desenvolvido e testado contra o Appwrite da sua máquina, nunca contra o servidor. No console local (`http://localhost`), crie o projeto com o ID `clips` e rode, na raiz do repositório e com o CLI logado no Appwrite local:

```bash
appwrite push settings --force
appwrite push team --all --force
appwrite push table --all --force
appwrite push function --all --activate --force
appwrite project create-web-platform --platform-id 'unique()' --name "Computador local" --hostname localhost
appwrite project update-membership-privacy-policy --user-id=true --user-name=false --user-email=false \
  --user-phone=false --user-mfa=false --user-accessed-at=false
```

A política de privacidade impede que bolsistas vejam o nome e o e-mail dos colegas pela API de equipes ([decisão 0002](docs/decisions/0002-convite-pela-equipe-do-appwrite.md)).

Depois instale as dependências e suba o site:

```bash
for function in functions/*/; do (cd "$function" && composer install); done
cd sites/app && cp .env.example .env && npm ci && npm run dev
```

O Appwrite local não envia e-mail, então convite e "Esqueceu a senha?" não funcionam nele. Crie o seu administrador pelo CLI, já com senha:

```bash
appwrite users create --user-id 'unique()' --email voce@exemplo.com --password 'uma-senha-local' --name "Seu Nome"
appwrite teams create-membership --team-id clips --roles owner --user-id <id-mostrado-acima>
```

## Publicar

O servidor tem só a produção, no projeto `clips`, em https://clips.overclk.tech. É o mesmo ID do projeto local, então quem decide para onde um `appwrite push` vai é a sessão do CLI: confira `appwrite whoami` antes de publicar na sua máquina.

O merge na `main` publica sozinho: o job `deploy` do CI envia esquema, functions e site, nessa ordem, e confere o endereço no fim. Por isso, tudo é testado na sua máquina antes do pull request. Ninguém roda `appwrite push` contra o servidor.

As configurações de autenticação (`appwrite push settings`), a política de privacidade da equipe e os modelos de e-mail não passam pelo pipeline. Quem administra aplica com a própria sessão no servidor, sempre que essa parte mudar ([Configurar a publicação](https://docs.overclk.tech/guia/configurar-publicacao)).

Os valores `NUXT_PUBLIC_*` do `sites/app/.env` ficam visíveis no navegador, então nunca guardam segredo.

## Verificações

```bash
cd functions/timesheet && composer lint && composer test:unit && composer test
cd functions/members && composer lint && composer test
cd functions/user-cleanup && composer lint

cd sites/app
npm run lint
npm run format:check
npm run typecheck
npm test
```

Ao commitar, um hook (Husky e lint-staged) formata os arquivos alterados com Pint, ESLint e Prettier e roda o typecheck e os testes do site. O CI (`.github/workflows/ci.yml`) repete as verificações e os testes unitários em todo pull request e push na `main`, e confere o padrão de agentes.

O `composer test` roda os testes de integração contra o Appwrite local, nunca contra o servidor. Eles criam usuários de teste, colocam na equipe `clips` e apagam tudo no fim. Com as functions publicadas no projeto local, crie a chave efêmera com a sua sessão do CLI local:

```bash
export APPWRITE_TEST_ENDPOINT=http://localhost/v1
export APPWRITE_TEST_PROJECT_ID=clips
export APPWRITE_TEST_KEY=$(appwrite project create-ephemeral-key --scopes users.read --scopes users.write \
  --scopes rows.read --scopes teams.read --scopes teams.write --duration 1800 --json --show-secrets | jq -r .secret)
```

## Operação

- Backup e restauração: scripts `backup.sh` e `restore.sh` da infraestrutura. O backup é cifrado e conferido por checksum.
- Atualização do Appwrite: backup, ensaio num servidor temporário restaurado do backup, `migrate` e testes, e só então o servidor real (FLX-07). O Appwrite local de quem desenvolve acompanha a versão do servidor.
- Limites do produto: 24 lançamentos por dia, 50 horários na grade, 50 atividades padrão e 50 feriados por pessoa, 10 laboratórios no perfil e até 100 membros na equipe.
- Bloquear um acesso mantém os dados da pessoa. Excluir a conta apaga tudo, e o sistema impede que a última pessoa administradora exclua a própria conta.

## Decisões

As decisões deste projeto ficam em `docs/decisions/`:

- [0001: FUSE UI com fonte local](docs/decisions/0001-fuse-ui-com-fonte-local.md), herdada do modelo.
- [0002: acesso por convite com a equipe nativa do Appwrite](docs/decisions/0002-convite-pela-equipe-do-appwrite.md).
- [0003: relatório em PDF gerado na function com dompdf e logos em JPEG](docs/decisions/0003-relatorio-pdf-na-function.md).

Para registrar uma nova, copie `modelo.md` com a próxima numeração. Os contornos herdados do modelo, com o motivo de cada um, estão no `AGENTS.md`.
