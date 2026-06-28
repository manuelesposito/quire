# Quire — Coherence Audit

Stepping back to the oversight view: everything built so far (the navigation + the component
gallery) examined as **one system**, to find where details don't yet obey the whole. Each
finding gets a proposed **single rule** (the "grammar"). Severity:
🔴 system-breaking (decide together) · 🟠 off-token drift (snap to system) ·
🟡 undefined (add to foundation) · 🟢 already coherent (protect).

---

## 🔴 1. The "on / selected / active" colour — BROKEN (the big one)

The single most important grammar decision, and right now it's expressed **four** ways:

| Where | Today |
| --- | --- |
| Toggle (on) | **ochre** |
| Checkbox / radio (checked) | **ink** |
| Nav — active product | **ochre** square + dark-ochre mark |
| Nav — active menu item | **neutral grey** surface, no accent |

→ **Decide one rule.** Two sane options:
- **(A) Ochre is the universal "on/selected/active"** — everywhere (checkbox, radio, toggle, nav, tabs). Maximally coherent.
- **(B) Split by intent** — *navigation/emphasis* = ochre; *binary form selection* = ink. Defensible (ink checkboxes are common), but two rules to remember.

## 🔴 2. Focus — BROKEN (two languages)

| Where | Today |
| --- | --- |
| Button, checkbox, radio, toggle | **outline** 2px ochre + 2px offset |
| Input, select | **border** turns ochre + 3px ochre-soft **glow** |

→ **Decide one focus treatment** and apply to *every* interactive element. (Recommend the
outline ring — it's consistent and accessible on any background.)

---

## 🟠 3. Spacing — off the scale

Real off-scale values found (scale is 4 · 8 · 12 · 16 · 20 · 24 · 32…):
- nav menu items: `padding 7px 10px`; gaps `9px`, `26px`, `30px`
- gallery: gaps `22px`, `15px`; margins `18px`
→ **Rule:** every padding/gap/margin uses a space token. Snap: 7→8, 10→12, 15→16, 18→16/20, 22→24, 26→24, 30→32.

## 🟠 4. Typography — off the scale + roles unused

- Off-scale font sizes: nav `13.5px`, `9.5px` (token sizes are 11/12/13/14/16/18/22/28/40).
- **We defined composite `text.*` roles (body, label, section…) but components use raw
  `font-size` tokens instead** — the role layer exists and isn't consumed.
- Hardcoded `letter-spacing:-0.005em` on Button (token is -0.006em).
→ **Rule:** every text element uses a **text role** (`text.body`, `text.label`, `text.caption`…),
never a raw size. Snap nav 13.5→14 (body), 9.5→11 (caption).

## 🟠 5. Radius — one off-scale outlier

- control = 6 (buttons, inputs, menu items) ✓ · card = 10 (cards, nav squares) ✓
- **Checkbox box = `4px` hardcoded** — not in the radius scale (none/6/10/14/full).
→ **Rule:** add a `radius.xs` (4px) token for small controls, or round checkboxes to `radius.sm`.

## 🟠 6. Stray hardcoded values

- Toggle knob `#fff`; knob shadow `rgba(0,0,0,.25)`; theme-toggle shadow `rgba(0,0,0,.16)`.
→ **Rule:** knob/thumb colour = a token; shadows come from elevation tokens (see §8).

## 🟡 7. Motion — undefined

- Transitions are ad-hoc: mostly `.12s`, toggle `.15s`; no easing defined.
→ **Add motion tokens** (`duration.fast/base`, `easing.standard`) and use one consistently.

## 🟡 8. Elevation — undefined

- Separation is border-based everywhere (good). But overlays to come (Modal, Popover, Menu)
  and the toggle knob need shadow, and there's no shadow token.
→ **Add elevation tokens** (`shadow.sm` for knobs/raised, `shadow.lg` for overlays).
  **Rule:** borders for in-page separation; shadow *only* for true overlays + control thumbs.

---

## 🟢 Already coherent — protect these

- **Border-based separation** is used consistently (nav, cards, inputs) — keep it; don't let
  shadows creep into in-page layout.
- **Status colours** map cleanly through tokens (success=sage · warning=ochre · danger=brick ·
  info=slate); destructive Button and input-error already use the danger family. Keep.
- **Control heights** are consistent (32 md / 28 sm via size tokens). Keep.
- **The token namespace** (`--qr-*`) and the light/dark theming hold across everything. Keep.

---

## What this means

Two decisions are genuinely yours and gate the rest: **§1 (the "on" colour)** and **§2
(focus)**. Everything else (§3–§8) is mechanical alignment to the system once those are set —
snap spacing/type to the scales, tokenize the strays, add motion + elevation tokens, and make
every component consume the **text roles** and the **state tokens** rather than raw values.

Then the rule going forward: **no new component ships until it obeys this grammar** — that's
how "whole before detail" stays true as we finish Tiers 1 → 2 → 3.
