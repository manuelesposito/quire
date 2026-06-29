# tokens/src — Quire's canonical foundation (v1)

These are the **real, decided** token values — the single source of truth, consolidated from
the validated warm-bookish direction and the navigation work. They are no longer placeholders.

The build (`pnpm --filter @quire/tokens build`) compiles them via Style Dictionary into
`dist/css/variables.css` (`--qr-*` custom properties) and `dist/ts/tokens.ts`.

## Structure (three-tier — see ../ARCHITECTURE.md)

```
primitive/   raw values, named by what they are
  color.json        warm neutral ramp · ochre accent · brick/sage/slate "almanac" hues
  typography.json   families (Libre Baskerville · Inter · IBM Plex Mono) · size/weight/line-height/letter-spacing
  dimension.json    4px space scale · radius · border-width · sizing (controls, rail, sidebar)

semantic/    roles, named by purpose — what components consume
  color.json        text · surface · border · accent · action · state (hover/active/selected/focus) · feedback
  typography.json   text roles: display · page-title · section · body · label · caption · mono-label · code
  dimension.json    space (inset/stack/inline) · radius (control/card/pill)
```

## Conventions

- Components reference **semantic** tokens only — never primitives, never raw values.
- Everything emits under the `--qr-` namespace so it never collides with WordPress's `--wp-*`.
- Interaction states are first-class tokens (`color.state.hover`, `…active-surface`,
  `…selected-surface`, `…selected-mark`, `…focus-ring`) so hover/active/selected/focus are
  consistent everywhere by construction.

## Still to add (follow-ups)

- Motion (durations/easing) and elevation (shadow) tokens.
- Dark-mode remap of the semantic layer.
- Wire the demo surfaces to consume these tokens (replacing their inline values).
