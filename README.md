# CLIuno TallStack template

The CLIuno demo app on the **TALL stack** — Tailwind, Alpine.js, Laravel, Livewire
(Volt) — plus the full CLIuno **REST API** under `/api/v1`, so this template is both a
server-rendered app *and* a drop-in backend for every CLIuno frontend template.

- **UI (Livewire/Volt)**: register, login (username *or* email), profile, todos,
  posts + comments, users + follow.
- **API (Sanctum bearer tokens)**: the shared CLIuno contract — auth (refresh, reset,
  email verification, OTP), users, todos, posts+comments, follows, roles.

## Quick start

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
npm install && npm run build   # or npm run dev
php artisan serve              # UI at /, API at /api/v1
```

Registering the first user creates the `user` role automatically — no seeding needed.

## Checks

```bash
php artisan test        # feature + unit tests
./vendor/bin/pint       # formatting
```

## The API contract

Login sends `{usernameOrEmail, password}` and returns `data.token` (+
`data.refreshToken`). Responses are `{status, message, data}` with the exact keys
CLIuno frontends destructure (`data.users/user/todos/todo/posts/post/followers/`
`following/isFollowing`). The API surface lives in `routes/api.php` +
`app/Http/Controllers/` and matches the CLIuno-Laravel-template implementation.
