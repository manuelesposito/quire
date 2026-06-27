# Inventory — the audit input

Quire is built from evidence, not guesswork. Before deciding what components the design
system needs, we catalogued **every screen and state** of the three target surfaces.

These ~199 captured states are the audit input that drives the component inventory in
`DESIGN.md`. They were captured from real installs (Jetpack with a genuine WP.com
connection — no offline banner) via wp-playground + Playwright.

## Source captures

> These live outside the repo (they're large binary inventories, not source). Paths on the
> original capture machine:

| Surface | Count | Location |
| --- | --- | --- |
| WordPress core wp-admin | 111 states | `~/Desktop/wp-admin-design-overhaul/` |
| WooCommerce admin | 73 states | `~/Desktop/woocommerce-admin-overhaul/` |
| Jetpack (connected, incl. all 8 Settings tabs) | 15 states | `~/Desktop/jetpack-admin-overhaul/` |

Known gaps: My-Jetpack-hub and Stats were not captured (the heaviest React pages degraded
under SQLite/Sync during capture).

## How the inventory is used

1. **Component discovery** — every recurring button, table, form, notice, card, and toggle
   in the captures becomes an entry in the `DESIGN.md` component inventory.
2. **Conflict mapping** — where WP, WooCommerce, and Jetpack style the *same* concept
   differently, that divergence is the specific problem a token or component must resolve.
3. **Visual regression** — the proving screens in `apps/docs` are reconstructed from these
   captures, so changes are validated against what the real admin actually looks like.

## Next step

Curate the canonical proving set — the three deliberately-conflicting screens that the first
deliverable targets: **WP General Settings**, **WooCommerce Settings → General**, and
**Jetpack Settings → Security**.
