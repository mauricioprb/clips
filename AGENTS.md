# AGENTS.md

Clips: registro de horas e relatório mensal de bolsistas, sobre Appwrite 2.6 self-hosted. Site Nuxt 4 em modo SPA em `sites/app`, com FUSE UI, e Functions PHP 8.4 em `functions/<nome>`: `timesheet` (tudo do bolsista), `members` (administração e exclusão da própria conta) e `user-cleanup`. A fonte de verdade do projeto Appwrite (configurações, equipe, banco, tabelas, functions e site) é o `appwrite.config.json`. As regras de negócio estão no README e as tabelas em `docs/dados.md`.

## Regras

- Regra de negócio e autorização vivem na Function. O site cuida da tela e do estado de interface, e pode repetir validações só para ajudar o usuário.
- O acesso é por convite na equipe `clips` ([decisão 0002](docs/decisions/0002-convite-pela-equipe-do-appwrite.md)). As Functions têm `execute: ["team:clips"]`, e administrador é quem tem o papel `owner`. Toda rota de administração confere o papel na Function.
- Regras com invariante (calendário de feriados, completar mês, resumo do mês) ficam em classes `final` em `functions/timesheet/src`, sem SDK, com teste unitário em `tests/Unit`. O `index.php` só valida, autoriza, lê, chama o domínio e grava.
- Datas são texto `AAAA-MM-DD` e horários são minutos desde a meia-noite. Durações nunca são gravadas.
- Imagem do relatório PDF é JPEG. O runtime não tem `gd`, e PNG faz o PDF falhar ([decisão 0003](docs/decisions/0003-relatorio-pdf-na-function.md)).
- A identidade vem do cabeçalho `x-appwrite-user-id`. Campos de autoridade (`userId`, papel, status financeiro) são montados no servidor, campo a campo.
- Toda mudança de banco, function, site ou bucket entra pelo `appwrite.config.json` e sobe com `appwrite push`. Mudou tabela ou coluna: atualize `docs/dados.md` na mesma mudança. Tabela nova com `userId` entra em `$tablesWithUserData` da function `user-cleanup`.
- O código fica sem comentários. Decisões deste projeto viram ADR em `docs/decisions/`, copiando `modelo.md`.
- Segredos ficam nos arquivos `.env`, fora do Git. Use dados fictícios: dumps, dados de produção e dados pessoais ficam fora de ferramentas de IA.
- Todo texto em português (telas, mensagens, docs, commits e respostas) segue a skill `escrita-pt-br`.
- Telas, componentes e estilos do site seguem a skill `fuse-ui`, que parte da documentação em `https://fuseui.overclk.tech/llms.txt` e usa só tokens do tema, sem valores arbitrários do Tailwind.

## Contornos herdados do modelo

Cada um tem motivo; mantenha até a condição de revisão acontecer.

- SDK PHP `appwrite/appwrite` fixo na 28.0.0, com `config.platform.php` 8.4.27: o runtime das Functions é PHP 8.4 e o SDK 29 em diante exige PHP 8.5. Revisar quando existir o runtime `php-8.5`.
- Functions respondem JSON com `$context->res->text(json_encode(...))` e leem o corpo com `json_decode($context->req->bodyText, true)`: `res->json()` transforma listas em objetos e `bodyJson` responde 500 quando o JSON é inválido.
- TypeScript 6.0.3 no site: o `vue-tsc` depende da API JavaScript do compilador, que o TypeScript 7 não tem.
- O `npm audit` acusa 7 alertas com uma origem só, o `node-forge` (GHSA-86w9-cpqp-85rv). Ele entra pelo servidor de desenvolvimento do Nuxt, que só gera certificados e nunca verifica assinatura, não vai para o site publicado e não tem versão corrigida.

## Pronto

Uma mudança está pronta quando estas verificações passam e o comportamento novo tem um teste que falha sem ela:

- Em `functions/<nome>`: `composer lint` e `composer test` (o teste de integração precisa das variáveis descritas no README). Na `timesheet`, também `composer test:unit`.
- Em `sites/app`: `npm run lint`, `npm run format:check`, `npm run typecheck` e `npm test`.
