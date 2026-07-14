# Changelog

All notable changes to Quire are documented here. The format is based on
[Keep a Changelog](https://keepachangelog.com/), and the project follows
[Semantic Versioning](https://semver.org/) once it reaches a published release.

## [Unreleased]

### Changed
- **D6 first-review fixes** (Manuel's four findings, 2026-07-14, shell 0.3.1):
  (1) core's blue link states are dead in the shell — every shell link now states its ink
  in hover/focus/active (core's `a:hover` outranks a lone class, which is why the site name
  and nameplate turned blue); (2) the NAMEPLATE is now the way BACK — clicking
  WordPress/WooCommerce/Jetpack closes the product and returns to the main menu, and
  hovering slides a back chevron in over the product's mark to say so first (it previously
  linked to its own front door, i.e. clicked into nothing); (3) collapsed tooltips no
  longer bury behind the content — the scrolling menu clips its overflow, so shell.js lifts
  each tooltip to window coordinates on approach; (4) the second level wears the column's
  row clothes — height `control-lg` (40) and gap `space-100` (4), matching L1 (two menus
  side by side at one type size shouldn't have two row heights; width, indent and icons
  already tell the levels apart). Figma pattern page + rulebook synced the same day.
- **The second level never moves** (Manuel's call, 2026-07-14, shell 0.3.2): the L2
  column now pins at `size-band` (60) instead of the window's top, so its first row stays
  on one line with the column's first row through any scroll — both navigations stand
  still, only the page moves. The 60px quiet zone above it after scrolling mirrors the
  masthead zone (the shelf survives the scroll); long lists scroll within themselves.
  Verified live: first rows aligned at 84 from the top at rest AND at scrollY 600.
- **The warm palette** (decided 2026-07-14, evolving the same day's "warm paper"): the
  entire 10-step gray ladder warms by one rule (blue channel −~2%, identical structure) —
  from `background-100` #fdfcfa down to ink `gray-1000` #141312. Selected pills, borders,
  text grays, and surfaces all share one temperature; hover washes are black-alpha and
  inherit warmth for free. The interim surface literals dissolved back into pure aliases
  (`nav` = gray-100, one whisper above `canvas` = gray-200 — the two-papers relationship).
  The contract keeps its structure and gains a temperature. Dark mode unchanged.
- **Page titles calm down** (decided 2026-07-14): the `text.page-title` role remaps from
  `font.size.800` (32) to `font.size.600` (21) — the Shopify/Vercel register, chosen from a
  32/26/21 side-by-side. Changed at the token source and mirrored everywhere; the band and
  the legacy `.pageheader__title` now consume the ROLE tokens instead of reaching for the
  raw size, so widget stat numbers (also 32) correctly stay big. Watch-item on record: if
  21 proves too quiet as a landmark, the fallback is 26.
- **One continuous line** (decided 2026-07-14): `--qr-size-band` 112 → 60 — the band was a
  room sized for the retired 32px title. At 60 it matches the (coming) masthead height by
  intent, so the top of the screen reads as one shelf with a single hairline. The equality
  is the rule: if one changes, both change.
- **The warm ladder** (decided 2026-07-13): the corner radius scale warms one register —
  `radius-sm` 6→8, `radius-md` 12→16, `radius-lg` 16→24, giving a pure-doubling scale of
  4 / 8 / 16 / 24. The semantic roles (`radius-control`, `radius-card`, `radius-pill`) are
  unchanged, so every control and card across the product follows the tokens. Changed at
  the source (`packages/tokens/src/primitive/dimension.json`), rebuilt to `dist`, mirrored
  to the plugin's `variables.css` and the Figma variables. Two stray hardcoded radii (the
  settings save cluster's 8px, the shell tooltip's 8px) now reference `radius-control`.

### Added
- **D6 — the one-column shell** (built live 2026-07-14 from the "Pattern · Shell D6 —
  pixel-perfect" Figma page; supersedes the two-column rail+sidebar below): THE SITE LEVEL —
  `index.php` and `update-core.php` classify as the site's own product, so the dashboard is
  the site's lobby (Home + Updates, the update dot) above the products, not WordPress's.
  ONE COLUMN (`size-sidebar` 300): the masthead (the site's name in Lora — the ONE serif
  moment, shared with the login greeting; it IS the way home; fills and truncates), the
  current level's menu, the one divider, product rows at the site level (mark + name + › —
  each TRAVELS to its front door), the nameplate inside a product (mark 20 + name, no
  chevron — the way up is the masthead). The column is STATELESS: every row a real link,
  the page defines the menu, the browser's Back button remembers, changes are instant.
  COLLAPSE: the column narrows to `size-rail` 80 — same surface, border, divider; only the
  words leave; tooltips (translucent ink, the one spec) speak; at most one house on screen.
  THE BAND: 60 (= `size-masthead`, one optical shelf), no hairline, no context line; title
  = the page-title role; "View site" joins the Home band (out of the account popover — one
  thing, one place). Lora bundled (7 subsets + OFL); `rotate-cw` icon vendored; the search
  (⌘K) button ships with R8 — no dead doors. New tokens: `size-masthead`, `size-dot`,
  `duration-tooltip-delay`, `font-family-serif`; `size-sidebar` 380→300; `size-rail`
  repurposed as the collapsed width. Verified live across the site level, WordPress, Woo
  (incl. wc-admin paths), classic screens, the editor guard, collapse, tooltips, popover,
  front-door travel, the fixed save cluster, sticky second level, and the login.
- **Lane 2.5 — the shell** (`apps/delivery-plugin/quire/shell.php` + `assets/shell.css`/`shell.js`):
  the rail + contextual sidebar now replace core's admin menu and admin bar on every
  admin screen, rendered live from the capability-filtered `$menu`/`$submenu` globals.
  Product manifests (WordPress core ten · WooCommerce five · Jetpack) classify top-levels
  by slug; anything unclaimed lands in a "Plugins" tray entry so no plugin is ever
  unreachable. Grafts follow their registered parent (Woo's Scheduled Actions stays under
  WordPress → Tools). Current-item highlight reuses core's `$parent_file`/`$submenu_file`/
  `$self` resolution plus wc-admin's `&path=` slugs, reduced to a single you-are-here.
  Collapse persists per user; the account dot carries Visit site / Edit profile / Log out.
  Verified in the playground on Quire screens, classic screens, and Woo React screens.
- **The second level lives beside the content** (S9/S10, decided 2026-07-13 — supersedes
  the J2 takeover and the earlier Settings-mode arrival): the sidebar shows only first-level
  items; when the current menu has children they render as a plain vertical text list in a
  column on the canvas beside the content, the exact page lit (Settings sections, Posts →
  Categories, Woo Analytics' eleven reports — all the same shape). The chevron "peek" and
  the "‹ Parent" back header are retired. The column survives sidebar collapse — it belongs
  to the page, not to the sidebar.
- **First-level icons** (S10): every sidebar row speaks icon + label, from ONE family —
  vendored Lucide SVGs (ISC, GPL-compatible) in `packages/icons/lucide/` and
  `apps/delivery-plugin/quire/assets/icons/`, license alongside. Slug-keyed map covers the
  WordPress core ten, WooCommerce's twelve, and Jetpack; unknown slugs get the puzzle glyph;
  tray rows carry no icons (a column of identical fallbacks is noise). The icon/no-icon
  difference is what tells the two vertical levels apart.
- **The header band** (H1/H1b, decided 2026-07-13): one shell-owned header zone on every
  screen — context line + title left, an actions slot right, one hairline as the datum the
  second-level column and the content both hang from ("underneath, not next to"). Screens
  contribute actions through the `quire_shell_band_actions` filter (Dashboard's Customize/
  New post, Posts' New post, Settings' save cluster); classic screens retire their core
  `<h1>` and their "Add …" button is adopted into the band; wc-admin's duplicate sticky
  header retires under it. Title follows the front door rule (area name at the front door,
  section name inside Settings-like menus); the area moves into the crumb when deeper.
  The band scrolls away with the page. **Anchor rule (revised same day):** the second level
  is real page content — a grid column of `#wpcontent` with native `position: sticky`, the
  Vercel/Stripe-docs pattern. It rests 24px below the hairline, scrolls with the page, and
  sticks 24px from the top; no scroll script touches it (the first JS-transform version
  lagged the trackpad and felt like chrome pretending to be content). **The save cluster is
  fixed** — at rest it sits in the band's slot; scrolled, it stays put and gains the overlay
  shadow. Unsaved changes are never out of sight. The band and the column step aside in the
  block/site editors.
- **The front door rule** (decided 2026-07-13): every menu with children has a front door —
  the child whose page IS the menu (All Posts is Posts, Home is Dashboard). Clicking a
  first-level item navigates there; standing anywhere inside a menu lights its first-level
  row, and the precise page lights in the second-level column.
- Initial monorepo scaffold: `packages/` (tokens, primitives, components, patterns, icons)
  and `apps/` (docs, demo-core, demo-woo, demo-jetpack).
- Project documentation: `README`, `DESIGN`, `ARCHITECTURE`, `CONTRIBUTING`, `GOVERNANCE`,
  `CODE_OF_CONDUCT`.
- GPL-2.0-or-later license.
- RFC process and template under `governance/`.
- Screenshot inventory reference under `inventory/`.
