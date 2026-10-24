---
name: code-standards
description: Load this skill whenever writing, editing, or reviewing code. Enforces the project code standards: English identifiers with each language's native accent, comment-free clarity, the ponytail YAGNI decision ladder against over-abstraction, official-docs verification to avoid deprecated APIs, stable and mutually compatible versions, and correct Portuguese grammar for every user-facing string.
---

# Code Standards

Padrões de código do projeto. Aplicam-se a todo código escrito, editado ou revisado.

## Estrutura

O código segue a estrutura já estabelecida no projeto: mesma organização de pastas, mesmos padrões dos arquivos vizinhos, mesma densidade de tratamento de erro. Ao entrar em um repositório, leia os arquivos ao redor antes de escrever e imite o que existe. Estrutura nova só quando o projeto ainda não define nenhuma.

## Escada do ponytail

Antes de escrever qualquer código, suba a escada em ordem. Código novo é o último degrau, o primeiro que falhar vence:

1. Isto precisa existir? Se a resposta for não, a tarefa acaba aqui.
2. Já existe no codebase? Reutilize, estenda ou chame.
3. A biblioteca padrão da linguagem resolve?
4. Um recurso nativo da plataforma resolve (HTML, CSS, SO, framework já presente)?
5. Uma dependência já instalada resolve?
6. Dá para resolver com uma linha ou uma função existente?
7. Só então escreva o mínimo que funciona.

Se a skill `ponytail` estiver disponível no ambiente, carregue-a e siga-a; esta escada é o resumo dela. Fonte: `github.com/DietrichGebert/ponytail`.

A escada vale também para abstrações: nenhuma camada sem um segundo consumidor real, nenhum padrão inventado (interfaces, factories, wrappers) sem uma necessidade que já exista no código. Extraia na segunda ocorrência concreta, nunca na primeira por antecipação. Código plano vence generalidade especulativa.

## Sotaque da linguagem

Todo identificador (variável, função, classe, arquivo) em inglês e no sotaque nativo da linguagem, sem misturar:

| Linguagem                 | Funções e variáveis               | Classes, tipos, componentes | Arquivos                           |
| ------------------------- | --------------------------------- | --------------------------- | ---------------------------------- |
| JS / TS / Vue             | camelCase                         | PascalCase                  | kebab-case ou convenção do projeto |
| Python                    | snake_case                        | PascalCase                  | snake_case                         |
| PHP / Laravel             | camelCase                         | PascalCase                  | PSR: uma classe por arquivo        |
| Go                        | camelCase (exportado: PascalCase) | PascalCase                  | snake_case                         |
| Ruby                      | snake_case                        | PascalCase                  | snake_case                         |
| CSS / classes utilitárias | kebab-case                        | —                           | —                                  |

O sotaque vale também para os recursos da linguagem: cada uma tem seu jeito de iterar, tratar erro e compor. Escreva como um nativo daquela linguagem escreveria, não como um tradutor de outra.

## Código sem comentários

O código se explica sozinho: nomes que carregam a intenção, funções pequenas com uma responsabilidade, sem números mágicos. Quando um trecho parece pedir comentário, o trecho pede renome ou extração, e o problema se resolve no código. Zero comentários explicativos; documentação viva em README e docs do projeto, nunca inline.

## Prosa de interface

Todo texto que o usuário final lê (UI, labels, placeholders, mensagens, e-mails, redação) usa a gramática correta do idioma do projeto. Em português, siga a skill `escrita-pt-br`, que define frases, dois-pontos, ponto e vírgula e textos de tela.

Textos de interface são conteúdo: nada de lorem ipsum, nada de inglês se o projeto é em português, nada de gramática quebrada.

## Documentação oficial

Antes de usar um recurso de linguagem, framework ou API, confirme na documentação oficial da versão em uso que ele é atual. Recurso marcado como deprecated no docs é recurso fora de pauta, mesmo que a memória o lembre como padrão. Em caso de dúvida entre dois jeitos de escrever a mesma coisa, consulte a documentação oficial antes de escolher, não depois de errar.

## Versões

Use as versões estáveis das tecnologias do projeto, compatíveis entre si. Versões novas seguem o que o projeto já declara (package.json, requirements, go.mod), não a última lançada. Dependência nova entra apenas quando a escada do ponytail esgotou as existentes e há justificativa de compatibilidade.
