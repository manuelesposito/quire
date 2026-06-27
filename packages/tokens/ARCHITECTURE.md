# Quire — Token Architecture

> **Status: DRAFT for review.** This spec defines the *structure* of Quire's design tokens —
> the tiers, the naming, the shape of every scale, and the pipeline. It does **not** commit
> final values. Every concrete number below is illustrative ("SHAPE — not final") to show
> the *cadence* of a scale; the actual values are a later, deliberate decision made against
> real screens. Get the structure right here and every later detail slots in as a single,
> global, consistent lever.

---

## 1. The three-tier model

Every token belongs to exactly one tier. The tier decides who may reference it.

```
  TIER 1 — PRIMITIVE        TIER 2 — SEMANTIC          TIER 3 — COMPONENT
  (raw material)            (intent / role)            (per-component knob)
  ───────────────────       ─────────────────────      ──────────────────────
  color.blue.500     ◄───   color.action.primary  ◄─── button.bg
  space.200          ◄───   space.inset.card      ◄─── card.padding
  font.size.300      ◄───   text.body                  (most components stop
  radius.200         ◄───   radius.control              at the semantic tier)
```

- **Tier 1 — Primitive.** Context-free raw values. Named by *what they are*
  (`color.blue.500`, `space.200`). The palette and the scales. **Components never reference
  primitives directly.**
- **Tier 2 — Semantic.** Map intent → primitive. Named by *role/purpose*
  (`color.action.primary.default`, `text.page-title`, `radius.card`). **This is the layer
  components consume.** Theming (light/dark) happens here by remapping semantic → primitive.
- **Tier 3 — Component.** Optional. A named knob a single component owns
  (`button.padding-x → space.inset.sm`). Used *sparingly* — only when a component genuinely
  needs an independent lever. Most components compose straight from semantic tokens.

**Why three tiers:** it's the single thing separating a professional system (Anthropic,
Figma, Stripe, Radix) from a pile of CSS variables. Values change in one place (primitive),
meaning changes in one place (semantic), and a component quirk changes without touching
either. It is also what makes dark mode and brand-accents cheap.

---

## 2. Naming & format

- **Hierarchy:** `category.concept.variant.state` — e.g. `color.action.primary.hover`.
- **Case:** lowercase, dot-namespaced in the token JSON; kebab in CSS output.
- **CSS prefix: `--qr-`** (Quire). Non-negotiable groundwork: Quire runs *inside* WordPress,
  which ships its own `--wp-*` / `--wp-admin-*` variables. Our prefix guarantees we never
  collide with WordPress, WooCommerce, or Gutenberg CSS variables.
  - `color.action.primary.hover` → `--qr-color-action-primary-hover`
- **Source format: W3C Design Tokens** (`$value` / `$type`). Tool-agnostic, and spoken
  natively by both **Style Dictionary** (→ code) and **Tokens Studio** (→ Figma). This is
  what lets one source feed code *and* Figma without drift.

---

## 3. The scales (SHAPE — not final values)

Each scale is defined as a *system* (a base + a cadence), not a bag of arbitrary numbers.

### 3.1 Spacing — 4px base, stepped
Primitive: `space.0 … space.2400`, indexed so the number ≈ the px value ×100 of nothing —
simplest is a numeric ladder on a **4px base**:

| token | px (shape) | | token | px (shape) |
|---|---|---|---|---|
| `space.0` | 0 | | `space.500` | 20 |
| `space.50` | 2 | | `space.600` | 24 |
| `space.100` | 4 | | `space.800` | 32 |
| `space.200` | 8 | | `space.1000` | 40 |
| `space.300` | 12 | | `space.1200` | 48 |
| `space.400` | 16 | | `space.1600` | 64 |

Semantic spacing names the *use*, not the size:
`space.inset.{xs,sm,md,lg}` (padding inside a box), `space.stack.{xs…lg}` (vertical gaps),
`space.inline.{xs…lg}` (horizontal gaps). Calm-first → semantic defaults lean roomy.

### 3.2 Type — composite text roles over raw scales
Primitives (the raw ramps):
- `font.family.{sans, mono}`
- `font.size.{100…900}` — a **modular ramp** (a base + a ratio, e.g. base 14 · ~1.125).
- `font.weight.{regular, medium, semibold, bold}`
- `font.lineHeight.{tight, normal, relaxed}`
- `font.letterSpacing.{tight, normal, wide}`

Semantic **text roles** are *composites* (the Figma "text style" equivalent) — one token
bundles size + weight + line-height + letter-spacing:

```
text.display        text.page-title      text.section-title
text.body           text.body-sm         text.label
text.caption        text.code
```

This matters for you specifically: **letter-spacing and size live as first-class tokens**,
so tuning "the label is 1% too loose" is one edit to `text.label`, everywhere, forever.

### 3.3 Radius — one source for the "which team built this" tell
Primitive: `radius.{none, 100, 200, 300, 400, full}` (e.g. 0 / 4 / 6 / 8 / 12 / 9999 — shape).
Semantic: `radius.control` (inputs, buttons), `radius.card`, `radius.pill`. One decision
collapses square metaboxes and rounded cards into a single language.

### 3.4 Color — ramps → roles → modes
**Primitive (ramps):**
- `color.neutral.{0…1000}` — the workhorse ramp; needs the most steps (text, surfaces,
  borders all draw from it).
