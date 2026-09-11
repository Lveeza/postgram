# Postagram

An Instagram-style social posting application built with Laravel, featuring a full web app and a separate REST API with token-based authentication.

**Live demo:** [https://postgram-production-8430.up.railway.app/](https://your-app.up.railway.app)
**API documentation:** [API.md](./API.md)

---

## Features

- **Authentication & Authorization** — session-based auth for the web app, Sanctum token auth for the API, with Policy-based authorization enforced consistently across both
- **Posts & Comments** — create, edit, delete, with image uploads and pagination
- **Search & Filtering** — search posts by title or body
- **REST API** — full CRUD for posts and comments, scoped token abilities, rate limiting
- **Email Notifications** — post owners are emailed when someone comments, sent via a queued listener so it never blocks the request
- **N+1 Query Optimization** — eager loading throughout, verified via query logging
- **Database Transactions** — post creation/deletion wrapped in transactions to keep a denormalized `posts_count` counter in sync
- **API Resources** — consistent JSON response shapes via `PostResource` / `CommentResource`
- **Automated Tests** — Pest test suite covering web routes, API auth, token abilities, and rate limiting

---

## Tech Stack

| Layer      | Technology                                    |
| ---------- | --------------------------------------------- |
| Backend    | PHP 8.x, Laravel 11                           |
| Database   | MySQL                                         |
| Auth       | Laravel Sanctum (API), Session Auth (Web)     |
| Frontend   | Blade, Tailwind CSS                           |
| Queue      | Database driver                               |
| Email      | Resend (production), Mailtrap (local testing) |
| Testing    | Pest                                          |
| Deployment | Railway (GitHub auto-deploy)                  |

---

## Architecture Notes

This project was built as a learning exercise progressing from vanilla PHP fundamentals through to Laravel, with an emphasis on understanding _why_ the framework is structured the way it is rather than treating it as a black box. Notably:

- The **Repository pattern** used for post listing/search was first hand-built in plain PHP before being reimplemented in Laravel — the abstraction is the same, only the implementation changed.
- **Authorization logic lives in Policies**, not scattered across controllers — both the web and API controllers call the same `PostPolicy`/`CommentPolicy`, so authorization rules never drift out of sync between the two surfaces.
- **Side effects (email notifications) are decoupled from the request cycle** via Events, Listeners, and queued jobs — a comment being created doesn't wait on an email being sent.

---

## Local Setup

```bash
git clone https://github.com/Lveeza/postgram.git
cd postgram
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
```

Set your `MAIL_*` and `DB_*` credentials in `.env`, then:

```bash
php artisan serve
php artisan queue:work   # in a separate terminal, for queued email notifications
```

---

## Running Tests

```bash
./vendor/bin/pest
```

---

## API

See [API.md](./API.md) for full endpoint documentation, including authentication, request/response examples, and rate limiting details.

---

## License

Open-sourced for portfolio/educational purposes.
