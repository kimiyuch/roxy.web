# Roxy

A drop-in feedback widget for websites: clients point at a problem, and a coding agent reads the feedback over MCP, fixes it and resolves it. A kimiyu app (https://github.com/kimiyuch); the mascot is a galah. Live (later) at `roxy.kimiyu.ch`.

Read first:

- `docs/architecture.md`: the backend as decided (surfaces, magic-link flow, data model, MCP, Hostpoint). Wins over the plan.
- `docs/plan.md`: the original product plan (scope, phases). Written before the name, so it says "Feedback Widget".
- `docs/naming-and-icon.md`: the name, the icon, the palette, the kimiyu house style.

## Stack

Laravel 13, Fortify (dashboard login), Sanctum (MCP tokens), laravel/mcp, Inertia + Vue 3 + Tailwind 4, spatie/image + Imagick. SQLite locally, MySQL on Hostpoint. Runs in Herd at `http://roxy.web.test`.

- Business logic lives in `app/Actions` (feedback create / resolve / delete, reviewer invite / login code / exchange), shared by the controllers and the MCP tools.
- Widget API: `routes/api.php`, `app/Http/Controllers/Widget`, middleware `WidgetCors` + `AuthenticateReviewer`.
- Dashboard pages: `resources/js/pages` (`auth/*`, `feedback/*`).
- Settings: `config/roxy.php`.

## Conventions

- Code style: Laravel defaults, 4 spaces, `vendor/bin/pint`.
- `npm run build` and commit `public/build` (no Node on Hostpoint).
- Run `php artisan test` before committing.
- Setup commands until the dashboard has them: `roxy:user`, `roxy:site`, `roxy:invite`, `roxy:mcp-token`.

## Artwork

- `artwork/icon.png` (rounded, 1024), `icon-square.png` (full bleed), `mascot.png` (no background), `favicon.png` (96), `apple-touch-icon.png` (180), plus their SVGs
- `node scripts/make-icon.mjs` redraws them all (needs Google Chrome, no npm packages)
- `artwork/explorations/` and `scripts/make-icons-explorations.mjs` are the rejected drafts (numbat), kept for reference