- Hue ramps: `color.blue.{50…900}`, `color.green.*`, `color.red.*`, `color.amber.*`, … each
  a full ramp so states (hover/active) and dark mode have stops to pull from.

**Semantic (roles) — grouped by purpose:**
- `color.text.{default, muted, subtle, on-action, link, disabled}`
- `color.surface.{canvas, raised, sunken, overlay}`
- `color.border.{default, strong, focus}`
- `color.action.{primary, secondary, tertiary, destructive}.{default, hover, active, disabled}`
- `color.feedback.{success, info, warning, danger}.{surface, border, text, icon}`
- `color.accent.*` — the **governed brand layer** (see §4).

**Modes:** light + dark are **structural from day one** — the semantic layer remaps to
different primitive stops per mode; primitives don't change. Retrofitting dark later is
expensive; architecting for it now is nearly free. (See §5.)

### 3.5 Elevation & borders
- `shadow.{100, 200, 300}` (primitive) → `elevation.{raised, overlay}` (semantic).
- `border.width.{100, 200}` → semantic borders pull color from `color.border.*`.
- Calm-first note: Quire will likely *prefer borders over shadows*; the architecture carries
  both so that's a value choice, not a structural one.

### 3.6 Motion
- `duration.{100, 150, 200, 300}`, `easing.{standard, emphasized, decelerate}` (primitive)
  → `motion.{toggle, expand, fade, overlay}` (semantic). One calm, minimal set.

### 3.7 Sizing
- `size.control.{sm, md, lg}` (control heights, e.g. 28/32/40 — shape), `size.icon.{sm,md,lg}`,
  `size.touch-target.min` (accessibility floor for everyday owners), container max-widths.

### 3.8 Z-index
- A *named layer ladder* — `z.{base, dropdown, sticky, overlay, modal, toast, tooltip}` — to
  prevent z-index wars across hundreds of screens. Groundwork, decided once.

### 3.9 Breakpoints
- `breakpoint.{sm, md, lg, xl}` — the admin is responsive; named breakpoints keep it
  consistent.

---

## 4. The accent / brand layer (the Jetpack-green problem)

Marketing-owned color (Jetpack's green, Woo promos) is the recurring source of divergence.
Rule, baked into the architecture: brand accents exist **only** as a single, governed
`color.accent.*` semantic group, added via RFC — **never** a raw hex re-hardcoded per
surface. If multi-brand accents aren't sanctioned, they don't exist.

---

## 5. Theming axes — what the system supports (and deliberately doesn't)

| Axis | Decision | Rationale |
|---|---|---|
| **Color mode** (light / dark) | **Supported from day one** (structural) | Cheap now, expensive to retrofit; WP admin already has dark schemes. |
| **Density** (comfortable / compact) | **Single comfortable density now** — *not* an axis yet | Audience is everyday site owners → calm, low-density. One density keeps the system simple; can be added later if ever needed. |
| **Brand accent** | One governed `color.accent.*`, RFC-gated | See §4. |

---

## 6. Pipeline — one source, three outputs

```
        packages/tokens/src/                Style Dictionary v4
        ├─ primitive/*.json   ─────────┐
        ├─ semantic/*.json    ─────────┼──►  dist/css/variables.css   (--qr-* ; light + [data-theme=dark])
        ├─ component/*.json   ─────────┤    dist/ts/tokens.ts          (typed, for components)
        └─ modes/{light,dark}.json ────┘    dist/json/                 (for Figma via Tokens Studio)
```

- **Source of truth:** the W3C-format JSON in `packages/tokens/src`.
- **Code:** Style Dictionary emits CSS custom properties (semantic tokens under `:root` and
  `[data-theme="dark"]`) + a typed TS module. **Components reference only `var(--qr-…)`
  semantic tokens** — never a primitive, never a raw value.
- **Figma:** the same JSON syncs to Figma Variables via **Tokens Studio**, so the Figma file
  is a *mirror* of the truth, not a second master.

---

## 7. The rules this architecture enforces

1. Components reference **semantic** tokens only (never primitives, never raw values).
2. Primitives are named by *what they are*; semantics by *what they're for*.
3. Modes and accents live entirely in the semantic remap — primitives are mode-agnostic.
4. Everything emits under the `--qr-` namespace to coexist with WordPress.
5. One source (W3C JSON) → code **and** Figma. Neither is the master; the JSON is.

---

## 8. Open decisions for review

These are structural choices to confirm before we touch values:

1. **Three-tier model** (primitive → semantic → component)? *(recommended: yes)*
2. **Numeric primitive scales** (`space.200`) + **semantic role names** (`space.inset.card`)?
   *(recommended: yes)*
3. **Dark mode as a day-one structural axis**? *(recommended: yes — cheap now)*
4. **Single comfortable density** (no compact axis yet)? *(recommended: yes — matches the
   calm, everyday-owner mandate)*
5. **Composite text roles** as the type primitive-of-record (Figma text-style parity)?
   *(recommended: yes)*
6. **W3C token format + Style Dictionary + Tokens Studio** as the toolchain? *(recommended)*
7. **`--qr-` CSS prefix**? *(recommended — required to coexist with WordPress)*
