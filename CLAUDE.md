# CLIuno TallStack template

TALL stack (Tailwind, Alpine, Laravel 13, Livewire/Volt) app that is **two things at
once**: a server-rendered demo app (Breeze auth + Livewire pages) and a full CLIuno
REST backend (Sanctum bearer tokens under `/api/v1`). The API surface is a port of
CLIuno-Laravel-template — keep the two aligned when the contract changes.

## Commands

```bash
composer install && php artisan migrate   # sqlite database/database.sqlite
npm install && npm run build              # vite assets (breeze/livewire UI)
php artisan serve                         # UI at /, API at /api/v1
php artisan test                          # phpunit feature+unit — keep green
./vendor/bin/pint                         # formatting (--test to check)
```

## Structure

- **API** (don't diverge from CLIuno-Laravel-template): `routes/api.php` under
  `apiPrefix: 'api/v1'` (bootstrap/app.php), `app/Http/Controllers/Api/AuthController.php`
  (login issues an `api` + a `refresh`-ability Sanctum token), resource controllers in
  `app/Http/Controllers/`, `admin` middleware gates `PATCH/DELETE /users/{id}` and roles,
  `app/Services/TotpService.php` (dependency-free RFC 6238).
- **UI**: Breeze Livewire (Volt) — auth pages in `resources/views/livewire/pages/auth/`,
  demo pages in `resources/views/livewire/pages/` (`todos`, `posts/index`, `posts/show`,
  `users`), routed via `Volt::route` in `routes/web.php` inside the `auth` middleware
  group. Blade components (`x-text-input` etc.) come from Breeze.
- **User model has NO `name` column** — it's `username` + `first_name`/`last_name`
  (+ phone). Anything Breeze-flavored you add must use those fields; `UserFactory`
  already does. Login accepts **username or email** (LoginForm checks for `@`).

## Contract rules this codebase follows

- Responses: `{status, message, data}` with the exact keys frontends destructure
  (`data.users/user/todos/todo/posts/post/followers/following/isFollowing`,
  login `data.token` + `data.refreshToken`).
- Requests accept camelCase (`usernameOrEmail`, `refreshToken`, `oldPassword`/`newPassword`,
  `otp`) with snake_case fallbacks.
- One-time tokens live on users (`reset_password_token`, `verify_token`); lookup by token.
- The `user` role uses `firstOrCreate` on registration — **both** in the API and in the
  Livewire register page (fresh clone needs no seeding).

## Conventions

Pint formatting; conventional commits; UI tests live in `tests/Feature` (Volt render +
CRUD/follow interactions) — extend them when adding Livewire pages.
