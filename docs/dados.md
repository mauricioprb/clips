# Dicionário de dados

Banco `exemplo` do TablesDB (o ID do banco é o nome do app e é igual em todos os ambientes). Nenhuma tabela tem permissão pública: só as Functions leem e gravam, com a chave da execução.

Toda tabela com a coluna `userId` precisa estar na lista `$tablesWithUserData` da function `user-cleanup`, para ser limpa quando o usuário for apagado.

## notes

Notas de cada usuário. Cada pessoa só lê, cria e apaga as próprias notas.

| Coluna | Tipo | Obrigatória | Descrição |
|---|---|---|---|
| `$id` | texto (até 36) | sim | Identificador da nota, gerado pelo Appwrite. |
| `userId` | texto (até 36) | sim | Dono da nota. Vem da sessão, nunca do que o cliente envia. |
| `title` | texto (até 255) | sim | Texto da nota, sem espaços nas pontas. |
| `$createdAt` | data e hora (UTC) | sim | Momento da criação. Define a ordem da lista, da mais nova para a mais antiga. |

Índices:

- `user_id` (`userId`, crescente): lista e conta as notas de um usuário.

Regras:

- Até 100 notas por usuário.
- Apagar a conta do usuário apaga as notas dele: a function `user-cleanup` roda no evento `users.*.delete` e remove as linhas em lotes de até 100 (limite do Appwrite).

## Usuários

Contas do Appwrite (serviço de autenticação), com nome, e-mail e senha. A senha é guardada pelo Appwrite como hash e nunca passa pelas Functions.
