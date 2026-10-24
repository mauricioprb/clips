---
status: aceito
date: 2026-10-24
---

# Acesso por convite com a equipe nativa do Appwrite

## Contexto e problema

O Clips só aceita pessoas convidadas. No Laravel, o convite era um token próprio, válido por 7 dias, e o papel de administrador era uma coluna `is_admin`. O Appwrite deixa qualquer pessoa criar conta pela API, então o bloqueio precisa estar no servidor, e não só na tela de login.

## Opções consideradas

- Equipe `clips` do Appwrite: o convite, o e-mail, a criação da conta e o aceite são nativos; administrador é quem tem o papel `owner`.
- Token de convite próprio numa tabela, com e-mail enviado por uma Function e uma Function pública para aceitar.
- Rótulos de usuário (`labels`) gravados por uma Function, com o link de recuperação de senha servindo de convite.

## Decisão

Opção escolhida: equipe `clips`, porque o Appwrite já faz o fluxo inteiro com segurança e as Functions exigem `execute: ["team:clips"]`. Conta criada fora de um convite não chama nada e é desconectada no login. O link de recuperação expira em 1 hora, curto demais para convite.

- Convidar, reenviar convite e trocar o papel de administrador são chamadas da própria sessão do administrador à API de equipes. O Appwrite só aceita essas chamadas de quem é `owner`.
- Listar membros com e-mail e bloquear o acesso passam pela function `members`, com a chave do servidor, porque a política de privacidade da equipe esconde nome e e-mail dos colegas.

Quando revisar: se o Appwrite passar a permitir desligar o cadastro aberto mantendo o login por e-mail e senha, ou se o convite precisar expirar.

### Consequências

- Bom: nenhum token, e-mail ou página de aceite próprios para manter, e a proteção contra cadastro aberto está na permissão de execução.
- Ruim: o convite não expira sozinho, só quando é usado ou reenviado. Antes eram 7 dias.
- Ruim: a privacidade da equipe é uma política do projeto, aplicada por comando e documentada no README, e não pelo `appwrite.config.json`.
