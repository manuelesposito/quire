# The Figma mirror

The tokens live in Figma as native variables so the system can be reviewed
and used where a designer's eye works best — **but Figma is a generated
mirror, never a second source of truth.** Change the JSON in `src/`,
rebuild, re-sync. Never edit the variables in Figma by hand.

**File:** "Quire — Tokens" — https://www.figma.com/design/JD565ifTjv534gnI62lNzS

## Structure (mirrors the 3-tier architecture)

| Figma | Mirrors | Notes |
|---|---|---|
| Collection **Primitives** (mode: Value) | `src/primitive/` | 98 vars (system v2: the 10-step gray contract + alpha twins + 4 status hues). Colours hidden from property pickers — the semantic layer is the API. |
| Collection **Semantic** (modes: Light, Dark) | `src/semantic/` + `src/modes/dark/` | 105 vars. Values are **aliases** to Primitives, exactly like the CSS `var()` chain; Dark carries the full neutral-world remap. |
| Effect styles **Elevation/Raised · Tooltip · Menu · Overlay** | `elevation.*` | The materials presets. Shadows can't be variables in Figma. |
| Text styles **Display … Caption** (7) | `text.*` composite roles | All Inter (system v2 — one typeface); family + size BOUND to the semantic variables; weight/line-height resolved (Figma limits). |
| — (not mirrored) | `motion.*` (duration/easing) | Not representable in Figma. |

Every variable carries WEB code syntax (`var(--qr-…)`), so Figma's Dev Mode
shows the real CSS custom property name.

Known Figma limitations, accepted: `LETTER_SPACING` scope is FLOAT-only, so
the em-string letter-spacing tokens exist but are hidden from pickers.

**Note:** the mirror was fully REBUILT 2026-07-09 for system v2 (the clean-neutral pivot); the old warm-world collections and the Palette page were removed (the palette can be regenerated on demand).

## How it was synced / how to re-sync

1. `python3 scripts/figma-payloads.py <outdir>` flattens the sources into
   four payload JSONs (see the script header).
2. A Claude session pushes them into the file via the Figma MCP
   (Plugin API `figma.variables.*`): create-if-missing by variable name,
   aliases resolved through a name → variable lookup.

The push half currently lives in the session, not as a checked-in script —
if re-syncing becomes frequent, port it to a Figma REST/plugin script.
After changing token values (not names), a re-sync must UPDATE existing
variables (`setValueForMode`) rather than skip them — tell the session
whether names, values, or both changed.
