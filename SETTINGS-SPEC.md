# Quire — Settings Pattern Spec

Status: **DECIDED 2026-07-10** (design review, all decisions resolved).
Packaging: **J2 — page; settings takes over the sidebar.**
Exploration record: Figma "Quire — Tokens" → page "Explorations — Settings · General"
(15 variants A–M; J2 frame is the winner, K2 documented as a possible future packaging).

## What this pattern serves

One settings anatomy for all three products:
- **WordPress**: 8 flat sections (General, Connectors, Writing, Reading,
  Discussion, Media, Permalinks, Privacy)
- **WooCommerce**: grouped nav (STORE / PRODUCTS / FULFILMENT / CHECKOUT /
  COMMUNICATION / ADVANCED), content includes tables (shipping zones) and
  toggle groups
- **Jetpack**: 8 tabs (Security, Performance, Writing, Sharing, Discussion,
  Traffic, Newsletter, Monetize), toggle-module cards

Delivery: WordPress plugin (Lane 3); real wp-admin pages with URLs; form
semantics = post the full options allow-list (verified live, Camp 2).

## The anatomy

1. **Sidebar takeover.** "Settings" is ONE entry in the product sidebar.
   Entering it swaps the sidebar contents: "‹ Settings" header (back returns
   to the product nav), sections below. The shell keeps its constant
   three-column geometry (rail / one nav column / content). Exits: ‹ back,
   any rail product, browser back — all native navigation.
2. **Settings nav**: flat list up to ~8 sections; group headers beyond
   (never nesting, never a second column). Selected = grey wash + semibold.
3. **Content**: white cards on grey canvas; labels above fields; short
   controls paired on a row; text fields never squeezed; tables and wide
   content span the full row. Two-column body at desktop (what-it-is left,
   behaviour right); stacks below ~1100px.
4. **Contextual save bar**: at rest, zero action chrome. On first edit a dark
   cluster appears in the topbar ("Unsaved changes · Discard · Save",
   surface.inverse). Save posts the whole allow-list; success → "Saved",
   fades. Discard reverts. Navigating with dirty state warns.

## Decided behaviours (design review, 2026-07-10)

- **Save bar accessibility (D2):** the bar announces via `aria-live="polite"`
  ("You have unsaved changes"); focus NEVER moves when it appears; the bar's
  controls sit after the topbar in tab order; `Cmd/Ctrl+S` saves when dirty.
- **Save failure (D3):** the bar itself transforms into the error surface —
  feedback.danger family, "Couldn't save — your changes are still here",
  Retry + Discard. Edits are never lost on transport failure. Validation
  failures: inline error text under the offending field (feedback.danger.text)
  and the save is blocked until resolved.
- **Mobile ≤~768px (D4):** list-first drill-down. Settings opens as the
  section list (groups intact); tapping a section pushes its screen; back
  returns. Save bar pins to the top of the section screen.
- **Packaging (D5):** J2 page. Reasons of record: ordinary-document
  accessibility (no dialog contract), delivery on the proven reskin path
  (native URLs, allow-list form pattern, third-party injected content keeps
  working), wp-admin conventions intact. K2 (modal) remains adoptable later
  without redesign because the anatomy is packaging-independent; revisit only
  with evidence (e.g. frequent mid-order Woo settings trips).

## Not in scope (deferred, with rationale)

- **Tools pages relocation** — same hub is plausible; decide when Tools screens
  are reached in the climb.
- **Reduced-motion spec for bar appearance** — inherit the system-wide motion
  pass (bar appears without slide when `prefers-reduced-motion`).
- **Settings search** (Shopify-style) — valuable at Woo scale; needs the hub
  live first.

## What already exists (reuse, don't reinvent)

- Selection idiom (wash + semibold), focus ring token, elevation presets
- Card, field, toggle, radio, button components (components.css)
- Six-step feedback ramps incl. danger default/hover/pressed + on-solid
  (adopted 2026-07-10) — the failure bar uses these
- Camp 2's verified options.php round-trip form pattern

## GSTACK REVIEW REPORT

| Review | Trigger | Why | Runs | Status | Findings |
|--------|---------|-----|------|--------|----------|
| CEO Review | `/plan-ceo-review` | Scope & strategy | 0 | — | — |
| Codex Review | `/codex review` | Independent 2nd opinion | 0 | — | — |
| Eng Review | `/plan-eng-review` | Architecture & tests (required) | 0 | — | — |
| Design Review | `/plan-design-review` | UI/UX gaps | 1 | CLEAN | score: 6/10 → 9/10, 4 decisions |
| DX Review | `/plan-devex-review` | Developer experience gaps | 0 | — | — |

- **UNRESOLVED:** 0 (three items consciously deferred with rationale, listed above)
- **VERDICT:** DESIGN CLEARED — packaging decided (J2), save lifecycle complete,
  a11y and mobile specified. Eng review recommended before the WordPress build.
