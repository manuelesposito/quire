# Quire — Design

This document defines **the problem Quire's design system exists to solve** and the
**principles** every design decision is measured against. It is the design source of truth
in prose; the machine source of truth is `packages/tokens`.

---

## The core problem: four languages in one product

The same conceptual screens in a WordPress install are rendered in **four mutually
incompatible visual languages that ship together**, and the seams are visible on a single
page:

1. **Classic wp-admin** — square gray "postbox" metaboxes, list-tables, ~13px base type,
   1px borders, flat surfaces.
2. **Gutenberg / `@wordpress/components`** — its own black/white chrome, 40px controls,
   segmented toggles, an editorial scale.
3. **WooCommerce** — a React card app with indigo primary buttons *and* blue-outline
   secondaries *and* legacy metaboxes coexisting on the **same** screen.
4. **Jetpack** — a fourth language: black primary buttons, a green brand CTA, fully rounded
   cards, large display headings, and more than one toggle style within a single tab.

A site owner moving Dashboard → Products → Jetpack Settings → edit a post crosses four
design systems without leaving the admin. **Quire's job is to collapse these four into
one** — to make "a primary button," "a settings row," "a notice," and "a data table" mean
exactly one thing, regardless of which team shipped the screen.

## Audience: everyday site owners

The bar is **calm and low-density**, not power-user dense. Quire reduces noise; it does not
add controls. When in doubt, choose the roomier spacing, the quieter surface, the fewer
options visible at once. Developers and agencies are not the primary audience — everyday
owners are.

## Principles

1. **One of each.** One primary button. One settings row. One notice. One toggle. One icon
   family. Divergence between WP, WooCommerce, and Jetpack is the bug; convergence is the
   product.
2. **Tokens are law.** Every color, space, radius, type, elevation, and motion value comes
   from `packages/tokens`. No raw hex, no magic numbers, ever. (Enforced by lint.)
3. **Calm by default.** Roomier spacing, quieter surfaces, less on screen. Reject classic
   wp-admin density.
4. **Compose, never fork.** Surfaces are built from `packages/components`. If a component
   doesn't fit, we evolve the component — we never copy-and-tweak it.
5. **Respect the boundary.** Where a screen is already React-Gutenberg, Quire *aligns to and
   extends* `@wordpress/components` rather than re-skinning a moving target. Full custom
   components are reserved for the classic/PHP surfaces. This boundary is explicit.
6. **Degrade gracefully.** Quire styles WP core + WooCommerce + Jetpack. Foreign third-party
   plugin screens keep the shared chrome and sit calmly inside it — never pretend coverage.

## Foundations to define (and their conflicts)

| Foundation | The conflict to resolve |
| --- | --- |
| **Color** | Primary action: WP/Woo indigo vs Jetpack black vs Gutenberg mix → **one** brand-primary token. Jetpack green survives only as a single governed accent, if at all. |
| **Type** | WP ~13px system UI vs Jetpack large display vs Gutenberg editorial → one UI scale with explicit page-title / section-title roles. |
| **Spacing** | WP tight 8/12px vs Woo/Jetpack roomy 16/24px → standardize on the **roomier** 4px-based scale. |
| **Radius** | Square metabox vs rounded card — the single most visible "which team built this" tell → one radius token. |
| **Elevation** | Flat 1px borders vs subtle shadow vs rounded cards → one elevation idiom. |
| **Motion** | Largely undefined today → one minimal, calm set (1–2 durations, 1 easing). |

## Component inventory (what the system must cover)

Grounded in the real screenshot inventory. Minimum set:

- **Chrome / nav:** admin bar, shared side menu + fly-outs, page header, secondary tab bars.
- **Data display:** list-table, data table + KPI tiles, card/panel (the metabox↔card
  unifier), chart container.
- **Forms & controls:** input, textarea, select, number/unit, checkbox, radio, **toggle**,
  **button** (primary / secondary / tertiary / destructive), **settings row** (label +
  control + help text + help-tip), tabbed sub-panel, inline/quick edit.
- **Feedback & overlays:** notice/banner (success/info/warning/error), modal, empty state,
  error state, onboarding checklist, promo/upgrade card (contained), progress, badge/pill,
  avatar, popover/menu.
- **Editor surface (special case):** consumed via `@wordpress/components`, not re-skinned.

## Relationship to `@wordpress/components`

WordPress already ships a design system: **`@wordpress/components`** (the React library
behind Gutenberg), **`@wordpress/base-styles`** (SCSS variables/mixins and CSS custom
properties like `--wp-admin-theme-color`, `--wp-components-color-accent`), a published
**Gutenberg Storybook**, and a **WordPress Design Library in Figma**.

We are clear-eyed about it: it is reasonably accessible and componentized, but it **does not
cohere** — grown by many teams over many years and applied unevenly, it is a major source of
the very "four clashing languages" problem Quire exists to fix. Quire's stance is therefore
deliberate: **learn from it, reject its style, interoperate only where the platform forces
us to — and even there, never inherit its look.**

- **Learn (coverage, not style).** Its component set is a battle-tested checklist of *what a
  WordPress admin needs*, and its accessibility patterns are worth studying. We mine *which
  components must exist* — never *how they look*. It also serves as a concrete anti-pattern
  library: examples of the incoherence we're correcting.
- **Reject (the visual language).** Its look is not Quire's look. We do not adopt its tokens,
  its density, or its component styling as our own.
- **Interoperate (only where forced, on our terms).** On React/Gutenberg surfaces we cannot
  remove its components, so we *extend* them **technically** — mounting into them and mapping
  our `--qr-` tokens onto their `--wp-components-*` variables so they obey Quire's look. On
  classic/PHP surfaces we use full custom Quire components.

**The load-bearing distinction: "extend it technically" is *not* "adopt its look."** Even
where we interoperate, we bend `@wordpress/components` to Quire's tokens; we never let its
visual language leak into ours. A future contributor must not mistake technical interop for
stylistic inheritance.

## Source of truth

- **Machine:** `packages/tokens` (tokens) → `packages/components` (styled library).
- **Prose:** this file.
- **Visual:** the Storybook + inventory in `apps/docs`.

If these ever disagree, `packages/tokens` wins and the others are corrected.
