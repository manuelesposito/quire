# Quire

**A cleaner, more unified WordPress admin — design-system first.**

Quire is an open-source redesign of the WordPress admin experience for **everyday site
owners**. It exists to do one thing well: take the four clashing visual languages that
ship inside a single WordPress install — classic wp-admin, Gutenberg, WooCommerce, and
Jetpack — and **gather them into one calm, modern, consistent whole.**

> A *quire* is a set of loose leaves gathered and folded into a single bound section of a
> book. That is the whole project in one word: many scattered surfaces, bound into one.

---

## Status

🌱 **Early — foundation stage.** We are building the design system (the source of truth)
first, and proving it against real screens before committing to any single delivery
mechanism. No production code ships yet.

| Decided | Open |
| --- | --- |
| Design-system-first monorepo (`packages/` = source of truth, `apps/` = swappable consumers) | Delivery mechanism (plugin overlay / standalone / hybrid) — **deliberately deferred** |
| Audience: everyday site owners (calm, low-density) | Final token values (in progress) |
| Scope: WordPress core + WooCommerce + Jetpack | — |
| License: GPL-2.0-or-later | — |

## Why this structure

Three independent expert evaluations (a WordPress-plugin architect, a standalone/headless
architect, and a design-system lead) reviewed a full inventory of ~199 real admin screens
and **converged on the same conclusion**:

- The **plugin overlay** is fast and reversible but can only *reskin* — not restructure —
  the dense React screens (editors, WooCommerce Home/Analytics, Jetpack).
- The **standalone reimplementation** is the cleanest UX but explodes in scope and can
  never natively rebuild Gutenberg; it becomes a parity treadmill against three products.
- The **design system is the invariant.** Delivery is the variable.

So Quire commits to the part that is true no matter what — a canonical design system —
and keeps delivery a **swappable front-end** we decide once the system is proven.

See [`ARCHITECTURE.md`](./ARCHITECTURE.md) for the full reasoning and
[`DESIGN.md`](./DESIGN.md) for the design problem and principles.

## Repository layout

```
packages/          The source of truth — nothing here knows how it will be delivered
  tokens/          Design tokens (color, type, space, radius, elevation, motion)
  primitives/      Unstyled, accessible behaviors (button, toggle, dialog, tabs, table…)
  components/       The styled library (Button, SettingsRow, Notice, Card, DataTable…)
  patterns/         Composed blocks (SettingsPage, ListTablePage, Dashboard…)
  icons/            One icon set — the single symbol family

apps/              Disposable, swappable consumers of the packages
  docs/            Storybook + the live UI-state inventory
  demo-core/       Reskinned WordPress core screens (proof)
  demo-woo/        WooCommerce screens (proof)
  demo-jetpack/    Jetpack screens (proof)

inventory/         The audit input: ~199 real admin screenshots drive what we build
governance/        RFCs and the token-change process
```

## The first deliverable

Not a plugin. Not an app. The first thing we ship is the smallest decisive proof:

> A **token set** + one **Button** + one **SettingsRow** + one **Notice**, rendered against
> three deliberately-conflicting real screens — WordPress General Settings, WooCommerce
> Settings, and Jetpack Security.

If one button and one settings row can make all three look like one product, the system is
real. We build that before a single line of delivery code.

## Getting started

```bash
corepack enable          # Node 20+ ships pnpm via corepack
pnpm install
pnpm dev
```

## Contributing

Quire lives or dies by one rule: **everything composes from the design system; nothing
hardcodes a value or forks a component.** Read [`CONTRIBUTING.md`](./CONTRIBUTING.md) and
[`GOVERNANCE.md`](./GOVERNANCE.md) before opening a PR.

## License

[GPL-2.0-or-later](./LICENSE) — matching WordPress core and the plugin/theme ecosystem.

---

*Quire is an independent open-source project. It is not affiliated with, endorsed by, or
sponsored by the WordPress Foundation, Automattic, WooCommerce, or Jetpack.*
