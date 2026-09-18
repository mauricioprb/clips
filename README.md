<img src="./public/images/logo_clips_light.svg" alt="Clips" width="150" />

Registro de horas e relatório mensal de bolsistas. O bolsista cadastra os dados da bolsa, a grade semanal e as atividades padrão, lança as atividades de cada dia, completa o mês automaticamente e baixa o relatório em PDF.

## Stack

| Camada           | Escolha                                                  |
| ---------------- | -------------------------------------------------------- |
| Backend          | Laravel 13, PHP 8.5, Fortify                             |
| Frontend         | Inertia 3, Vue 3, TypeScript, PrimeVue 5, Tailwind CSS 4 |
| Banco            | PostgreSQL 18                                            |
| PDF              | dompdf (`barryvdh/laravel-dompdf`)                       |
| Testes           | Pest 5 e Vitest                                          |
| Estilo de código | Pint (PSR-12 com preset Laravel), ESLint e Prettier      |
| Deploy           | Laravel Forge                                            |

## Ambiente local

PHP 8.5, Composer, Node 24 e Docker. O Docker sobe apenas o PostgreSQL e o Mailpit; PHP e Node rodam na máquina.

```bash
composer install
composer run setup
composer run dev
```

`composer run setup` copia o `.env`, gera a chave, sobe os containers, roda as migrations e compila o frontend. `composer run dev` sobe o servidor, o Vite e o log juntos.

- Aplicação: http://localhost:8000
- E-mails de redefinição de senha: http://localhost:8025 (Mailpit)
- Usuário de teste: `php artisan db:seed` cria o admin `bolsista@example.com` com a senha `password1`

## Acesso por convite

Não existe cadastro aberto. Um administrador convida a pessoa em **Usuários** com nome e e-mail, e ela recebe um link para criar a senha. O link vale por 7 dias e pode ser reenviado. Administradores também podem bloquear e desbloquear o acesso.

O primeiro administrador é criado pelo terminal. O comando envia o e-mail e imprime o link:

```bash
php artisan users:invite coordenacao@ufn.edu.br "Nome da Pessoa" --admin
```

O PrimeVue 5 exige uma chave de licença. Gere uma chave Community em primeui.dev e coloque em `VITE_PRIMEUI_LICENSE`. Sem ela a interface funciona, mas mostra um aviso de licença.

## Comandos

```bash
composer lint          # Pint
composer test          # Pest
npm run check          # ESLint, Prettier, tipos, Vitest e build
composer run services:stop
```

O hook de pre-commit (Husky com lint-staged) formata os arquivos alterados. Como o `.npmrc` desativa scripts de instalação, ative o hook uma vez com `npx husky`.

## Regras de negócio

- A meta diária é a carga horária semanal dividida por 5. A meta do mês é a meta diária vezes os dias úteis.
- Dias úteis são de segunda a sexta, exceto feriados nacionais e os feriados cadastrados pelo bolsista. No cadastro já entram a Revolução Farroupilha e o Corpus Christi, que podem ser removidos.
- **Completar mês** lança a grade semanal nos dias em que ela vale e completa cada dia útil até a meta com as atividades padrão, primeiro de manhã (a partir de `TIMESHEET_MORNING_START`, até `TIMESHEET_MORNING_MAX_MINUTES`) e depois à tarde (a partir de `TIMESHEET_AFTERNOON_START`). Lançamentos manuais nunca são alterados nem sobrepostos.
- A prioridade das atividades padrão define quantas vezes cada uma é usada: alta 3, média 2, baixa 1.
- O relatório em PDF lista apenas os dias úteis. Horas lançadas em fins de semana e feriados ficam fora do total.

Deploy: veja [docs/deploy.md](docs/deploy.md).
