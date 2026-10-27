# Progress

Where Roxy stands, for picking up in a new session. Update it at the end of each session: move things from Next to Done, note decisions in `docs/architecture.md`.

## Done

### 2026-10-27

- **Backend**: widget API (`/api/widget/{site key}`, per-site CORS, reviewer tokens), magic-link login for reviewers, feedback with screenshots (Imagick re-encode), MCP server at `/mcp` with `list_sites`, `list_feedback`, `get_feedback`, `resolve_feedback`, `discard_feedback`, queued emails. 20 feature tests.
- **Storage**: no Exoscale. Screenshots live on the `local` disk (`storage/app/private`) on the Hostpoint server and are only handed out as signed temporary URLs.
- **Website**: landing page in the croc.web / wuff.web house style (hero with a mock of the widget on a client site, how it works, MCP section with a Claude Code mock, dashboard mock, features, setup with copyable snippets), `/legal`, Open Graph image (`scripts/og-image.sh`).
- **Dashboard**: the inbox in the look of the landing page mock: sidebar (feedback, sites with open counts), list (Open / Done), detail (screenshot with the picked element outlined, summary, raw console / network / HTML, note + Done, Reopen, discard). Login, forgot and reset password pages restyled to match; sign-out lands on the login page; console / requests / HTML as custom disclosures.
- **Local**: Herd link with SSL at `https://roxy.web.test`. The local database has demo sites (Croc, Alpenblick, Bäckerei Huber) and a few entries.

## Next

1. **`widget.js`**: Shadow DOM, the panel as drawn in `components/site-window.blade.php` (see architecture.md, Look and feel), auto screenshot (lazy `modern-screenshot`), element picker sending `element.rect` in viewport coordinates, console / network capture, `#roxy=` code pickup, open feedback on the page. Separate Vite build to `public/widget.js` at a stable URL.
2. **Dashboard: sites and reviewers** (what it takes to onboard a client): sites (create, edit origins / start page / repository, copy the script tag, button position and colour, delete); reviewers per site (invite, resend the link, last seen, remove = sign out everywhere).
3. **Dashboard: agent tokens**: create with a name, show the `claude mcp add` command once, last used, revoke; maybe scope to one site.
4. **Dashboard: profile**: name, email, password (Fortify's `updatePasswords`), email notifications on / off.
5. **Later**: team (invite colleagues into the account; `accounts` and `role` exist), inbox search, keyboard shortcuts (j / k, d for done).
6. **Deploy**: `deploy.sh` (rsync like croc.web, never touch `storage/`), Hostpoint cron (`schedule:run`, `queue:work --stop-when-empty`), `.env`, check Imagick, `roxy.kimiyu.ch`.

Until the dashboard pages exist: `roxy:user`, `roxy:site`, `roxy:invite`, `roxy:mcp-token`.

## Open questions

- Licence: croc and wuff are MIT; Roxy has none yet (might become paid).
- Name for the theme colour (`roxy`, sky blue) for kimiyu.web, and adding Roxy to kimiyu.web's app list.
- Notify the reviewer when their feedback is done (the note field says "Note for Anna", so it's implied)?
