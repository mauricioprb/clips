---
status: aceito
date: 2026-10-24
---

# FUSE UI pelo registry, com fonte local por cliente

## Contexto e problema

Cada cliente pode precisar de uma interface diferente. Os componentes usados pelo sistema devem pertencer ao seu repositório e acompanhar a entrega do projeto.

## Decisão

Usar somente o registry/CLI da FUSE UI. O pacote npm `@overclock/fuse-ui` distribui a ferramenta de cópia e fica fixado em `devDependencies` (0.4.0), para que `add` e `diff` usem sempre o mesmo registry; não é dependência de execução do aplicativo.

A organização segue o registry: componentes e seções em `app/ui/`, estilos em `app/assets/css/`, utilitários em `app/lib/` e composables em `app/composables/`. O `components.json` aponta para `app/`. As importações usam os aliases do próprio Nuxt.

As telas de autenticação preservam o formulário de três campos do cadastro e o login por e-mail e senha. Não exibem opções sem implementação. O Input encaminha `required` ao elemento nativo, com teste de regressão no registry e no exemplo.

O cabeçalho tem Logoipsum, menu da conta e tema, sem navegação redundante. O botão de adicionar e o campo compartilham uma linha de layout. Falha ao salvar preserva o texto; falha no carregamento oferece nova tentativa.

O plugin de locale seleciona português do Brasil. A paleta em `app/assets/css/theme.css` foi gerada pela FUSE com marca `#a4472f`, neutros `trail-print` e raio `0.5rem`, passando em 66 verificações de contraste. O composable `useColorMode` oferece claro, escuro e sistema; o head aplica a preferência antes de montar a interface.

Atualizações de componentes são aplicadas explicitamente após revisão do diff. Arquivos personalizados não são sobrescritos por padrão. As dependências necessárias ficam fixadas no `package.json` e no lockfile, e a licença MIT acompanha o fonte em `FUSE-UI-LICENSE`.

### Consequências

- Cada projeto mantém seu próprio fonte e suas personalizações.
- O sistema pode ser mantido sem acesso ao repositório da biblioteca.
- Correções da biblioteca precisam ser revisadas e incorporadas às cópias utilizadas.
