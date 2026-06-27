# tokens/src — PLACEHOLDER VALUES

> ⚠️ **The values in these files are scaffolding, not design decisions.**
>
> They exist only to prove the pipeline builds end-to-end (W3C JSON → CSS variables + TS).
> The real color, type, spacing, radius, elevation, and motion values are a later,
> deliberate decision made against the three real product surfaces — see
> [`../ARCHITECTURE.md`](../ARCHITECTURE.md). Do not treat anything here as final.

Structure follows the three-tier model:

```
primitive/   raw values  (color.neutral.900, space.200, font.size.300)
semantic/    roles        (color.text.default → {color.neutral.900})
component/   per-component knobs (added later, sparingly)
```
