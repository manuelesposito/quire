# Dashboard — functional analysis of core (before layout decisions)

Captured 2026-07-10 against WP 7.0.1 (playground, Quire toggled OFF — the kill
switch doing its job). Method: walk every state — empty, Screen Options, all
widgets, Welcome, all four Help tabs, drag targets. Companion to the Figma
exploration page "Explorations — Dashboard" (variants A–D). Same rule as
SETTINGS-SPEC.md: nothing gets dropped silently.

## What core's Dashboard actually is

Not a screen — a **per-user, extensible widget board**:

1. **Widget board mechanics**
   - 4 columns of drop zones ("Drag boxes here"); boxes drag between them
   - Every box collapses/expands; up/down arrows move boxes (keyboard path)
   - **Screen Options** toggles each box per user: Site Health Status,
     At a Glance, Activity, Quick Draft, WordPress Events and News, Welcome
   - Arrangement + visibility persist per user (user meta)
2. **The six core boxes**
   - **Welcome panel** — dismissible onboarding: version headline + three
     task columns (write with blocks / customize with block themes / Styles).
     Reappears per user until dismissed; targets brand-new sites
   - **Site Health Status** — "No information yet…" empty state; else
     good/should-improve + link to site-health.php
   - **At a Glance** — posts / pages / comments counts + "WP x.y running
     {theme}" line (version + theme awareness lives HERE)
   - **Activity** — upcoming SCHEDULED posts, recently published, latest
     comments with inline moderation (hover actions)
   - **Quick Draft** — title + content save-as-draft, PLUS the three most
     recent drafts listed with links
   - **Events and News** — geolocated meetups/WordCamps + Planet feed +
     external links row
3. **Extensibility (the load-bearing wall)** — plugins register boxes via
   `wp_add_dashboard_widget()` (Woo status, SEO overviews, forms, analytics…).
   For many plugins this is their primary surface.
4. **Contextual Help tab** — four tabs (Overview / Navigation / Layout /
   Content) describing the box mechanics; per-screen core mechanism
5. **Role awareness** — boxes and counts filter by capability (Quick Draft
   needs edit_posts; subscribers see almost nothing)

## Audit: what Camp 1's Quire dashboard does against this today

| Core capability | Quire today | Verdict |
|---|---|---|
| At a Glance counts | ✅ richer (4 stat tiles incl. media, deltas, links) | keep |
| Recently published | ✅ card | keep |
| Comment moderation | ✅ card with real actions | keep |
| Quick Draft (save) | ✅ works (verified) | keep |
| Quick Draft recent-drafts list | ❌ missing | add |
| Scheduled posts (Activity) | ❌ missing | add |
| Site Health | ✅ card incl. empty state | keep |
| Welcome/onboarding for new sites | ❌ gone | decide |
| Events and News | ❌ gone | decide |
| **Plugin dashboard widgets** | ❌ `#dashboard-widgets-wrap{display:none}` — **silent functionality loss**, plugins' boxes unreachable | **fix — non-negotiable** |
| Screen Options show/hide per user | ❌ gone | decide |
| Drag arrangement / collapse | ❌ gone | decide (likely drop: opinionated layout is the point) |
| Help tab | ⚠️ still renders but describes boxes that no longer exist | refresh or suppress |
| Theme/version line | ❌ gone (updates notice covers version partially) | fold into Site health card |

## Requirements this feeds into the layout decision (variants A–D)

- **R1 — plugin widgets need a home.** Every variant must include a
  "From your plugins" region rendering foreign `wp_add_dashboard_widget`
  boxes (the dashboard sibling of the settings screen's plugin card).
  This is the strongest argument against the most minimal variant D
  *as drawn* — D needs a place for them too.
- **R2 — scheduled posts** join the published list ("Publishing next" or a
  merged Activity reading).
- **R3 — recent drafts** listed under Quick Draft (with the count already
  shown in the Posts stat delta).
- **R4 — first-run state.** A brand-new site (0 posts, no icon, default
  tagline) needs a designed welcome moment — this is where variant D's
  greeting language naturally covers core's Welcome panel role.
- **R5 — personalization scope (DECISION).** Options: none (one opinionated
  calm layout — Quire's thesis), or show/hide only (Screen-Options parity
  without drag). Recommendation: none in v1; revisit on real feedback.
- **R6 — Events & News (DECISION).** Conscious drop, demote to a link, or
  keep as an optional card. Recommendation: drop from the default view —
  it is WordPress marketing surface, not site work; the philosophy says
  the dashboard serves the site owner's site.
- **R7 — role awareness**: cards render by capability (moderation card only
  for moderate_comments, Quick Draft for edit_posts…).
- **R8 — Help tab** content rewritten for the Quire dashboard (or the tab
  suppressed on this screen until the shell camp owns help).
