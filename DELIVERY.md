# Delivery — the decision

**Status: DECIDED — 2026-07-09.** Ratified by Manuel. Quire ships as a WordPress
plugin with three lanes (hooks · reskin-css · owned-DOM components). This is the
decision record; the deferral in ARCHITECTURE.md is ended and amended by this file.

## Why now

The deferral had an explicit exit condition: *"until the system is proven."* Both proofs
now exist:

1. **The design proof.** The living style guide renders WordPress, WooCommerce, and
   Jetpack screens — settings, dashboard, tables, catalogues, toggles, upsells — from one
   shared catalogue on one token foundation, light and dark, with contrast and
   token-purity enforced by CI-able gates.
2. **The physics proof.** The playground probe
   (`experiments/playground-probe/FINDINGS.md`) injected the real built tokens into a
   live wp-admin and measured what delivery mechanisms can actually reach.

## The physics, measured

| Surface | WP's own theming hooks | A token-mapped bridge stylesheet |
| --- | --- | --- |
| Classic screens (Dashboard, Posts, Settings…) | **0.0%** pixels changed | 57–88% changed, token-exact |
| Block editor (core React) | re-accents fully | — |
| WooCommerce React (Home, Analytics) | **0.03–0.27%** — only WP primitives | needs its own bridge |

Three findings govern everything:

- **"Retint for free" is a myth** everywhere everyday owners actually live.
- **The bridge is cheap and lands exactly** — but its real cost is the long tail of
  hardcoded microstates (hover flashes, load-order fights, link blues). Two correction
  rounds in one afternoon; budget for the tail, not the happy path.
- **Plugin-owned React components mostly ignore the hooks** — every product's surface
  class needs its own bridge section.

## The proposal: ship Quire as a WordPress plugin, three lanes with a hard boundary

**Lane 1 — hooks (free).** Set `--wp-admin-theme-color` / `--wp-components-color-accent`
from the tokens. Re-accents the block editor and every `@wordpress/components` primitive
that surfaces anywhere. Zero selectors, zero maintenance.

**Lane 2 — reskin-css (load-bearing).** A new `packages/reskin-css`: token-mapped bridge
stylesheets per surface class — `core-classic`, `woo`, `jetpack` — grown from the
~199-screenshot inventory, microstate by microstate. This lane *does* re-style foreign
DOM, which the original architecture rule forbade. **The rule is hereby amended, not
broken silently:** foreign-DOM styling is confined to this one package, consumes tokens
only, and every selector block names the screen and state it covers. It is the probe's
verdict that no honest delivery exists without it.

**Lane 3 — canonical components (the ceiling).** `@quire/components` mount only inside
DOM the plugin owns — screens Quire adds or wholly replaces (a Quire dashboard, a Quire
settings page). Restructuring happens only where we own the ground. The React editors
and Woo/Jetpack apps are never forked, only retinted and bridged.

**v1 contents:** tokens.css + Lane 1 hooks + `reskin-core-classic` + light/dark following
the admin colour-scheme choice + an off switch. Woo/Jetpack bridges follow as v1.x.
**v1 non-goals:** the rail/nav-shell restructure, replacing any React screen, Gutenberg
anything.

## Why not the alternatives (unchanged, now with data)

Standalone still explodes in scope and still can't rebuild Gutenberg — and the probe adds
that even its "just the classic screens" subset would re-implement what a 130-line bridge
already transforms. Hybrid remains the *eventual* shape (Lane 3 grows), which is exactly
what the plugin path preserves: the monorepo keeps delivery swappable, so this decision
risks an adapter, never the system.

## Risks, named

- **Upstream churn**: every WP/Woo/Jetpack release can repaint the long tail. Mitigation:
  the bridge is enumerable, versioned, and covered by screenshot comparison against the
  inventory; breakage is visible, not silent.
- **Load-order fights**: already met twice (colour-scheme stylesheet, wc-admin hook
  re-declaration). Mitigation: explicit dependency ordering + the documented `!important`
  tail — ugly, bounded, honest.
- **Plugin conflicts**: other admin-styling plugins. Mitigation: the off switch, and
  `--qr-` tokens that never collide with `--wp-*`.

## What this changes (started same night)

`packages/reskin-css` gets scaffolded from the probe's layer files (which are, in
miniature, its first two modules), and the plugin adapter becomes `apps/delivery-plugin`
— an mu-plugin grown from `quire-probe.php`. The probe was secretly the prototype.
