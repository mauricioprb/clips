# AGENTS.md

Sistema web sob medida sobre Appwrite 2.6 self-hosted: site Nuxt 4 em modo SPA em `sites/app` e Functions PHP 8.4 em `functions/<nome>`. A fonte de verdade do projeto Appwrite (banco, tabelas, índices, buckets, functions e sites) é o `appwrite.config.json`.

## Regras

- Regra de negócio e autorização vivem na Function. O site cuida da tela e do estado de interface, e pode repetir validações só para ajudar o usuário.
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

- Em `functions/<nome>`: `composer lint` e `composer test` (o teste de integração precisa das variáveis descritas no README).
- Em `sites/app`: `npm run lint`, `npm run format:check`, `npm run typecheck` e `npm test`.
