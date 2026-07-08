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

🌿 **Foundation built, delivery deferred.** The design system exists and is proven: a full
token set (light + dark), the complete component catalogue, and a ten-screen **living style
guide** where WordPress, WooCommerce, and Jetpack screens all render under one grammar.
No production code ships yet — by design.

| Decided | Open |
| --- | --- |
| Design-system-first monorepo (`packages/` = source of truth, `apps/` = swappable consumers) | Delivery mechanism (plugin overlay / standalone / hybrid) — **deliberately deferred**, now probe-informed (see `experiments/playground-probe/FINDINGS.md`) |
| Audience: everyday site owners — calm through *consistency*, dense & precise (not big & empty) | Going public |
| Tokens v1: warm paper canvas, ochre accent, Libre Baskerville · Inter · IBM Plex Mono; dark mode as a first-class axis | Spacing/type lint (colour lint is live) |
| The grammar: accent = the singular "you are here"; bulk selection neutral; one focus ring; borders separate, shadow floats | — |
| Enforced guarantees: WCAG contrast gate + raw-colour lint (both CI-able, see below) | — |
| Scope: WordPress core + WooCommerce + Jetpack · License: GPL-2.0-or-later | — |

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

## The first deliverable — done

The founding bet was the smallest decisive proof:

> A **token set** + real components, rendered against three deliberately-conflicting real
> screens — WordPress General Settings, WooCommerce, and Jetpack Security.

That proof now exists in the living style guide: WP Settings, Dashboard, WooCommerce
Orders & Products, and Jetpack Security all compose from one shared catalogue
(`apps/docs/specimen/components.css`) on one token foundation — light and dark. The
delivery question was probed against a live WordPress too
(`experiments/playground-probe/FINDINGS.md`): WP's own theming hooks re-accent only the
core React surfaces; a token-mapped bridge stylesheet is load-bearing everywhere else.

## Getting started

```bash
corepack enable          # Node 20+ ships pnpm via corepack
pnpm install
pnpm --filter @quire/tokens build   # JSON tokens -> CSS variables + TS

python3 serve.py         # the living style guide (no-cache dev server)
# then open http://localhost:4321/apps/docs/specimen/home.html

pnpm check:contrast      # WCAG gate: 52 text-on-surface pairs, both themes
pnpm lint:colors         # tokens-are-law: no raw colours outside the tokens
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
