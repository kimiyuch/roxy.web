# Feedback Widget - Plan

Working title: tbd. A drop-in script that lets clients and reviewers leave feedback on any website, with screenshots and page context, in a form a coding agent can read and act on.

Origin: the internal feedback module built for Nightnurse Project Room (Oct 2026): nav button, popup, screenshots (pick / drop / paste), open/done triage, and MCP tools so Claude Code can read entries incl. screenshots and resolve them.

---

## 1. Positioning

**Not** "another visual feedback tool". That market is crowded and polished:
Marker.io, BugHerd, Usersnap, Userback, Feedbucket, Ruttl, Pastel (check what each currently offers for AI / MCP before committing).

**The angle:** client feedback your coding agent can fix.

- Feedback arrives with everything an agent needs: screenshot, URL, viewport, the clicked element (selector + DOM snippet), console errors, failed requests.
- An MCP server exposes it, so the loop is: client clicks -> agent reads -> agent fixes -> agent resolves.
- One-line pitch: "Your client points at it. Claude fixes it."

## 2. Strategy

1. **Own tool first.** Build it for my own projects (Jamon.digital sites, Statamic / immosite builds, Laravel apps) during staging and client review.
2. **Prove it.** If it changes how client feedback gets handled across 3-5 real projects, that's the signal.
3. **Then SaaS.** Multi-tenant, billing, onboarding. Most of the core is already multi-site from day one, so this step is mostly business plumbing.

Nightnurse keeps its built-in module for now; it could later be swapped onto this.

---

## 3. MVP scope

### In

- Script tag install on any site
- Floating button (position configurable), popup with text + screenshots
- Screenshots: auto-capture of the current viewport + manual pick / drop / paste
- Element picker: click the problem, store selector + text + bounding box
- Auto context: URL, page title, viewport, device pixel ratio, user agent, timestamp
- Console errors (last ~20) and failed fetch / XHR requests captured since page load
- Reviewer access only (no public visitors), via magic link
- Dashboard: list per site, filter open / done, detail view with screenshot + context, resolve, discard
- MCP endpoint per account: list / get (with images) / resolve / discard
- Email notification to site owner on new feedback (digest optional)

### Out (later)

- GitHub / Linear / Jira issue sync
- Comment threads / replies to the reviewer
- Annotating on top of the screenshot (arrows, boxes) - Nightnurse markup tool could be reused
- Session replay
- Billing, teams, roles beyond owner + reviewer
- Framework component detection (Vue / React component name)

---

## 4. Architecture

```
 Client site                         Backend (Laravel)                 Agent
 -----------                         -----------------                 -----
 <script widget.js> ---- POST ---->  API: entries, screenshots   <---- MCP (/mcp, Bearer token)
   Shadow DOM UI                     Dashboard (SPA)                    Claude Code / Cursor
   capture + picker                  S3 / Exoscale storage
```

### Widget (JS)

- Vanilla TS, bundled to one small file (target < 40 KB gz, screenshot lib lazy-loaded on open)
- Renders inside a **Shadow DOM** so host CSS can't break it and it can't break the host
- Install:
  ```html
  <script src="https://cdn.example.com/widget.js" data-site="SITE_KEY" defer></script>
  ```
- Optional JS API for apps with their own auth:
  ```js
  FeedbackWidget.identify({ id, name, email, signature }) // HMAC-signed by the host app's backend
  FeedbackWidget.open()
  ```
- Captures:
  - Screenshot: `modern-screenshot` (DOM -> canvas) by default; fallback / "pixel-perfect" mode via `getDisplayMedia` (asks permission)
  - Element picker: hover outline, click -> unique CSS selector, `outerHTML` (truncated), text, bounding rect, scroll position
  - Console: wrap `console.error` + `window.onerror` + `unhandledrejection`, ring buffer
  - Network: wrap `fetch` / `XMLHttpRequest`, keep failed (status >= 400 / network error) with method, URL, status
