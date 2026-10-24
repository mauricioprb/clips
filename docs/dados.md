# Dicionário de dados

Banco `clips` do TablesDB (o ID do banco é o nome do app e é igual em todos os ambientes). Nenhuma tabela tem permissão pública: só as Functions leem e gravam, com a chave da execução.

Toda tabela com a coluna `userId` precisa estar na lista `$tablesWithUserData` da function `user-cleanup`, para ser limpa quando o usuário for apagado. Hoje são as cinco tabelas abaixo.

Datas são texto no formato `AAAA-MM-DD`, sem fuso, porque representam o dia do calendário e não um instante. Horários são minutos desde a meia-noite (`480` é 08:00). Durações nunca são gravadas: saem de `endMinute - startMinute`.

## profiles

Dados da bolsa de cada pessoa, usados no cabeçalho do relatório e na meta diária. A linha é criada na primeira chamada da pessoa à function `timesheet`, junto com os feriados iniciais, numa transação.

| Coluna | Tipo | Obrigatória | Descrição |
|---|---|---|---|
| `$id` | texto (até 36) | sim | Igual ao ID do usuário. Garante um perfil por pessoa. |
| `userId` | texto (até 36) | sim | Dono do perfil. Vem da sessão. |
| `advisorName` | texto (até 255) | não | Nome do orientador. |
| `scholarshipName` | texto (até 255) | não | Nome da bolsa (CAPES, PROBIC...). |
| `laboratories` | lista de texto (até 120 cada) | não | De 1 a 10 laboratórios ou salas, sem repetição. |
| `weeklyWorkloadMinutes` | inteiro de 60 a 2640 | não (padrão 1200) | Carga semanal em minutos, em passos de 30. A meta diária é esse valor dividido por 5. |

Índices: `user_id` (único, `userId`).

O nome e o e-mail da pessoa não ficam aqui, porque são da conta do Appwrite.

## timeEntries

Lançamentos de horas.

| Coluna | Tipo | Obrigatória | Descrição |
|---|---|---|---|
| `userId` | texto (até 36) | sim | Dono do lançamento. |
| `date` | texto (10) | sim | Dia do lançamento, `AAAA-MM-DD`. |
| `startMinute` | inteiro de 0 a 1439 | sim | Início. |
| `endMinute` | inteiro de 1 a 1440 | sim | Fim, sempre depois do início. |
| `description` | texto (até 255) | sim | Atividade realizada. |
| `source` | `manual`, `schedule` ou `activity` | sim | Origem: lançado pela pessoa, pela grade semanal ou pelo preenchimento automático. Editar um lançamento torna ele `manual`. |

Índices: `user_date` (`userId`, `date`), que carrega o mês e confere sobreposição no dia.

Regras:

- Dois lançamentos do mesmo dia não se sobrepõem.
- Até 24 lançamentos por dia.
- "Completar mês" nunca altera nem sobrepõe um lançamento `manual`. Só move os lançamentos `schedule` quando o horário da grade muda.

## weeklySlots

Grade semanal, com os compromissos fixos lançados pelo "Completar mês".

| Coluna | Tipo | Obrigatória | Descrição |
|---|---|---|---|
| `userId` | texto (até 36) | sim | Dono do horário. |
| `weekday` | inteiro de 1 a 5 | sim | Dia da semana, de segunda (1) a sexta (5). |
| `startMinute` | inteiro de 0 a 1439 | sim | Início. |
| `endMinute` | inteiro de 1 a 1440 | sim | Fim. |
| `description` | texto (até 255) | sim | O que é lançado no dia. |
| `validFrom` | texto (10) | não | Primeiro dia em que vale. Vazio vale desde sempre. |
| `validUntil` | texto (10) | não | Último dia em que vale, nunca antes de `validFrom`. |

Índices: `user_id` (`userId`). Até 50 horários por pessoa.

## defaultActivities

Atividades usadas para completar os dias úteis até a meta.

| Coluna | Tipo | Obrigatória | Descrição |
|---|---|---|---|
| `userId` | texto (até 36) | sim | Dono da atividade. |
| `description` | texto (até 255) | sim | O que é lançado. |
| `priority` | `high`, `medium` ou `low` | sim | Peso no rodízio: 3, 2 e 1. |

Índices: `user_id` (`userId`). Até 50 atividades por pessoa.

## holidays

Feriados próprios da pessoa, somados aos nacionais, que são calculados no código e não ficam gravados.

| Coluna | Tipo | Obrigatória | Descrição |
|---|---|---|---|
| `userId` | texto (até 36) | sim | Dono do feriado. |
| `name` | texto (até 120) | sim | Nome mostrado no calendário. |
| `recurrence` | `once`, `yearly` ou `corpus_christi` | sim | Só nesta data, todo ano no mesmo dia e mês, ou Corpus Christi (60 dias depois da Páscoa). |
| `date` | texto (10) | não | Obrigatória, exceto para `corpus_christi`. Em `yearly`, o ano é ignorado. |

Índices: `user_id` (`userId`). Até 50 feriados por pessoa. Todo perfil novo começa com a Revolução Farroupilha (20 de setembro) e o Corpus Christi, que a pessoa pode apagar.

## Usuários e equipe

Contas do Appwrite, com nome, e-mail e senha. O hash da senha é guardado pelo Appwrite e nunca passa pelas Functions. Só membros confirmados da equipe `clips` chamam as Functions. Quem tem o papel `owner` na equipe administra o sistema: convida, reenvia convite, dá ou tira a administração e bloqueia.

Apagar a conta apaga os dados dela. A function `user-cleanup` roda no evento `users.*.delete` e remove as linhas das cinco tabelas em lotes de até 100.
