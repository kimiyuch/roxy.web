# roxy.web

Roxy is a feedback widget for websites. Your client points at what needs changing, and your coding agent reads the feedback over MCP, fixes it and resolves it. *Tell Roxy. Claude fixes it.*

A Laravel 13 app with an Inertia + Vue dashboard. See `docs/architecture.md` for how it fits together.

## Local development

The app runs in [Herd](https://herd.laravel.com) at `https://roxy.web.test`.

```sh
composer install
npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
npm run dev       # or: npm run build
```

Set yourself up:

```sh
php artisan roxy:user you@example.com --name=You           # prints a password
php artisan roxy:site you@example.com "My site" https://my-site.test --repository=me/my-site
php artisan roxy:invite <site id> client@example.com        # emails + prints a magic link
php artisan roxy:mcp-token you@example.com                  # prints the `claude mcp add` command
```

## Tests

```sh
php artisan test
```
