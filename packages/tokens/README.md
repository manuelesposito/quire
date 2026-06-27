# @quire/tokens

The **single source of truth** for every design value in Quire: color, type, space, radius,
elevation, and motion. Everything else composes from here — no surface hardcodes a value.

Tokens build into both **CSS custom properties** (for styling) and **typed JS/TS** (for
components), so the same value can't drift between the two.

## Status

🌱 Empty scaffold. The first task is the canonical token set that resolves the three-way
conflicts documented in [`../../DESIGN.md`](../../DESIGN.md):

- **Primary action** — one token (resolves WP/Woo indigo vs Jetpack black).
- **Type scale** — one UI scale with page-title / section-title roles.
- **Spacing** — the roomier 4px-based scale.
- **Radius** — one value (resolves square metabox vs rounded card).
- **Elevation** — one idiom.
- **Motion** — one calm set.

See `src/` for the (forthcoming) token definitions.
