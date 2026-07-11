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
- **R5 — personalization scope — REVERSED 2026-07-11: FULL drag/remove/add
  IS wanted, redesigned in Quire's language.** (Superseded the earlier
  same-day "none in v1" call — Manuel: "we should keep the idea that the
  desktop is customizable... a site with WooCommerce installed should be
  able to place Woo widgets on the desktop too.") Explored in Figma page
  "Explorations — Dashboard Customization" (file JD565ifTjv534gnI62lNzS,
  page 123:2), three frames:
  - **H1 — calm default**: a "Customize" button in the topbar; at rest,
    zero drag chrome (same contextual-reveal grammar as the settings save
    bar). Granularity note: bringing back per-widget remove/add means
    widgets can't be MERGED the way variant G tried (comments+scheduled
    in one card) — each widget must be one independently addable/removable
    concept, pulling structure back toward variant A's granularity.
  - **H2 — customize mode**: every widget gets a small grip handle (drag,
    not whole-card — cards have live Approve/Edit/View links inside that
    must stay clickable) + a remove (×); a dashed "+ Add widget" slot ends
    each column; button becomes "Done"; one widget shown mid-drag with a
    dashed drop-target ghost showing where it'll land.
  - **H3 — the widget picker** ("selection area"): a right-hand drawer,
    grouped by SOURCE — a "Quire" section (Overview, Needs your eye,
    Recently published, Quick draft, Site health, WordPress news & events)
    then one section PER ACTIVE PLUGIN, computed from what's actually
    installed (WooCommerce section only rendered because Woo is active in
    this scenario; a generic "Other plugins" section for anything else
    registered via `wp_add_dashboard_widget`). Each row: name + one-line
    description + Remove (if added) or +Add (if not).
  Other decisions made in the same brainstorm: two columns (main+side),
  matching the same two-column body convention as Settings J2 — reuse, not
  reinvent; auto-saves per drop (no separate save step, matches core);
  removing a widget only hides it (never deletes data), reversible via the
  picker. NOT YET BUILT — Figma-only; still needs: persistence schema
  (user meta, versioned so the widget set can evolve), what happens when a
  column is emptied (calm empty state, not blank), whether >2 columns are
  ever needed for very widget-heavy Woo sites (open question, no evidence
  yet either way).

  **Size classes (2026-07-11, same session):** not every widget fits a
  small slot. Dense-row widgets (comment/post lists with inline actions,
  stat strips) are FULL-WIDTH ONLY — a half-width slot would clip or wrap
  their action links. Sparse widgets (Site health's one line) waste space
  at full width — COMPACT, paired two-up on one row. Each widget has ONE
  fixed size class, not user-resizable (avoids building a real resize
  engine core doesn't have either). Full: Overview, Needs your eye,
  Recently published, any Woo order/status list. Compact: Site health,
  a trimmed News & events, simple one-line plugin stats (SEO overview).
  **News & events redesigned smaller to fit Compact**: dropped core's
  location-picker CTA and the extra external-link footer row (happy talk,
  cut per DESIGN.md) — kept to 3 headlines + "See all", now genuinely
  compact instead of needing full width. Demonstrated in H1/H2: Site
  health + News & events sit side by side as a compact pair in the main
  column.
- **R6 — Events & News — DECIDED 2026-07-11: DEMOTED TO A LINK.** No box;
  a quiet "WordPress news & events" entry in the links row/footer keeps
  community discovery one click away without occupying the room.
- **R7 — role awareness**: cards render by capability (moderation card only
  for moderate_comments, Quick Draft for edit_posts…).
- **R8 — Help tab** content rewritten for the Quire dashboard (or the tab
  suppressed on this screen until the shell camp owns help).


## Widget library (2026-07-11) — every core widget redesigned, state by state

Figma page "Dashboard — Widget Library" (138:2). All six core boxes, each with
its full state set, all token-pure. Rules established while designing them:

- **Edit chrome lives on the title line** — grip left of the title, × far
  right, centered on that one line, never wrapped around a multi-line header.
- **One removal affordance at a time** — a widget's own dismiss (only
  Welcome has one) yields to the standard × in customize mode.
- Plugin widgets get chrome only; their body content belongs to the plugin.

| Widget | Size | States designed |
|---|---|---|
| Welcome (setup checklist) | Full | mid-progress (progress bar, hint lines, chevron links), all-done ("You're all set" + explicit Dismiss — acknowledged, not auto-vanished), customize. Steps are CORE-MAPPED and machine-detectable: theme chosen, title/tagline set (≠ defaults), first post written, site icon set, styles edited. Replaces core's static Welcome banner with the same jobs as a living checklist. |
| Site health | Compact | good (green dot), attention (amber dot, "3 items to look at" + "See what they are"), never-checked ("Run the first check"), customize |
| Overview (At a Glance) | Full | established, brand-new site (honest sample-content numbers, no fake encouragement — Welcome owns nudging), customize. Comments count restored (earlier mock dropped it); version+theme caption lives here. |
| Needs your eye | Full | with items (author, quote, ago, Approve/Spam/Trash), all-clear (green dot + "new comments land here first"), customize |
| Publishing | Full | scheduled(chip)+published mix, empty ("first post will appear here"), customize |
| Quick draft | Compact-ish (side column) | REST = single title line ("Catch an idea before it goes…") + recent drafts list; TYPING = focus ring, content area + Discard/Save revealed (the confirmed collapse-expand interaction); customize |
| News & events | Compact | 3 headlines (nearby event emphasized over project news), feed-unreachable ("News can't be loaded right now. It will refresh on its own." — quiet, never an error tone for non-essential content), customize |


**Grid decision (2026-07-11): TWO EQUAL COLUMNS.** Not core's 4 (at 1220px
content that's ~290/col — only compact widgets survive; variant C proved
dense rows truncate), and not the earlier asymmetric main+320 side (free
drag into a fixed narrow column crushes Full-class widgets — the H2 mock
itself had this bug). Two equal ~590px columns mean ANY widget drops in
ANY column; compacts subdivide a column two-up; stacks to one column
<1100px (same breakpoint rule as Settings). Same two-column grammar as
the Settings body — one rhythm across the product. Customize-entry
decision, same session: explicit "Customize" button → all handles at
once → "Done" (modern platform practice: iOS jiggle mode, macOS/Windows
widget editors, Shopify Analytics customize; core's hover-drag 4-arrow
is the outlier and causes accidental drags — no undo). Vercel has no
arrangeable dashboard at all; Shopify Home is fixed, its Analytics uses
an explicit customize mode.
