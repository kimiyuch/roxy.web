# Roxy: backend architecture

Decided on 2026-10-27, after `plan.md`. Where the two disagree, this file wins.

## Decisions

| Question | Decision |
|---|---|
| Hosting | Hostpoint, like the other kimiyu apps (`roxy.kimiyu.ch`), MySQL. No long-running workers: the queue is drained from cron. |
| Dashboard | Inertia + Vue 3 (no separate SPA API). |
| Reviewers | Per site. One invite = one site. |
| Can reviewers see each other's feedback? | Yes: the widget lists open feedback on the current page from every reviewer of the site, to avoid duplicates. |
| Screenshot storage | The `local` disk (`storage/app/private`) on the Hostpoint server, in Switzerland. No S3: screenshots are served through Laravel as short-lived signed URLs (`temporaryUrl`, `serve => true`). `deploy.sh` must never touch `storage/`. |
| Tenancy | `accounts` from day one; every query is scoped by `account_id` through policies. No teams, roles or billing yet. |

## One app, three ways in

| Surface | Caller | Auth | Where |
|---|---|---|---|
| `/api/widget/{site key}/*` | `widget.js` on client sites, cross-origin | Reviewer Bearer token (no cookies) | `routes/api.php`, `App\Http\Controllers\Widget` |
| `/app/*` | Owners / members | Fortify session | `routes/web.php`, `resources/js/pages` |
| `/mcp` | Claude Code, Cursor | Sanctum token with the `mcp` ability | `routes/ai.php`, `App\Mcp` |

Plus `/` (landing page) and `/r/{reviewer}` (magic links).

## Dashboard accounts

Fortify, session login. `/register` is open: it creates a new account with the person signing up as its owner (`CreateNewUser`, at most 5 sign-ups per hour and IP). Password reset by email. No email verification yet. `roxy:user` still creates users from the command line.

## Reviewer login (magic link)

Roxy's domain can't write to the client site's localStorage, so the login goes through the site:

1. An owner invites `client@x.ch` (`InviteReviewer`). The email links to `roxy.kimiyu.ch/r/{reviewer}`, signed, valid 14 days, reusable on other devices.
2. That route (`MagicLinkController`) mints a one-time code (15 minutes, hash stored on the reviewer) and redirects to the site's `start_url` (or `?to=` if that's on an allowed origin) with `#roxy=<code>`. The fragment never reaches a server log or a Referer header.
3. `widget.js` sees the code, `POST /session` trades it for a long-lived token (`ExchangeLoginCode`, one `reviewer_tokens` row per browser, SHA-256 only), keeps the token in localStorage and clears the fragment.
4. `?feedback` on any page lets a reviewer ask for a new link by email (`POST /link`). It always answers 202, whether or not the email belongs to a reviewer.

## Widget API

| Method | Path (under `/api/widget/{key}`) | Auth | |
|---|---|---|---|
| POST | `session` | code | Trade the magic-link code for a token |
| POST | `link` | none | Email a magic link to a known reviewer |
| GET | `me` | token | Reviewer + site name and settings (button position, colour) |
| DELETE | `session` | token | Log out this browser (not used by the widget: reviewers stay signed in until they’re removed in the dashboard) |
| GET | `feedback?url=` | token | Open feedback on that page (same path), all reviewers |
| POST | `feedback` | token | Multipart: `body`, `url`, `context`/`element`/`console`/`network` as JSON strings, `screenshots[]` + `screenshot_kinds[]` |

- **CORS per site** (`WidgetCors`): only the site's `allowed_origins` (exact, or `https://*.example.ch` for one subdomain level). It answers preflights itself. Laravel's global CORS config is switched off. The Origin check is not the security boundary; the token is.
- **Middleware order**: `WidgetCors` resolves the site by key and `AuthenticateReviewer` resolves the token, both before route model binding and the rate limiters (`bootstrap/app.php`), because the CORS answer and the limiter keys depend on them.
- **Rate limits** (`AppServiceProvider`): login/link 10/min per IP and 100/h per site; API 120/min per reviewer; new feedback 10/min per reviewer and 300/h per site.
- **Captured context** is trimmed server-side to known keys and fixed sizes (`App\Support\CapturedContext`): 20 console entries, 20 failed requests (query strings dropped), 4000 characters of element HTML.
- **Screenshots** are compressed in the browser (JPEG/WebP, long edge ≤ 2560), uploaded through Laravel (≤ 8 MB each, ≤ 5), then ping-validated, fitted and re-encoded by Imagick, which strips metadata (`CreateFeedback`, ported from Nightnurse). JPEG stays JPEG, everything else becomes PNG. They are only served through short-lived temporary URLs. No presigned uploads for now.

## Data model

| Table | Notes |
|---|---|
| `accounts` | The tenant |
| `users` | `account_id`, `role` (`owner` for now). Dashboard users only. |
| `sites` | ULID; public `key` (in the script tag); `allowed_origins`; `start_url` (where magic links land); `repository` (tells the agent which codebase); `settings` |
| `reviewers` | ULID; per site, unique `(site_id, email)`; pending login code hash + expiry; `last_seen_at` |
| `reviewer_tokens` | One per browser, SHA-256 hash only |
| `feedback` | ULID; `body`, `url`, `path` (for "open on this page"), `status` open/done; `context`, `element`, `console`, `network` JSON; `resolved_at`, `resolved_by_id`, `resolution_note` |
| `screenshots` | ULID; `path`, `mime`, `width`, `height`, `size`, `kind` (auto/manual), `position` |
| `personal_access_tokens` | Sanctum; MCP tokens carry the `mcp` ability |

