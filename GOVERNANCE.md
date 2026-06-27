# Quire — Governance

Quire is a design system shared across three surfaces (WordPress core, WooCommerce,
Jetpack). With multiple surfaces and contributors, **the first hardcoded hex or forked
component is the beginning of the end.** Governance exists to keep the source of truth
canonical over time.

## What requires an RFC

Open a lightweight RFC (copy `governance/rfcs/0000-template.md`) before:

- Adding, removing, or changing a **design token**.
- Changing the **public API** of a shared component or primitive.
- Adding a **new shared component** to `@quire/components`.
- Changing the **Gutenberg boundary** (what we extend vs. build custom).
- Introducing a **new delivery adapter** under `apps/delivery-*`.

Small, surface-local fixes that don't change a token or a shared API do **not** need an RFC —
just a PR.

## RFC process

1. Copy `governance/rfcs/0000-template.md` → `governance/rfcs/NNNN-short-title.md`.
2. Fill in: problem, proposal, alternatives, impact on all three surfaces.
3. Open a PR with the RFC. Discussion happens on the PR.
4. A maintainer merges when there's rough consensus and the three-surface impact is
   understood. Merged RFC = the decision of record.

## Mechanical enforcement (so governance isn't just vibes)

- **Lint** bans raw color/spacing/radius literals outside `@quire/tokens`.
- **Visual snapshots** of every state in the inventory guard against silent drift.
- **CI** validates token/component changes against `demo-core`, `demo-woo`, `demo-jetpack`.

## Versioning across three upstreams

WordPress core, WooCommerce, and Jetpack each release on their own cadence. Every change is
validated against all three demo surfaces in the same monorepo, so breakage is visible
*before* it lands rather than discovered in the wild.

## The brand-accent escape hatch

Marketing-owned color (e.g. Jetpack's green) is a recurring source of divergence. It is
permitted **only** as a single, governed `accent` token added via RFC — never re-hardcoded
per surface. If multi-brand accents aren't sanctioned, they don't exist.

## Decision-making

Early stage: decisions are made by the maintainers via rough consensus on issues, PRs, and
RFCs. This document will grow into a fuller model as the contributor base grows.
