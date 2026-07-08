# apps/delivery-plugin — the Quire WordPress plugin

The delivery adapter (Lane architecture: see `/DELIVERY.md`). A plain WordPress
plugin that carries the tokens + reskin bridges and an off switch on
Settings → General. It owns no styles — `assemble.py` copies them in from
`@quire/tokens` (built) and `@quire/reskin-css`:

```sh
corepack pnpm --filter @quire/tokens build
python3 apps/delivery-plugin/assemble.py
```

Test against a live WordPress (same harness as the probe):

```sh
npx @wp-playground/cli server --port=8882 --login \
  --blueprint=blueprint.json \
  --mount=./apps/delivery-plugin/quire:/wordpress/wp-content/plugins/quire
# blueprint.json: { "login": true, "steps": [ { "step": "activatePlugin", "pluginPath": "quire/quire.php" } ] }
```

Verified 2026-07-09 against WP 7.0: canvas/menu/buttons/links token-exact,
off switch present. Known long tail on file: the Welcome banner (core
hardcodes its dark panel), classic hover microstates.
