# Project AGENTS.md

Persistent context for coding agents. It records decisions that are already made. If a request conflicts with something here, name the conflict before acting.

## 1. How to work

- Small, reviewable increments. Present a short plan before large changes.
- Never commit, push or rewrite history. Never use `git checkout --`, `git restore`, `git stash` or `git reset` on uncommitted work.
- Do not guess third-party APIs (Inertia, PrimeVue, Fortify, dompdf). Read the installed package types or the official docs.
- Close every task with `composer lint:test`, `composer test` and `npm run check`. It is only done when all pass.

## 2. Product

Clips helps research scholarship students log daily hours and produce the monthly PDF report their advisor signs. Single user per account, no teams, no billing. Interface in Brazilian Portuguese only.

Access is invitation only, like the Lumos admin panel. There is no self registration. An admin invites someone by name and email (`InviteUser`), the person gets a link backed by the `invitations` password broker (own token table, 7 days) and sets the password on `/convite/{token}`. `User::status()` is derived: blocked when `is_active` is false, pending while `password_set_at` is null, otherwise active. Only active users can log in. The first admin comes from `php artisan users:invite email "Name" --admin`.

## 3. Stack

| Layer       | Choice                                                                 |
| ----------- | ---------------------------------------------------------------------- |
| Backend     | Laravel 13, PHP 8.5, Fortify for auth and password reset               |
| Frontend    | Inertia 3, Vue 3 `<script setup lang="ts">`, PrimeVue 5, Tailwind 4    |
| Database    | PostgreSQL 18 (SQLite in memory for tests)                             |
| PDF         | dompdf through `barryvdh/laravel-dompdf`, Blade view `reports/monthly` |
| Tests       | Pest 5 (Feature and Unit), Vitest for `resources/js/Lib`               |
| Style       | Pint (`pint.json`), ESLint flat config, Prettier                       |
| Local infra | Docker Compose with PostgreSQL and Mailpit only                        |
| Deploy      | Laravel Forge, triggered by the CI webhook (`docs/deploy.md`)          |

PrimeVue 5 needs a Community license key in `VITE_PRIMEUI_LICENSE`. Do not add a dependency without approval.

## 4. Architecture

Default Laravel skeleton plus `Actions/`, `Support/`, `Enums/` and `Concerns/`. No Services, Repositories or DTOs.

```
app/
├── Actions/             FillMonth, SummarizeMonth, InviteUser, SendInvitation, Fortify/*
├── Concerns/            PasswordValidationRules
├── Enums/               EntrySource, Priority, HolidayRecurrence
├── Http/Controllers/    thin, one resource each, Settings/ for account pages
├── Http/Requests/       validation lives here, never inside an Action
├── Models/              User, TimeEntry, WeeklySlot, DefaultActivity, Holiday (UUID keys)
├── Policies/            OwnedByUserPolicy, returns 404 for other users' records
└── Support/             HolidayCalendar, Minutes

resources/js/
├── Pages/               Inertia pages
├── Components/Layout/   AppLayout, AuthLayout, SettingsLayout
├── Components/Month/    calendar, day drawer, summary, setup checklist
├── Components/UI/       small shared pieces
├── Composables/         useI18n, useTheme
├── Lib/                 pure functions with Vitest tests, PrimeVue preset
├── i18n/                pt-BR copy and PrimeVue locale
└── types/               shared types
```

- An Action has one `execute()` method and real business logic.
- Controllers build the Inertia payload inline in camelCase. Form fields stay snake_case.
- Times are stored as minutes since midnight (`start_minute`, `end_minute`). Dates use the `date` column. Durations are always derived, never stored.

## 5. Business rules

- Daily target is the weekly workload divided by 5. The monthly target is the daily target times the workdays.
- Workdays are Monday to Friday minus national holidays (`HolidayCalendar`) and the user's own holidays. Registration seeds Revolução Farroupilha and Corpus Christi, which the user may delete.
- `FillMonth` runs in a transaction. It places weekly slots first, then fills free windows up to the daily target with default activities, morning then afternoon (`config/timesheet.php`). It never changes or overlaps a manual entry and is idempotent.
- Default activity priority weights are high 3, medium 2, low 1, using smooth weighted round robin.
- Editing any entry turns it into a manual entry.
- The PDF lists workdays only. Weekend and holiday hours are shown in the app but excluded from the report total.

## 6. UI

- Every visible string lives in `resources/js/i18n/pt-BR.ts` and is read through `useI18n()`. Laravel `lang/pt_BR` only covers framework messages.
- Colors come from the PrimeVue preset in `Lib/theme.ts` (UFN blue primary, cool gray surfaces) and the semantic variables in `resources/css/theme.css`. No literal colors in templates.
- Use PrimeVue components for inputs, buttons, dialogs, drawer, toast and confirmations. Time inputs stay native (`InputText type="time"`) for the mobile picker.
- Dark mode, keyboard access and WCAG 2.2 AA contrast are required in everything new.

## 7. Code conventions

- Code, identifiers and docs for agents in English. Product copy in Portuguese.
- PHP: `declare(strict_types=1)`, explicit types, constructor promotion, native enums.
- Vue: typed `defineProps<{}>()`, no `any`.
- No comments. Rename or extract instead.
- Plain punctuation in copy and docs: no em dashes or middots.