All public ids are ULIDs (widget, dashboard URLs, MCP).

## MCP

`RoxyServer` with `list_sites`, `list_feedback`, `get_feedback`, `resolve_feedback` (with a note for the client), `discard_feedback`. The server instructions describe the workflow: check the repository, URL path + selector → find the code → fix → resolve with a note. `get_feedback` returns screenshots as images scaled to ≤ 1568 px: larger images cost tokens without showing the model more.

Not yet: scoping a token to a single site (`site_id` on the token).

## Hostpoint notes

- Queue: `database`. Cron every minute: `php artisan schedule:run` plus `php artisan queue:work --stop-when-empty --max-time=50`. Mails (invites, new feedback) are queued.
- Inertia SSR is off (`config/inertia.php`). There's no Node on the server; `public/build` is built locally and committed, as in the other kimiyu repos.
- Imagick: re-check that it's enabled for the PHP version chosen in the Hostpoint panel.

## Widget

`resources/widget/`, plain JavaScript, built by `vite.widget.config.js` (`npm run build:widget`, part of `npm run build`) into two classic scripts in `public/`:

- `widget.js` (≈ 10 KB gzipped): boots from `data-site`, finds Roxy's origin from its own `src`, renders into a `<roxy-widget>` shadow root (plain CSS in `styles.css`, no Tailwind: its `@property` variables don't work in shadow DOM).
- `widget-screenshot.js` (≈ 10 KB gzipped, `modern-screenshot`): only loaded when a reviewer takes the first screenshot. Takes the viewport (`translate(-scroll)` on the root, the visible area without scrollbars, the same box as `element.rect`), at most 2× and 2560 px, without Roxy itself. Taken on demand ("Screenshot" button, replaces the previous one); the picked element is measured again at that moment and the screenshot is retaken after a new pick, so `element.rect` always matches it.

Behaviour:

- Caching: `widget.js` keeps its address, so `public/.htaccess` (Apache, Hostpoint) lets browsers keep it for 5 minutes, then revalidate. Locally (Herd / nginx) there's no such header: hard-reload after a widget build.
- Visitors without a token get nothing: no button, no capture, no requests beyond loading the script.
- `#roxy=CODE` (from the magic link) is removed from the address, traded for a token (localStorage `roxy:<site key>`), and the panel opens.
- With a token, console errors (`console.error`, `error`, `unhandledrejection`) and failed requests (`fetch`, XHR, failed scripts / images; query strings dropped; Roxy's own calls ignored) are recorded from load, 20 each. Capture pauses while the screenshot runs.
- `?feedback` in the address without a token shows the sign-in form (email → `POST /link`).
- The panel: what's open on this page (all reviewers), text, "Screenshot" and "Upload image" buttons (images also by drop or paste; ≤ 5, fitted to 2560 px), "Point at it" (outline in the shadow root, Esc cancels), Send (⌘/Ctrl + Enter). The automatic screenshot is always uploaded first.
- Requests without an `Origin` header (same-origin, non-browser) pass `WidgetCors` without CORS headers; the token is what protects the API.
- Selectors skip utility classes (Tailwind and the like) and state classes, and walk up to an id, a landmark (`main`, `header`, `nav`, `section`, …) or four steps; a click on an icon counts as its button or link.
- Texts in `strings.js`: German (Swiss spelling, "Sie") when `<html lang>` or the browser says `de`, English otherwise.
- Try it locally on `https://roxy.web.test/widget-test` (local only): a stand-in client page with a console error and a failed request. It needs a site named "Widget test" with that start URL; invite yourself with `roxy:invite` and open the link.

## Look and feel

The landing page (`resources/views/home.blade.php`) draws the widget and the dashboard as mocks; those are the design reference for building them.

- **Widget** (`components/site-window.blade.php`): a round button in the galah rose (`galah-600`), bottom right; above it a white panel. From top to bottom: header (Roxy icon, “Feedback on this page”, who's signed in), a hint with what's already open on the page, the text field, then the attachments: screenshot thumbnail, the picked element (`pin` colour, monospace selector) and “page, browser and errors added”. Footer: “Add image” and Send. The picked element on the page gets a dashed `pin-500` outline and the pin from the icon above it.
- **Dashboard** (`components/dashboard-window.blade.php`): three columns. Sidebar on `roxy-50`: Roxy, nav (Feedback with open count, Sites, Reviewers, Agent tokens), then the sites with a colour dot and open count. The list: Open/Done switch, rows with reviewer · site + path · time, two lines of text, chips for screenshots, element and errors; the selected row has a `roxy-500` bar. The detail on the right: status pill, text, screenshot with the element outlined, a small Page/Element/Browser/Console list, and at the bottom the note field next to Done (`roxy-600`).
- In the app: `layouts/AppLayout.vue` (sidebar; the sites with open counts are a shared Inertia prop), `pages/feedback/Index.vue` (list + detail on one page: `/app/feedback` and `/app/feedback/{id}` render the same page, the filters stay in the query string), `components/FeedbackDetail.vue`. The element outline on the screenshot needs the widget to send `element.rect` in viewport coordinates, relative to the automatic screenshot.
- Colours: `roxy` (sky) for the dashboard and the site, `galah` for the widget and accents, `pin` only for picked elements. Type: Inter, headings in Figtree (`font-display`).

## Next

See `docs/progress.md`.
