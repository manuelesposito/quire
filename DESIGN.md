# Quire — Design

This document is Quire's design source of truth **in prose**: the **philosophy** we build
toward, the **problem** we solve, and the **principles and grammar** every decision is
measured against. The machine source of truth is `packages/tokens` — this file *explains*, the
code *enforces*, and the two are meant to describe the same system.

---

## The North Star — Quire's philosophy

A *quire* is a bookbinding term: a gathering of folded sheets, bound into a book. The name
holds the whole idea. WordPress's admin today is **loose, mismatched pages from four different
printers** — wp-admin, Gutenberg, WooCommerce, Jetpack — never bound together. **Quire's
reason for being is to gather the scattered pages into one coherent, well-bound whole.**

> **North Star.** An everyday person's WordPress admin that is calm, clear, and legible
> to everyone — one quiet, coherent place instead of a committee's dashboard.

What we believe:

1. **Coherence is respect.** Every clashing button is a small tax on someone's attention and
   confidence. Making everything mean *one thing* is how we respect the person using it.
2. **Calm is the feature.** The work is subtraction — fewer colours, fewer styles, less noise.
   A quiet screen is a finished screen. We never add a control to solve a problem we could
   remove.
3. **Legible to everyone, or it isn't design.** Accessibility is not a checklist item —
   AAA-contrast body text, unmistakable states, and one honest focus ring are the craft
   itself. If someone can't read it, nothing else about it matters.
4. **For the everyday owner.** Not developers, not agencies — the shop owner, the writer, the
   volunteer who never *chose* to learn an admin panel. They deserve one that doesn't punish
   them.
5. **Built to age, not to trend.** Black, white, gray, and one typeface do not go out of
   style. Restraint is what keeps a system feeling right in ten years instead of novel for
   one.
6. **A commons, not a product.** Open, GPL, owned by the community that runs a large share of
   the web. Quire answers to no vendor.

**The long arc:** today Quire is a coherent skin over the existing admin; the North Star is a
genuine reimagining of what running a WordPress site feels like. At every stage the immutable
core is the *feeling* — calm, clear, unhurried, made with care.

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

The bar is **calm, generous, and airy** — big enough type to read without leaning in,
room around everything, never power-user dense. Quire reduces noise; it does not add
controls. When in doubt, choose the roomier spacing, the quieter surface, the fewer
options visible at once. Developers and agencies are not the primary audience — everyday
owners are.

## The design language — how Quire feels

The principles below say *what* to converge; this says *how it should feel* — the part a
contributor cannot infer from the tokens.

**Clean, quiet, modern.** Quire is white paper and black ink — a strict gray scale, one
typeface, generous air. Nothing decorates; everything either informs or stays out of the
way. It is deliberately **not** a clone of any existing admin, nor a copy of any other
product's look — the discipline is the identity.

**Nothing decorative, ever.** No marker bars, no letterspaced uppercase labels, no filled
chips, no gradients, no ornament. State is carried by tone and weight; hierarchy by size
and space. If an element neither informs nor acts, it goes.

The four pillars of the feel:

- **White, in two layers.** Pure white is the page; one near-white carries the quiet
  chrome. Surfaces separate by a 1px hairline, never by drop shadow — shadow is reserved
  for things that truly float (menus, modals, tooltips).
- **Black is the accent.** The strongest ink on screen belongs to the primary action;
  location is marked by a gray wash and a weight change, never filled. Colour appears
  *only* as status — green = success, amber = warning, red = danger, blue = info — each
  with a text step that clears AA, and blue is never interactive, so colour always means
  something.
- **One typeface.** Inter, at a generous scale (16px body, AAA contrast), with a
  single-line/multi-line line-height split so labels sit crisp and reading text breathes.
  Non-Latin scripts fall through to the system's native fonts. Code renders in the
  system monospace — as content, not as a voice.
- **Airy and deliberate.** 40px controls, roomy padding, few options at once, nothing
  shouting. The emotional target is *reassuring* — for an everyday owner, not a power user.

**Voice & tone (microcopy).** Plain, warm, concrete. Short sentences. Say what happened and
what to do; never jargon. Reassure, don't alarm. (The warmth lives in the words now, not
the pigments.)

## Principles

1. **One of each.** One primary button. One settings row. One notice. One toggle. One icon
   family. Divergence between WP, WooCommerce, and Jetpack is the bug; convergence is the
   product.
2. **Tokens are law.** Every color, space, radius, type, elevation, and motion value comes
   from `packages/tokens`. No raw hex, no magic numbers, ever. *(Today upheld by review; a
   no-raw-value lint is planned — see the enforcement table below.)*
