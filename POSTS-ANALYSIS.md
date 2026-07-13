# Posts list — functional analysis of core (before layout decisions)

Captured 2026-07-13 against WP 7.0.1 (playground, live probing of edit.php
with real posts in Published/Draft states). Method: walk every state —
table, views, filters, Quick Edit open, Bulk Edit open, Screen Options,
search, pagination. Companion doc to the coming Figma exploration page.
Same rule as SETTINGS-SPEC.md and DASHBOARD-ANALYSIS.md: nothing gets
dropped silently.

## What core's Posts screen actually is

Not just a table — a **status-scoped, filterable, bulk-editable worklist**
with two inline editors hiding inside it:

1. **Views row** (`.subsubsub`) — status scopes with live counts:
   All (2) · Published (1) · Draft (1); Scheduled / Pending / Private /
   Trash / Sticky appear only when non-empty. Mine appears for authors.
   This row is the screen's primary navigation — most users never touch
   the filter dropdowns.
2. **The table** — columns: [checkbox] · Title (sortable) · Author ·
   Categories · Tags · Comments (sortable, icon column) · Date (sortable).
   - Title cell carries **post states** appended to the name: — Draft,
     — Pending, — Sticky, — Password protected, — Scheduled. It's a
     second status system living inside a cell.
   - Date column doubles as meaning: "Published 2026/07/12" vs
     "Last Modified" (drafts) vs "Scheduled" — label changes per status.
   - Row hover reveals **row actions**: Edit · Quick Edit · Trash ·
     Preview/View. In Trash view they become Restore · Delete Permanently.
3. **Quick Edit** (inline, per row) — a full mini-editor replacing the row:
   title, slug, date (5 inputs), author, password OR private, categories
   (checkbox tree), tags (freeform textarea), allow comments, allow pings,
   status, sticky. Update/Cancel. No content editing.
4. **Bulk Edit** (inline, above rows, on N selected) — categories (ADD
   only — cannot remove), tags (add only), author, comments on/off,
   pings on/off, status, format, sticky. The asymmetry (add-only) is a
   core wart users hit constantly.
5. **Bulk actions** — Bulk edit · Move to Trash (Trash view: Restore ·
   Delete permanently + an "Empty Trash" button).
6. **Filter bar** — All dates (month dropdown, built from real post
   months) · All Categories · Filter button. No tag filter (tags filter
   only via clicking a tag link in a row).
7. **Search** — top right, searches title+content, produces
   "Search results for …" state with its own views counts.
8. **Screen Options** — per-user column toggles (Author, Categories,
   Tags, Comments, Date), posts-per-page number, and **two view modes**:
   Compact (list) vs Extended (excerpt under each title).
9. **Pagination** — items count + first/prev/«page x of y»/next/last.
10. **Empty states** — "No posts found." (bare); Trash empty hides the
    Trash view link entirely (views self-prune).

## Extension surface (what plugins do to this screen)

This is the dashboard-widgets problem again, in column form:

- **Columns**: `manage_posts_columns` + `manage_posts_custom_column` —
  SEO plugins add score columns, Woo adds product data on its own list,
  translation plugins add flag columns. Any redesign must render
  UNKNOWN columns acceptably (they arrive as raw th/td HTML).
- **Row actions**: `post_row_actions` filter — plugins append links
  (Duplicate, Clone, SEO analysis…).
- **Views**: `views_edit-post` filter — plugins add scopes.
- **Bulk actions**: `bulk_actions-edit-post` — plugins add operations.
- **Quick/Bulk Edit boxes**: `quick_edit_custom_box` /
  `bulk_edit_custom_box` — plugins inject fields.
- Post states: `display_post_states` — plugins append badges to titles.

## What's genuinely good (keep the physics)

- Views-with-counts as primary nav — one click, self-pruning, readable.
- Quick Edit's promise: fix metadata without leaving the list.
- Date column's status-aware labeling (Published/Modified/Scheduled).
- Sticky + password states being visible at a glance in the title cell.
- Self-pruning UI (empty statuses vanish rather than showing zeros).

## What's core wart (candidates to fix, not copy)

- Post states as appended em-dash text inside the title — should be
  Quire badges (we already have the Scheduled badge on the dashboard).
- Bulk Edit's add-only taxonomy editing with zero feedback about it.
- Comments column as a cryptic speech-bubble icon with a number.
- Two view modes (Compact/Extended) buried in Screen Options — decide
  ONE density (dense & precise is the identity) or make it a visible,
  honest toggle.
- Filter bar ≠ views row: two competing scoping systems with different
  capabilities (status vs date/category). One coherent scoping model?
- Screen Options column toggles — same fate as the dashboard's Screen
  Options: replace with something Quire-native or deliberately drop.
- Quick Edit is powerful but visually a form-bomb (14 fields at once).

## Open decisions for the Figma exploration (R-numbered)

- **R1 — Unknown plugin columns**: render them (bridge style) or hide
  behind a "More" affordance? (Must not silently lose data — Woo/SEO
  columns are load-bearing for real users.)
- **R2 — Post states**: badges in the title cell (dashboard grammar) —
  which states earn color vs neutral?
- **R3 — Quick Edit**: keep-as-inline-form (restyled), reduce to the
  actually-used fields (title/slug/date/status/sticky?), or replace with
  the settings-style drawer? (The drawer is now an established pattern.)
- **R4 — Bulk Edit**: same question + fix or at least LABEL the add-only
  taxonomy behavior.
- **R5 — Scoping model**: views row + separate date/category dropdowns
  (core), or one unified filter bar (dt-toolbar in components.css
  already sketches this)?
- **R6 — Density modes**: one density (which?) or visible toggle;
  excerpt view keep/kill.
- **R7 — Comments column**: keep as count-with-icon, fold into a row
  meta line, or drop from default columns?
- **R8 — Columns per user**: do we keep per-user column choice at all
  (it's the customize-mode question in table form — same auto-save
  grammar as the dashboard if yes)?
- **R9 — Trash**: dedicated view (core) with restore/delete — restyle
  only, or rethink (undo-toast on trash like modern apps)?
- **R10 — Search**: stays a separate box, or joins the unified filter
  bar (dt-search exists in components.css)?

## Existing Quire groundwork to reuse

- `components.css`: the full **DataTable** pattern (dt-toolbar with
  filters + search, sortable headers, row-hover actions, row selection
  as neutral bulk surface, dt-foot pagination) — built for the Orders
  specimen, never yet used on a live screen.
- `apps/docs/specimen/orders.html` — the table grammar explored (Woo
  orders variant).
- Dashboard patterns that carry over: badges for states, the drawer
  (candidate for Quick Edit), auto-saved per-user preferences with
  nonce'd ajax, the width contract (this is a BOARD-width screen, 1280;
  it may become the first legitimate test of the full-width exception
  if plugin columns demand it — decide only when real columns exist).
