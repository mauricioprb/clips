---
name: fuse-ui
description: Interface do site com a FUSE UI. Use ao criar ou mudar telas, componentes ou estilos em `sites/app`, ao escolher um componente, ao rodar `npx fuse-ui`, ou quando o pedido citar FUSE, `app/ui`, tema, dark mode ou classes do Tailwind.
---

# FUSE UI

## Documentação

A documentação da FUSE para agentes começa em `https://fuseui.overclk.tech/llms.txt`. Leia esse arquivo antes de escolher um componente. Ele lista cada página em Markdown puro, com exemplos e tabelas de props.

Antes de usar um componente, abra a página `.md` dele (por exemplo `https://fuseui.overclk.tech/components/button.md`) e use as props, os eventos e os slots descritos ali. A memória de outras bibliotecas não vale aqui, nem a de shadcn-vue: a API é a da página.

A versão do registry é a do `@overclock/fuse-ui` em `devDependencies`. Quando a página e a cópia em `app/ui/` divergirem, vale o código da cópia, que é o que roda no projeto.

## Componentes

O fonte dos componentes fica no projeto, em `sites/app/app/ui/`. Os comandos rodam dentro de `sites/app`:

1. Procure o componente em `app/ui/`. Se já existe, importe de `@/ui/<nome>`.
2. Se não existe, confira o nome com `npx fuse-ui list` e copie com `npx fuse-ui add <nome>`. O CLI traz as dependências do componente e preserva arquivos que já existem.
3. Para ver o que mudou no registry, use `npx fuse-ui diff <nome>`. Diferença só de aspas e quebra de linha vem do Prettier do projeto e não é atualização.

`AuthLogin`, `AuthCard`, `AppShellStacked` e `Label` são personalizados. Mudanças neles são feitas à mão, e o `add --overwrite` fica reservado aos componentes que ainda estão iguais ao registry.

Os textos padrão dos componentes vêm do plugin `fuse-locale`, em português do Brasil. Texto específico da tela entra pela prop do componente.

Campo obrigatório não leva asterisco. Quando um campo for opcional, escreva `(opcional)` no rótulo, como em `Telefone (opcional)`.

## Estilo

As classes do Tailwind usam só a escala e os tokens do tema:

- cores semânticas, como `bg-background`, `text-foreground`, `text-muted-foreground`, `border-border` e `bg-primary`;
- tipografia, como `text-body-md`, `text-heading-3` e `text-caption`;
- espaçamento, tamanho, raio (`rounded-md`) e sombra (`shadow-s`) da escala padrão;
- breakpoints `sm`, `md`, `lg`, `tablet` e `desktop`.

Valor arbitrário entre colchetes fica fora do código do projeto: `w-[37px]`, `mt-[13px]`, `text-[#333]`, `max-w-[1280px]` e `min-[360px]:` são exemplos. Quando a escala não tem o valor, use o mais próximo. Quando o valor se repete e faz parte da identidade, crie um token em `app/assets/css/theme.css` e use a classe gerada. Variantes de estado do Reka UI, como `data-[state=open]:` e `aria-[selected=true]:`, são seletores e continuam permitidas.

O dark mode vem dos tokens. A mesma classe semântica funciona nos dois temas, então a tela não precisa de `dark:` com cor fixa.

Conferência antes de terminar, em `sites/app`:

```bash
grep -rnE '(^|[^a-z])(min|max|w|h|p[trblxy]?|m[trblxy]?|gap|text|bg|border|rounded|shadow|top|left|right|bottom|inset|size|grid-cols|grid-rows)-\[' app/pages app/components app/error.vue
```

O resultado esperado é vazio.
