# Quire — Architecture

## The one decision everything follows from

> **The design system is the invariant. Delivery is the variable.**

Quire is structured so we can commit fully to a canonical design system **today** without
committing to *how* it reaches the user (plugin overlay, standalone app, or a hybrid). That
decision was deliberately deferred until the system was proven. **It now is, and the
decision is made: a WordPress plugin, three lanes — see `DELIVERY.md` (2026-07-09).**
The architecture below is unchanged; it is what made the deferral free and keeps the
adapter swappable even after the decision.

## Why not just pick a delivery mechanism now?

Three independent expert evaluations reviewed ~199 real admin screens:

- **Plugin overlay** — fastest, reversible, install-anywhere; wins decisively on the
  PHP-rendered screens owners use daily. But it can only *reskin*, not *restructure*, the
  React screens (editors, WooCommerce Home/Analytics, Jetpack), and it breaks on every
  upstream release. Score: ~6.5/10.
- **Standalone reimplementation** — best UX ceiling, cleanest system. But scope explodes:
  you cannot natively rebuild Gutenberg, REST coverage runs out at payments/shipping/plugin
  settings, and you inherit a permanent parity treadmill against three products. Score:
  ~4/10 as a replacement.
- **Design-system lead** — the real enemy is four clashing design languages; the only
  approach that keeps tokens canonical is design-system-first with **swappable delivery**.

All three converged. So we build the invariant and keep delivery pluggable.

## The shape: a monorepo with a hard direction of dependency

```
packages/  ──────────────►  source of truth (delivery-agnostic)
   ▲                         knows nothing about plugins or apps
   │ depends on
apps/      ──────────────►  swappable consumers (docs, demos, future delivery adapters)
```

**Rule:** dependencies point one way. `apps/*` import from `packages/*`. `packages/*` never
import from `apps/*` and never assume a delivery target. This is what keeps delivery free to
change later without touching the source of truth.

### `packages/` — the source of truth

| Package | Responsibility |
| --- | --- |
| `@quire/tokens` | Design tokens as the one source. Outputs CSS custom properties + typed JS/TS. No surface hardcodes a value. |
| `@quire/primitives` | Unstyled, accessible behaviors (button, toggle, dialog, tabs, table, menu). No look, just correctness + a11y. |
| `@quire/components` | The styled library — primitives + tokens. Button, SettingsRow, Notice, Card, DataTable, Toggle, Tabs, Modal, EmptyState, PromoCard… |
| `@quire/patterns` | Composed blocks — SettingsPage, ListTablePage, Dashboard, the metabox↔card unifier. |
| `@quire/icons` | One icon set — the single symbol family. |

### `apps/` — swappable consumers

| App | Responsibility |
| --- | --- |
| `docs` | Storybook + the live UI-state inventory. The shop window and the proving ground. |
| `demo-core` | Reskinned WordPress core screens, built only from `packages/*`. Proof. |
| `demo-woo` | WooCommerce screens. Proof. |
| `demo-jetpack` | Jetpack screens. Proof. |
| `delivery-*` | **Decided 2026-07-09 — WordPress plugin** (see `DELIVERY.md`). Canonical components mount only in plugin-owned DOM; foreign-DOM styling is confined to `packages/reskin-css` (tokens-only, per-screen documented) — the probe proved that lane is load-bearing, so the old never-re-style rule is amended there, not broken silently. |

## The boundary with `@wordpress/components`

WordPress core ships its own evolving design system (`@wordpress/components`), and Gutenberg
increasingly renders the React admin screens. Quire's stance:

- **Classic / PHP surfaces** → full custom Quire components.
- **React-Gutenberg surfaces** → *extend* `@wordpress/components` **technically** — map our
  `--qr-` tokens onto its `--wp-components-*` variables so it obeys Quire's look — rather than
  re-skin a moving target. We **never inherit its visual language**: extending it is not
  adopting it.

This boundary is documented and treated as load-bearing; ignoring it is how a redesign rots
at the editor's edge. See `DESIGN.md → Relationship to @wordpress/components` for the full
learn / reject / interoperate stance.

## Non-negotiable invariants (enforced, not just hoped)

1. No hardcoded design values anywhere outside `@quire/tokens` (lint-enforced).
2. No forked components — surfaces compose from `@quire/components`.
3. Dependencies point `apps → packages`, never the reverse.
4. Any delivery adapter may only mount canonical components, never restyle foreign DOM.
5. Every token/component change is validated against all three demo surfaces (core, Woo,
   Jetpack) via the inventory in `apps/docs` before it lands.

## Tooling

- **pnpm workspaces** for the monorepo.
- **Storybook** (`apps/docs`) as the inventory + visual-regression surface.
- Tokens build via a token pipeline (e.g. Style Dictionary) → CSS custom properties + TS.
- Node 20+ (`corepack enable` provides pnpm).
