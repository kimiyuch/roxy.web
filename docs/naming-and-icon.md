# Roxy - naming and icon

Notes from the first session (2026-10-27), where the name and the icon were decided. The product plan is in `plan.md` (written before the name, so it still says "Feedback Widget" / "tbd").

## Decision

- **Name: Roxy**
- **Mascot: a galah** (pink-and-grey cockatoo, a.k.a. rose-breasted cockatoo) peeking over a browser window
- **Tagline idea:** *"Tell Roxy. Claude fixes it."* (from the plan's "Your client points at it. Claude fixes it.")
- **Domain idea:** `roxy.kimiyu.ch`
- **JS API idea:** `Roxy.open()`, `Roxy.identify()`; MCP tool names can stay generic (`list_feedback`, `resolve_feedback`, ...)

## House style (kimiyu apps)

Same family as https://github.com/kimiyuch (Joey, Bilby, Wallaby, Wombat, Croc, Wuff, ...):

- An Australian animal peeking over the thing the app is about, paws (here: feet) gripping its top edge
- The kimiyu studio bear (`artwork/kimiyu-bear.svg`, from kimiyu.web) shown on that thing
- Vertical gradient on a macOS-style rounded square, soft drop shadows, flat shapes with gentle gradients
- Drawn as hand-written SVG in `scripts/make-icon.mjs`, rendered with headless Chrome, no npm packages (same setup as `croc.app/scripts/make-icon.mjs`)
- Each app has a theme colour in `kimiyu.web/resources/css/app.css` (`gum`, `billabong`, `burrow`, ...). Roxy's is still to be named, see palette below.
- App sites are single-page Laravel 13 + Tailwind 4, deployed to Hostpoint via `deploy.sh`, live at `<name>.kimiyu.ch`

## The icon

The browser window shows the faded kimiyu bear as the page's hero image, picked out with a dashed red outline and an orange feedback pin. That's the element picker: the client points at something on the page.

Palette:

| Use | Colours |
|---|---|
| Sky background (theme colour candidate) | `#dcebf8` -> `#a9c8e8` -> `#6b97c9`, tile `#9fc0e3` |
| Galah rose (face) | `#f08aa0` -> `#d34f72` |
| Galah cap / crest | `#f9d9e0`, `#f7cdd7` |
| Wings / feet | `#b3b8c0` -> `#868c95`, `#8e939b` |
| Beak | `#f4ede2` -> `#cfc4b3` |
| Feedback pin / outline | `#ff7a59` -> `#e5482a` |

Possible small polish later: the pin slightly touches the left foot; the window could be a bit larger to match how much room the objects take in sibling icons.

## How we got here

Animals tried (drafts in `artwork/explorations/`, script `scripts/make-icons-explorations.mjs`):

- **Numbat**: eats ~20,000 termites a day, so "eats bugs". Teal background. Dropped in favour of the galah look.
- **Quokka**: the "selfie animal" (screenshots). Dropped: in this flat style it looked like a bear and clashed with the kimiyu bear.
- **Galah** (blue/purple sky): chosen.

Names considered for the galah:

- **Galah**: Aussie slang for a fool, and the user wasn't sure about it.
- **Perch** (sits on the edge of your site): already the name of an existing CMS for web agencies, too close to the audience.
- **Squawk** (named after the call, like Wuff): hard to spell for non-native English speakers.
- **Rosie** (rose-breasted cockatoo): easy, friendly, but very common.
- **Pip**: clashes with Python's package manager. **Gala**: reads as "party". **Coco**: no link.
- **Roxy**: chosen. Easy to spell in German, French, Italian and English; more distinctive than Rosie; sounds like *rosy*; and like *proxy*, since it passes messages between the client and the agent. Note: Roxy is also a surf/fashion brand (Quiksilver) and a common club/cinema name. Different field, but generic "Roxy" won't rank first on Google.

## Next steps

1. Name Roxy's theme colour (sky blue) for kimiyu.web
2. Update `plan.md` with the name
3. Start the spike (plan section 5, phase 1): widget in Shadow DOM, text + auto screenshot, POST to a Laravel endpoint, list in a plain Blade page
