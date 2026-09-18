# Deploy no Laravel Forge

O Docker é usado só em desenvolvimento. Em produção a aplicação roda num servidor provisionado pelo Forge, com Nginx, PHP-FPM e PostgreSQL.

## Servidor

- PHP 8.5 com `pdo_pgsql`, `mbstring`, `intl`, `gd`, `dom` e `zip`.
- PostgreSQL 18 (banco criado pelo Forge).
- Node 24, para compilar o frontend durante o deploy.
- Não há fila nem agendador: e-mails são enviados na hora (`QUEUE_CONNECTION=sync`) e não existem tarefas agendadas.

## Site

1. Crie o site apontando para o repositório, branch `main`, diretório web `/public`.
2. Ative o certificado HTTPS.
3. Em **Deployments**, copie a URL do _deploy webhook_ e cadastre como secret `FORGE_DEPLOY_WEBHOOK` no GitHub. O CI chama esse webhook depois que os testes passam na `main`. Deixe o _auto deploy_ do Forge desligado para o deploy só acontecer com o CI verde.

## Script de deploy

Além do `git pull` padrão do Forge, o script precisa de:

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

O build usa as dependências de desenvolvimento do npm, por isso o `npm ci` não leva `--omit=dev`.

## Variáveis de ambiente

Parta do `.env.example` e ajuste no painel do Forge:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` com o domínio em HTTPS.
- `APP_KEY` gerada no servidor (`php artisan key:generate`), nunca reaproveitada do ambiente local.
- `DB_*` com os dados do banco criado pelo Forge.
- `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` e `SESSION_SAME_SITE=lax`.
- `MAIL_*` com um provedor real (SMTP da instituição, Resend, Postmark ou SES) e um `MAIL_FROM_ADDRESS` do domínio. Sem isso a redefinição de senha não chega.
- `VITE_PRIMEUI_LICENSE` com a chave Community do PrimeVue. Ela é lida no `npm run build`, por isso precisa estar no `.env` do site antes do deploy. A chave vai para o JavaScript público; isso é esperado, a verificação é offline.

## Depois do primeiro deploy

- Crie o primeiro administrador pelo terminal do Forge. O cadastro é só por convite, então sem esse passo ninguém consegue entrar:

  ```bash
  php artisan users:invite coordenacao@ufn.edu.br "Nome da Pessoa" --admin
  ```

- Configure backups do PostgreSQL no Forge. Excluir a conta apaga os lançamentos em cascata e não há lixeira.
- Confira `/up` para o health check.
