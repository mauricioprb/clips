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
- `docs/dados.md`: dicionário de dados.
- `docs/decisions/`: decisões deste projeto (ADR no formato MADR), a partir de `modelo.md`.
- `AGENTS.md`: regras para agentes de IA. Skills ficam em `.agents/skills`.

## Pré-requisitos

- Appwrite 2.6.1 com SMTP configurado (o convite e a recuperação de senha são e-mails do Appwrite) e o CLI do Appwrite 28.9.0, com `appwrite login --endpoint <endpoint>/v1`.
- PHP 8.4 ou superior com Composer, Node.js 24 (LTS) com npm. O `.nvmrc` do site fixa a versão.
- Git com suporte a links simbólicos (`.claude/skills` aponta para `.agents/skills`).

## Primeira execução

Primeira vez num projeto da fábrica? Siga o [guia do desenvolvedor](https://docs.overclk.tech): do computador vazio ao app publicado.

Para rodar na sua máquina:

```bash
for function in functions/*/; do (cd "$function" && composer install); done
cd sites/app && cp .env.example .env && npm ci
```

Preencha `NUXT_PUBLIC_APPWRITE_PROJECT_ID` no `sites/app/.env` e rode `npm run dev` em `sites/app`.

Num projeto Appwrite novo, além do `appwrite push` (seção Publicar), rode uma vez:

```bash
appwrite project create-web-platform --platform-id localhost --name "Computador local" --hostname localhost
appwrite project update-membership-privacy-policy --user-id=true --user-name=false --user-email=false \
  --user-phone=false --user-mfa=false --user-accessed-at=false
```

O primeiro comando libera o site rodando no seu computador. O segundo impede que bolsistas vejam o nome e o e-mail dos colegas pela API de equipes ([decisão 0002](docs/decisions/0002-convite-pela-equipe-do-appwrite.md)).

O primeiro administrador entra pela equipe com o papel `owner`. Com a sessão do console no CLI, o convite é aceito na hora e não envia e-mail:

```bash
appwrite teams create-membership --team-id clips --roles owner --email coordenacao@ufn.edu.br --name "Nome da Pessoa"
```

A pessoa então usa **Esqueceu a senha?** na tela de login para criar a senha. Os próximos convites saem pela tela **Usuários**.

## Publicar

```bash
appwrite push settings --force
appwrite push team --all --force
appwrite push table --all --force
appwrite push function --all --activate --force
appwrite push site --site-id app --activate --with-variables --force
```

A ordem importa: configurações e esquema, depois as functions e por último o site.

O `appwrite.config.json` aponta para o projeto de desenvolvimento (`clips-dev`). Os IDs do banco, das tabelas, da equipe e das functions são iguais em todos os ambientes. Para publicar em outro ambiente, as variáveis do CLI têm prioridade sobre o arquivo:

```bash
export APPWRITE_ENDPOINT=https://<servidor>/v1
export APPWRITE_PROJECT_ID=clips-hml
```

O `sites/app/.env` precisa ter os valores do mesmo ambiente antes do `push site`. No CI, o pipeline gera esse arquivo a partir dos segredos do ambiente. Os valores `NUXT_PUBLIC_*` ficam visíveis no navegador, então nunca guardam segredo.

O site de desenvolvimento está em `https://clips-dev.overclk.tech`.

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

O `composer test` roda testes de integração contra um projeto Appwrite descartável, nunca contra produção. Eles criam usuários de teste, colocam na equipe `clips` e apagam tudo no fim. A chave é efêmera e criada com a sua sessão do CLI:

```bash
export APPWRITE_TEST_ENDPOINT=https://overmaker.overclk.tech/v1
export APPWRITE_TEST_PROJECT_ID=clips-dev
export APPWRITE_TEST_KEY=$(appwrite project create-ephemeral-key --scopes users.read --scopes users.write \
  --scopes rows.read --scopes teams.read --scopes teams.write --duration 1800 --json --show-secrets | jq -r .secret)
```

## Operação

- Backup e restauração: scripts `backup.sh` e `restore.sh` da infraestrutura. O backup é cifrado e conferido por checksum.
- Atualização do Appwrite: backup, atualização da homologação, `migrate`, testes, e só então produção.
- Limites do produto: 24 lançamentos por dia, 50 horários na grade, 50 atividades padrão e 50 feriados por pessoa, 10 laboratórios no perfil e até 100 membros na equipe.
- Bloquear um acesso mantém os dados da pessoa. Excluir a conta apaga tudo, e o sistema impede que a última pessoa administradora exclua a própria conta.

## Decisões

As decisões deste projeto ficam em `docs/decisions/`:

- [0001: FUSE UI com fonte local](docs/decisions/0001-fuse-ui-com-fonte-local.md), herdada do modelo.
- [0002: acesso por convite com a equipe nativa do Appwrite](docs/decisions/0002-convite-pela-equipe-do-appwrite.md).
- [0003: relatório em PDF gerado na function com dompdf e logos em JPEG](docs/decisions/0003-relatorio-pdf-na-function.md).

Para registrar uma nova, copie `modelo.md` com a próxima numeração. Os contornos herdados do modelo, com o motivo de cada um, estão no `AGENTS.md`.
