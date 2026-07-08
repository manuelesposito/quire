# The Figma mirror

The tokens live in Figma as native variables so the system can be reviewed
and used where a designer's eye works best — **but Figma is a generated
mirror, never a second source of truth.** Change the JSON in `src/`,
rebuild, re-sync. Never edit the variables in Figma by hand.

**File:** "Quire — Tokens" — https://www.figma.com/design/JD565ifTjv534gnI62lNzS

## Structure (mirrors the 3-tier architecture)

| Figma | Mirrors | Notes |
|---|---|---|
| Collection **Primitives** (mode: Value) | `src/primitive/` | 96 vars. Raw ramps & scales. Hidden from property pickers (`scopes: []` for colours) — the semantic layer is the API. |
| Collection **Semantic** (modes: Light, Dark) | `src/semantic/` + `src/modes/dark/` | 108 vars. Values are **aliases** to Primitives, exactly like the CSS `var()` chain. Dark mode carries the 41 overrides; everything else falls through to the same alias as Light. |
| Effect styles **Elevation/Raised, Elevation/Overlay** | `elevation.*` | Shadows can't be variables in Figma. |
| — (not mirrored) | `motion.*` (duration/easing) | Not representable in Figma. |

Every variable carries WEB code syntax (`var(--qr-…)`), so Figma's Dev Mode
shows the real CSS custom property name.

Known Figma limitations, accepted: `LETTER_SPACING` scope is FLOAT-only, so
the em-string letter-spacing tokens exist but are hidden from pickers.

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
