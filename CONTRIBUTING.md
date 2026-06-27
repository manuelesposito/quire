# Contributing to Quire

Thank you for helping build a calmer WordPress admin. Quire is a design-system-first
project, and it only works if everyone holds one line:

> **Everything composes from the design system. Nothing hardcodes a value or forks a
> component.**

## The five rules (non-negotiable)

1. **No hardcoded design values.** Colors, spacing, radius, type, elevation, and motion come
   from `@quire/tokens` — never a raw hex, px literal, or magic number in a component. This
   is lint-enforced.
2. **Compose, never fork.** If `@quire/components` doesn't fit, *evolve the component* (with
   an RFC if it's a shared change) — don't copy-and-tweak it into a one-off.
3. **Dependencies point one way:** `apps/*` → `packages/*`. Never the reverse. `packages/*`
   must not know how they'll be delivered.
4. **Respect the Gutenberg boundary.** On React-Gutenberg surfaces, extend
   `@wordpress/components`; reserve full custom components for classic/PHP surfaces. See
   `ARCHITECTURE.md`.
5. **Prove against all three surfaces.** A token or component change must look right on
   `demo-core`, `demo-woo`, and `demo-jetpack` before it lands.

## Setup

```bash
corepack enable
pnpm install
pnpm dev          # run docs + demos
pnpm lint         # includes the no-hardcoded-values check
pnpm test
```

Node 20+ required (see `.nvmrc`).

## Workflow

1. Open an issue describing the change (use the templates in `.github/ISSUE_TEMPLATE`).
2. For anything touching tokens or a shared component's API, **file an RFC first** —
   see `GOVERNANCE.md`.
3. Branch from `main`, keep changes focused.
4. Run `pnpm lint && pnpm test` and check the three demo surfaces.
5. Open a PR. Describe *which surfaces you verified* and link the issue/RFC.

## Commit style

Conventional commits are appreciated (`feat:`, `fix:`, `docs:`, `refactor:`, `chore:`),
scoped where useful (`feat(tokens): …`, `fix(components/Button): …`).

## What makes a great Quire contribution

- It removes a divergence between WP, WooCommerce, and Jetpack.
- It makes a screen *calmer*, not busier.
- It moves a value *into* tokens, or a one-off *into* a shared component.

## Code of conduct

By participating you agree to uphold our [Code of Conduct](./CODE_OF_CONDUCT.md).

## License

Contributions are licensed under [GPL-2.0-or-later](./LICENSE).