3. **Calm by default.** Roomier spacing, quieter surfaces, less on screen. Reject classic
   wp-admin density.
4. **Compose, never fork.** Surfaces are built from `packages/components`. If a component
   doesn't fit, we evolve the component — we never copy-and-tweak it.
5. **Respect the boundary.** Where a screen is already React-Gutenberg, Quire *aligns to and
   extends* `@wordpress/components` rather than re-skinning a moving target. Full custom
   components are reserved for the classic/PHP surfaces. This boundary is explicit.
6. **Degrade gracefully.** Quire styles WP core + WooCommerce + Jetpack. Foreign third-party
   plugin screens keep the shared chrome and sit calmly inside it — never pretend coverage.

## The grammar — the locked visual rules

System-wide rules that keep every screen coherent. They are not per-component choices; they
hold everywhere, and a component that breaks one is the bug.

1. **Colour is meaning, and selection has a cardinality.** The accent is for emphasis —
   links, highlights, the focus ring — and for the **singular "you are here"**: the one
   current product, the one current page, the one active tab. That state is a quiet gray
   wash with a semibold ink label (inverted in dark) — never a filled chip, never a marker,
   so it can never be mistaken for a button. Because the primary action is the only solid
   black on screen, its meaning stays absolute.
   **Bulk, plural selection is neutral** — ticked table rows and the like use a neutral
   surface, never the accent, so a screenful of them stays calm and never reads as a wall of
   warnings. **Hover / pressed** is a neutral translucent wash that sits *below* selected, so
   the two differ in hue and lightness. **Form selection** (a ticked checkbox, a chosen radio,
   a toggle that's on, a picked date) uses the control ink (`control.on` / `control.on-fg`):
   it picks a value, not a location.
2. **One focus ring.** Everything focusable shows the same ring — a 2px accent outline at a
   2px offset. No alternate focus styles.
3. **Borders separate; shadow floats.** Surfaces divide by 1px hairline borders. Drop shadow
   appears *only* on overlays that leave the page plane — menus, popovers, modals (and the
   small lift under a control thumb).
4. **Status has four fixed colours.** success = sage, warning = amber, danger = brick, info =
   slate — and a status colour never doubles as decoration. Warning is amber (orange), kept
   distinct from the gold accent precisely so "selected" and "warning" never read alike.
5. **Legible by guarantee.** Every text-on-surface pair must pass WCAG AA (4.5:1 normal text,
   3:1 large/heavy). This is checked live on the Tokens screen; a failing pair is a bug, not a
   preference.
6. **Everything from tokens.** No off-scale value, ever (see Principle 2).

## How these are enforced (words ↔ code)

The philosophy, principles, and grammar above are only real if the code backs them. Where a
rule *can* be checked mechanically, it should be — that is the difference between a poster and
a guarantee. Honest status today:

| Rule | Enforced by | Status |
| --- | --- | --- |
| One of each | A single shared definition per component (`components.css` / `packages/components`) | ✅ real |
| Legible by guarantee | Live WCAG contrast check on the **Tokens** screen | ✅ real |
| Accent = emphasis only · status = four fixed colours | Semantic colour tokens (`accent.*`, `feedback.*`) | ✅ real (by token use) |
| Borders separate · shadow floats | `elevation.*` tokens; shadow only on overlay components | ✅ real (by token use) |
| One focus ring | Shared focus-ring token used by every focusable component | ✅ real |
| Tokens are law (no raw colours) | `scripts/lint-colors.py` — scans `components.css` + every app screen for raw colour values; reasoned allowlist for content stand-ins; exit 1 gates CI (`pnpm lint:colors`) | ✅ real (colours; spacing/type lint still planned) |
| Contrast never regresses | `packages/tokens/scripts/check-contrast.py` — 52 text-on-surface pairs, both themes, resolved from the built CSS; exit 1 gates CI (`pnpm check:contrast`). First run caught 3 real fails (dark subtle, dark selected-mark, light on-accent) — all fixed at token level | ✅ real |
| Calm · compose-never-fork · respect-the-boundary · degrade-gracefully | Design + code review | 👁 judgment |

When a row says *planned*, the rule still holds — it is upheld by review until the check
exists. We do not claim enforcement the code does not provide.

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
- **Prose:** this file — philosophy → problem → principles → grammar.
- **Visual:** the live specimens in `apps/docs/specimen` (Foundation, Tokens, Components,
  Navigation, Orders), plus the screenshot inventory in `inventory/`.

If these ever disagree, `packages/tokens` wins for *values* and this file wins for *intent*;
the others are corrected to match.
