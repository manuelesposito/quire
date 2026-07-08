# Playground probe — testing the token-injection thesis on a live wp-admin

**Date:** 2026-07-08 · **Status:** complete · **Verdict:** split — thesis holds on React surfaces, dead on classic screens.

## The question

The delivery architecture bets that Quire can reach users as a *reskin plugin*:
inject one token layer and the admin re-dresses itself. Until now that was an
architecture claim tested only against our own hand-written mockups. This probe
injected the real built tokens into a real, live WordPress (wp-playground,
WP latest, PHP 8.3) and measured what actually changed.

## Method

An mu-plugin (`mu-plugins/quire-probe.php`) enqueues the tokens in two
independently toggleable layers (`?quire=0/1/2`, cookie-persisted):

- **Layer 1 — "free":** only WordPress's own theming hooks remapped to Quire
  values (`--wp-admin-theme-color` + shades, `--wp-components-color-accent`
  + shades). Zero selectors.
- **Layer 2 — "cheap":** + one deliberately small bridge stylesheet
  (`layer2-bridge.css`, ~100 lines) mapping the big classic surfaces —
  canvas, admin menu, admin bar, buttons, boxes, list tables, inputs, fonts.

Measured by screenshotting Dashboard / Posts / Settings→General at each layer
and diffing pixels in pure Python (`analyze.py`) — plus a human check of the
block editor (too heavy for headless capture).

## Results

| Screen (classic) | Layer 1: hooks only | Layer 2: + ~100-line bridge |
|---|---|---|
| Dashboard | **0.0% pixels changed** | 57.5% changed |
| Posts list | **0.0% pixels changed** | 86.9% changed |
| Settings → General | **0.0% pixels changed** | 88.5% changed |

Sampled colours confirm the bridge lands token-exact: body `#f0f0f0 → #e8e5dc`
(canvas putty, exact), admin bar `#1e1e1e → #211c14` (ink, exact), admin menu
charcoal → warm light nav.

**Block editor (human-verified):** at Layer 1 the accent turned **ochre** —
the React surface consumes the hooks. But hover states in the classic sidebar
still **flashed WP blue** (`#72aee6`-family), because classic hover/focus
microstates are hardcoded and the small bridge doesn't chase them.

## What this means

1. **"Retint for free" is a myth on the classic admin.** WP's own theming
   hooks are not consumed by classic screens at all (0.0% three times). Any
   claim that the whole admin re-skins through CSS variables is false where
   everyday owners spend most of their time.
2. **The bridge is cheap and lands exactly.** ~100 lines transformed 57–88%
   of the pixels with token-perfect values. The planned `reskin-css` package
   is therefore the **load-bearing** piece of delivery, not a fallback.
3. **The React lane works as hoped.** Setting the official hooks re-accents
   the block editor (and by extension the component-based screens). Delivery
   is a two-lane road: hooks for React surfaces, reskin-css for classic.
4. **The real cost lives in the long tail.** The blue hover flash is the
   shape of the ongoing work: hundreds of hardcoded microstates (hover, focus,
   notices, per-plugin screens) that only enumeration + overrides can catch.
   Budget for the tail, not the happy path.
5. **A grammar limitation, found early:** WP couples links + buttons + focus
   into ONE hook, so Quire's links-ochre / primary-ink split cannot be
   expressed on React surfaces without deeper overrides.

## Reproduce

```sh
cd experiments/playground-probe
npx @wp-playground/cli server --port=8881 --login \
  --mount=./mu-plugins:/wordpress/wp-content/mu-plugins
# then browse:  /wp-admin/?quire=0  ·  ?quire=1  ·  ?quire=2
python3 analyze.py            # pixel diffs (expects *-q{0,1,2}.png screenshots)
```

## Next probes (not yet run)

- WooCommerce React screens (Home/Analytics) at Layer 1 — does the accent hook
  reach them like the editor?
- Dark mode on classic screens — recoloring server-rendered markup we don't own.
- Long-tail inventory: enumerate the hardcoded classic states (hover/focus/
  notices) to size the real reskin-css effort.