- Uploads: multipart POST, or presigned upload for large screenshots
- Must not break the host: try/catch everything, no globals besides `FeedbackWidget`, no-op if blocked

### Who sees the widget

Public visitors must never see it. Options (MVP = A, C later):

- **A. Magic link:** owner invites reviewers by email -> link sets a token in localStorage for that site -> widget shows. `?feedback` on any URL also prompts for email login.
- **B. Staging only:** `data-only-hosts="staging.example.ch"`
- **C. Identify:** host app calls `identify()` with an HMAC signature (like Intercom), no separate login.

### Backend (Laravel)

Reuse patterns from Nightnurse: Actions, Policies, Resources, Imagick re-encode for screenshots, presigned S3 GETs, laravel/mcp.

Data model (first cut):

| Table | Key fields |
|---|---|
| `accounts` | name, plan |
| `users` | account_id, role (owner / member) |
| `sites` | account_id, name, public key, allowed origins, settings (button position, colour) |
| `reviewers` | site_id, email, name, token hash, last_seen_at |
| `entries` | site_id, reviewer_id, body, url, page_title, viewport, user_agent, element (json), console (json), network (json), status, resolved_at, resolved_by |
| `screenshots` | entry_id, path, width, height, kind (auto / manual) |
| `mcp_tokens` | Sanctum PATs scoped to account (+ optional site) |

Security:

- CORS: widget API only accepts requests from the site's allowed origins
- Site key is public; writes need a reviewer token (or signed identify)
- Rate limit per site + per reviewer
- Screenshots re-encoded (strip metadata, cap size), served only via short-lived signed URLs
- Element / console / network data truncated + sanitised (no cookies, no auth headers, mask password inputs)

### MCP tools

| Tool | Purpose |
|---|---|
| `list_sites` | sites the token can reach |
| `list_feedback` | filter by site, status, date; summary incl. url + element selector |
| `get_feedback` | full entry: text, context, element, console, network + screenshots as images |
| `resolve_feedback` | mark done, optional note to reviewer |
| `discard_feedback` | delete |
| `add_note` (later) | agent writes what it changed |

Server instructions should explain the agent workflow: locate the code via URL + selector, fix, then resolve with a short note.

---

## 5. Phases

1. **Spike (1-2 days)**
   Widget in Shadow DOM, text + auto screenshot, POST to a Laravel endpoint, list in a plain Blade page. Test on one real site.
2. **MVP (1-2 weeks)**
   Element picker, console / network capture, magic-link reviewers, dashboard with filters + detail, MCP tools, email notification. Dogfood on 2-3 client projects.
3. **Polish**
   Screenshot annotation (reuse Nightnurse markup), reviewer replies ("fixed, please check"), GitHub issue sync, `identify()`.
4. **SaaS (only if phase 2 proves itself)**
   Multi-tenant signup, billing (Stripe / Paddle), landing page, docs, pricing per site / seat.

---

## 6. Open questions

- Name + domain
- Hosting: same Cyon + Exoscale setup, or Laravel Cloud for the SaaS step?
- Screenshot approach: is DOM-to-canvas good enough on real client sites (web fonts, iframes, cross-origin images), or default to `getDisplayMedia`?
- Should reviewers see each other's feedback (avoid duplicates) or only their own?
- Notify the reviewer when their entry is resolved? (Nice closure for clients.)
- Data residency: Swiss / EU storage as a selling point for Swiss clients?
- Pricing model, if it goes SaaS: per site, per seat, or flat per agency?

---

## 7. Reuse from Nightnurse

- `app/Actions/Feedback/*` - create / resolve / delete, Imagick screenshot re-encode
- `app/Mcp/Tools/*Feedback*` - MCP tool shapes, `Response::fromStorage` for images
- `components/feedback/ScreenshotInput.vue` - pick / drop / paste logic (port to vanilla TS)
- Markup tool (`components/projects/images/*`) - later, for screenshot annotation
