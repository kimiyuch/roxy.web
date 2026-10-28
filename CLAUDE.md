# Roxy

A drop-in feedback widget for websites: clients point at a problem, and a coding agent reads the feedback over MCP, fixes it and resolves it. A kimiyu app (https://github.com/kimiyuch); the mascot is a galah. Live (later) at `roxy.kimiyu.ch`.

Read first:

- `docs/progress.md`: what's done, what's next. Update it at the end of a session.
- `docs/architecture.md`: the backend as decided (surfaces, magic-link flow, data model, MCP, Hostpoint). Wins over the plan.
- `docs/plan.md`: the original product plan (scope, phases). Written before the name, so it says "Feedback Widget".
- `docs/naming-and-icon.md`: the name, the icon, the palette, the kimiyu house style.

## Stack

Laravel 13, Fortify (dashboard login), Sanctum (MCP tokens), laravel/mcp, Inertia + Vue 3 + Tailwind 4, spatie/image + Imagick. SQLite locally, MySQL on Hostpoint. Runs in Herd at `https://roxy.web.test`.

- Business logic lives in `app/Actions` (feedback create / resolve / delete, reviewer invite / login code / exchange), shared by the controllers and the MCP tools.
- Widget API: `routes/api.php`, `app/Http/Controllers/Widget`, middleware `WidgetCors` + `AuthenticateReviewer`.
- Website: `home.blade.php`, `legal.blade.php`, Blade components in `resources/views/components` (layout, header, footer like croc.web and wuff.web). `og-image.blade.php` → `scripts/og-image.sh` → `public/images/og.png`.
- Dashboard pages: `resources/js/pages` (`auth/*`, `feedback/Index.vue`: the inbox, list and detail in one; `sites/Index.vue`: the same three columns, sites in the middle, the open site or "Add site" on the right, see `components/SiteDetail.vue`), layout with the sidebar in `resources/js/layouts/AppLayout.vue`. Icons: `@heroicons/vue` here, `blade-heroicons` on the website. The landing page's mocks (`site-window`, `dashboard-window`) are the design reference for the widget and the dashboard, see `docs/architecture.md`, Look and feel.
- Widget: `resources/widget` (plain JS, Shadow DOM), built by `vite.widget.config.js` into `public/widget.js` and `public/widget-screenshot.js`. Try it on `https://roxy.web.test/widget-test`.
- Settings: `config/roxy.php`.

## Conventions

- Code style: Laravel defaults, 4 spaces, `vendor/bin/pint`.
- `npm run build` (dashboard and widget) and commit `public/build`, `public/widget*.js` (no Node on Hostpoint).
- Run `php artisan test` before committing.
- Sites and reviewers are managed in the dashboard (`/app/sites`); agent tokens still need `roxy:mcp-token`. `roxy:user`, `roxy:site`, `roxy:invite` work too.

## Artwork

- `artwork/icon.png` (rounded, 1024), `icon-square.png` (full bleed), `mascot.png` (no background), `favicon.png` (96), `apple-touch-icon.png` (180), plus their SVGs
- `node scripts/make-icon.mjs` redraws them all (needs Google Chrome, no npm packages)
- `artwork/explorations/` and `scripts/make-icons-explorations.mjs` are the rejected drafts (numbat), kept for reference
