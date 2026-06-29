# Quire — Complete Component Audit (the honest, full list)

Earlier `component-inventory.md` was a **prioritised** list — the components that recur most.
This document is the **full** accounting: every UI control across the WordPress, WooCommerce,
and Jetpack admin screenshots, marked honestly. Nothing glossed over.

**Status key:** ✅ built · 🟨 partial (have a basic version, need variants) · ⬜ still to build ·
↘ consume from WordPress · ✕ cut.

`(seen)` = directly verified in a screenshot this audit. `(known)` = a control on a specific
named screen in the inventory we haven't re-opened pixel-by-pixel, but know is there.

---

## ✅ Built — ~28 (these cover the bulk of every screen)

**Forms:** Button · Input · Textarea · Select · Checkbox · Radio · Toggle · SettingsRow ·
TokenInput · MediaField (the *field*, not the picker)
**Data:** DataTable · Card · StatTile · ProgressBar · Checklist · ChartCard (bar only) · Badge/StatusPill
**Feedback/overlays:** Notice · Tooltip · Modal · Menu/Popover · EmptyState · PromoCard · Avatar · HelpTip
**Navigation:** Product rail + contextual sidebar · Tabs · PageHeader · Command palette

These are enough to give a unified look to the **everyday screens** — Dashboard, Posts/Pages,
Settings, Orders, Products, Customers, most of Jetpack.

## 🟨 Partial — have a basic version, need variants

- **ChartCard** — built as a bar chart; Analytics also needs a **line chart** and the
  **leaderboard** table (that one is just DataTable). `(seen: Analytics)`
- **StatTile** — built with a delta line; Analytics uses a small **% comparison chip** variant. `(seen)`

## ⬜ Still to build — the specialised controls our prioritised list skipped

These are real, but lower-frequency / more advanced. Roughly **10–12 items**:

1. **Segmented control / view-switcher** — list↔grid toggle, chart line↔bar toggle, interval. `(seen: Media, Analytics)`
2. **Media grid** — the thumbnail gallery view of the media library. `(seen: Media)`
3. **Date & date-range picker** — incl. the Analytics comparison range; post scheduling. `(seen: Analytics)`
4. **Line chart** + chart toolbar (interval select, type toggle, legend checkboxes). `(seen: Analytics)`
5. **Color / swatch picker** — Global Styles, theme colours, profile colour scheme. `(known)`
6. **Range slider** — e.g. image sizes / quality. `(known)`
7. **File upload / dropzone** — Add Media, Import, plugin/theme upload. `(known)`
8. **Accordion / collapsible section** — collapsible metaboxes, Site Health panels. `(known)`
9. **Stepper / wizard** — the WooCommerce onboarding profiler. `(known: onboarding)`
10. **Nested sortable list / tree** — category hierarchy; the classic menu editor (note: block
    themes are moving this into the site editor, so lower priority). `(known)`
11. **Code editor** — theme/plugin file editors. Power-user; lowest priority. `(known: 06-appearance/04, 07-plugins/03)`
12. **Zone/region editor** (WooCommerce shipping zones) — likely *composed* from existing pieces
    (rows + a multi-select), not a brand-new primitive. `(known: 09-settings/06-shipping)`

## ↘ Consume from WordPress — don't build

- **Block editor / Site editor / rich text** (Gutenberg) → extend `@wordpress/components`.
- **Media picker modal** → WordPress' media library (we build the field; WP provides the picker).

## ✕ Cut

- **Admin bar** → folded into the product rail.

---

## The honest count

| Bucket | Count |
| --- | --- |
| ✅ Built | ~28 |
| 🟨 Partial (variants to add) | 2 |
| ⬜ Still to build (specialised) | ~10–12 |
| ↘ Consume from WordPress | 2 |
| ✕ Cut | 1 |

**So: the common set is done; ~10–12 specialised controls remain.** They're lower-frequency or
power-user, and because the foundation (tokens) + the rules are already set, each one is built by
assembling existing parts — it'll match automatically and won't change anything already built.

**Priority of the remaining ⬜, for everyday-owner scope:** segmented control, media grid,
date picker, file upload, and accordion are the most worth doing soon (they appear on common
screens). Line chart / charts, stepper, tree, code editor are more occasional / advanced.
