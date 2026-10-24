---
name: escrita-pt-br
description: Gramática e pontuação do português do Brasil. Use sempre que escrever ou revisar texto em português, inclusive textos de tela, mensagens de erro, e-mails, README, docs, ADR, commits, pull requests e respostas ao usuário.
---

# Escrita em português

Todo texto em português segue a norma culta do Brasil e soa como uma pessoa escrevendo, não como uma lista de tópicos colada em frases.

## Frases

Uma ideia por frase, terminada em ponto final. Duas ideias viram duas frases, ou uma frase com um conectivo que mostra a relação entre elas: `porque`, `então`, `mas`, `por isso`, `quando`, `para`.

| Evite                                        | Prefira                                            |
| -------------------------------------------- | -------------------------------------------------- |
| O build falhou: a versão do PHP está errada. | O build falhou porque a versão do PHP está errada. |
| Salve o arquivo; depois publique.            | Salve o arquivo e depois publique.                 |
| Sessão expirada: entre de novo.              | Sua sessão expirou. Entre de novo.                 |

## Dois-pontos

Os dois-pontos aparecem em três casos:

- antes de uma lista ou enumeração anunciada na própria frase;
- antes de uma citação ou fala;
- antes de um exemplo ou valor literal, como em `Use o ID do projeto: banca-juca-dev.`

Para ligar uma causa, uma consequência ou uma explicação à frase anterior, use ponto final ou conectivo.

## Ponto e vírgula

O ponto e vírgula separa itens de uma enumeração quando os itens já têm vírgulas por dentro, como nos itens de lista deste arquivo. Entre duas orações, use ponto final.

## Outros sinais

- Travessão é `—`, com espaço dos dois lados, e só para um aparte curto. Hífen duplo (`--`) não substitui travessão.
- Pontos centralizados (`·`, `•`) não separam ideias no meio do texto. Quem separa é o ponto final.
- Parênteses guardam informação acessória curta. Se o trecho é importante, ele vira frase.

## Textos de tela

- Botões e títulos são curtos e sem ponto final: `Adicionar nota`, `Entrar`.
- Mensagens ao usuário são frases completas, com ponto final: `Não foi possível salvar a nota. Tente de novo.`
- Nada de lorem ipsum nem de inglês em projeto em português. Termos técnicos sem tradução consagrada, como `deploy` ou `token`, podem ficar no original.

## Conferência

Releia o texto antes de entregar e procure `:` e `;`. Cada ocorrência precisa caber em um dos casos acima. Se não couber, troque por ponto final ou por conectivo.
