# Shell — the menu-classification analysis (before the rail is real)

Captured 2026-07-13 against the playground (WP 7.0.1 + WooCommerce 10.9.4
active) by reading the real rendered `#adminmenu`. Companion to the coming
Figma exploration. Same rule as every camp: nothing gets dropped silently —
the shell replaces core's chrome, so every item it carries must survive.

## What the shell replaces

1. **The admin menu** (`#adminmenu`) — one flat list of top-levels +
   submenus. Every plugin appends here via `add_menu_page` /
   `add_submenu_page`. Collapse state per user. Current-item highlighting
   driven by core per screen.
2. **The admin bar** (`#wpadminbar`) — site name (→ visit site), updates
   badge, comments badge, "+ New", search, the user account menu, plus
   plugin nodes (Woo adds a "Store coming soon" badge today).
3. Their behaviors: per-user collapse, keyboard access, responsive
   folding, the current-screen highlight on EVERY admin page — including
   the ~95% of screens that stay Lane-2 classic under the shell.

## The real menu with one plugin active (the inventory)

**Core's ten:** Dashboard · Posts · Media · Pages · Comments · Appearance
· Plugins · Users · Tools · Settings.

**WooCommerce adds FIVE top-levels, interleaved among them:**
- WooCommerce (Home · Orders · Customers · Coupons(moved) · Reports ·
  Settings · Status · Extensions)
- Products (All · Add · Brands · Categories · Tags · Attributes · Reviews)
- Payments (→ a settings tab)
- Analytics (11 sub-items)
- Marketing (Overview · Coupons)

**And it grafts INTO core menus:** "Scheduled Actions" appears under
core's Tools. Lesson: classification is not just top-levels — submenu
grafts exist and must ride with wherever their parent lands.

The flat list with Woo active is 15 top-levels. Add an SEO plugin, a
form plugin, a cache plugin and it's 20+. This interleaving is the
single biggest reason wp-admin reads as chaos — and the rail's whole
argument.

## The classification model (proposal)

**Product manifests.** A product = a rail entry + the list of menu
slugs it owns:

- **WordPress** (always first, the default): the core ten.
- **WooCommerce** (because active): exactly its five observed
  top-levels — `wc-admin`, `edit.php?post_type=product`, Payments,
  Analytics, Marketing. Manifest keyed on slugs, not labels
  (translations break labels).
- **Jetpack** (when active): its own menu tree.
- Manifests live in Quire for KNOWN products (we style what we know —
  same philosophy as the per-product CSS bridges).

**The fallback — unknown plugins.** Anything not claimed by a manifest
lands in ONE rail entry (working name: the "Plugins" tray, drawn as the
"+"-adjacent slot in the nav model). Its sidebar lists each unknown
plugin's items as its own section. No plugin is ever unreachable — the
dashboard-widgets rule, applied to navigation.

**Grafts follow their parent menu.** Scheduled Actions registered under
Tools → appears under WordPress → Tools, even though Woo put it there.
The registration location is the plugin author's stated intent; we honor
it.

## Admin bar mapping

- Site name / visit site → the crumb above every screen title (built).
- "+ New" → each screen's primary action (built); a global New lives in
  the future ⌘K palette, not chrome.
- Account/Howdy → the rail's bottom avatar dot (drawn in nav.html).
- Updates badge → R-decision (candidate: a quiet dot on the WordPress
  rail square; count lives on Dashboard → Updates).
- Comments badge → already covered by Needs your eye + views counts.
- Plugin admin-bar nodes (Woo's "coming soon") → R-decision; candidate:
  the store-overview widget already says it — chrome need not.

## Open decisions for the Figma exploration (R-numbered)

- **R1 — Unknown-plugin fallback shape**: one "Plugins" tray entry
  (recommended) vs a rail square per unknown plugin (rail explosion) vs
  merging into the WordPress sidebar (defeats the product idea).
- **R2 — Rail order & overflow**: WordPress first, then active products
  by what? (activation order / alphabetical / user-arrangeable like the
  dashboard?). What happens at 6+ products.
- **R3 — Updates + notification badges** in a chrome that hates badges.
- **R4 — The account menu** (rail dot): what it contains (profile, log
  out, ...core's Howdy menu inventory).
- **R5 — Classic screens under the shell**: Lane-2 pages render in the
  content area with the shell around them — confirm nothing (notices,
  Screen Options, Help) breaks; the shell must not touch page content.
- **R6 — Collapse / focus mode**: rail-only state (nav.html has the
  toggle); does it persist per user like core's collapse.
- **R7 — Current-item highlight on classic screens**: mapping every
  wp-admin URL back to a sidebar item (core does this with a parent/
  submenu file system — reuse `$parent_file` resolution).
- **R8 — Search / ⌘K**: the admin-bar search dies; the palette
  (component exists) becomes the finder — in shell scope or after?
- **R9 — Responsive**: rail+sidebar on small screens (core folds to
  icons <960px, sheet <782px).
- **R10 — The "+" square**: add-product affordance — what it opens
  (plugin install? the tray?).

## What already exists to build on

- The visual pattern: `nav.html` specimen (rail + sidebar + tooltips +
  collapse toggle), tokens (`--qr-size-rail` 80 / `--qr-size-sidebar`
  380), the proper rail with real product marks in Figma (W1/W3 frames).
- The decided settings pattern (J2) already assumes the shell: its
  sidebar takeover replaces the WordPress context when you enter
  Settings.
- Data source: `global $menu, $submenu` (slugs, caps, positions,
  badges) — everything the classifier needs, capability-filtered by
  core before we read it.

## Suggested sequence

1. Figma: the WordPress context sidebar mapped 1:1 from the core ten
   (+ graft handling), the WooCommerce context from its manifest, the
   unknown-plugin tray — three frames + a states frame (collapse,
   current-item, badges).
2. Decide R1/R2/R3 on canvas.
3. Build Lane 2.5: render the shell around ALL admin screens (hide core
   menu + admin bar via CSS, render rail+sidebar from the live $menu
   globals), Quire screens first-class, classic screens framed.
4. Only then: retire the crumb workaround and per-screen chrome hacks
   that existed because the shell didn't.
