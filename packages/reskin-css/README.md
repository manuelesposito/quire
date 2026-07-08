# @quire/reskin-css

**Lane 2 of delivery** (see `/DELIVERY.md`, decided 2026-07-09): token-mapped bridge
stylesheets for the admin surfaces Quire does not own — classic wp-admin first, then
WooCommerce and Jetpack surface classes.

This is **the only package allowed to style foreign DOM.** The rules that keep that
honest:

1. **Tokens only.** Every value is a `var(--qr-…)` from `@quire/tokens`. The colour lint
   applies here with zero allowlist.
2. **Every selector block names the screen and state it covers.** The long tail
   (hover flashes, load-order fights, hardcoded link blues) is the real cost — it gets
   paid visibly, one documented block at a time, driven by the ~199-screenshot inventory.
3. **Load-order is explicit.** WP's colour-scheme stylesheet repaints late; bridges must
   be enqueued with it as a dependency, and the `!important` tail is documented, bounded,
   and shameless about why it exists.

## Modules

| File | Covers | Origin |
| --- | --- | --- |
| `src/hooks.css` | Lane 1 — WP's own theming hooks mapped to tokens (`--wp-admin-theme-color`, `--wp-components-color-accent`). Zero selectors. | Probe layer 1, proven on the block editor |
| `src/core-classic.css` | Classic wp-admin: canvas, admin bar/menu, buttons, boxes, tables, inputs, links + the menu-state `!important` tail | Probe layer 2 — 57–88% pixel transformation, token-exact |
| `src/woo.css` | *(planned)* WooCommerce surface class | probe found Woo ignores the hooks |
| `src/jetpack.css` | *(planned)* Jetpack surface class | — |

These first two modules are the probe's layer files, verbatim — the probe was the
prototype (`experiments/playground-probe/`), and its playground remains the test harness:
mount, browse `?quire=2`, compare against the inventory.
