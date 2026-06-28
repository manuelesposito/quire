# Quire — Component Inventory (the backbone)

Derived from the real captured screens of **WordPress core**, **WooCommerce**, and **Jetpack**
(~199 states; see `README.md` for sources). This is the evidence-based catalog the design
system builds from. For each component it records: where it appears, its variants/states, the
**divergence** across the three products (the problem Quire exists to solve), and the **one
Quire target**.

> Read order: §1 is the headline (how badly things diverge today). §2–§5 are the catalog,
> grouped by role. §6 is the prioritized build order. §7 is the Gutenberg boundary.

---

## 1. The core problem, in numbers

The same element is built many different ways across the three products. Quire's job is to
collapse each row to **one**.

| Element | WordPress | WooCommerce | Jetpack | → Quire |
| --- | --- | --- | --- | --- |
| **Primary button** | blue filled | indigo filled | **black** filled (+ **green** brand) | **1** (ink) |
| **Secondary button** | blue outline | blue outline | (text links) | **1** (outline) |
| **Toggle** | — (uses checkboxes) | — (uses checkboxes) | **2 styles** (blue iOS + grey nested) | **1** |
| **Card / container** | square gray *metabox* | rounded React card | rounded card w/ icon chip | **1** |
| **Settings row** | `.form-table` label-left | label-left variant | card row: desc + toggle + ⓘ | **1** |
| **Sub-navigation** | inline menu + flyouts | horizontal *and* vertical tabs | top tab bar (underline) | **1 tab system** |
| **Status** | plain text ("Published") | **pill** (green "Processing") | text | **1 status pill** |
| **Notice** | left-border admin notice | green-tinted banner | gradient promo card | **1** |
| **List table** | `.wp-list-table` | same bones + pills/eye/tooltips | — | **1 DataTable** |

That's the whole thesis: **four-ish visual languages → one.**

---

## 2. App chrome & navigation

| Component | Appears in | Notes / divergence | Quire target |
| --- | --- | --- | --- |
| **Admin bar** (top) | all three (WP black bar: logo · site name · ⌘K search · comments · +New · "Howdy, admin" + avatar) | shared, but Quire reframes it | folded into the product-rail model |
| **Primary nav** | WP dark side menu w/ hover **flyouts** + inline-expanding submenus | the cramped 2-levels-in-one-rail problem | **product rail + contextual sidebar** (built) |
| **Secondary nav / tabs** | WP inline submenu; Woo **horizontal** settings tabs *and* **vertical** product-data tabs; Jetpack **top tab bar** (underline-active) | 4 different tab treatments | **`Tabs`** — one component, horizontal/vertical |
| **Page header** | title + primary action; WP adds Screen Options/Help; Woo/Jetpack add icon buttons | 3 treatments | **`PageHeader`** (title · actions · breadcrumb) |
| **Search** | list-table search box; global ⌘K; Jetpack search icon | 3 entry points | **`SearchField`** + command palette |

## 3. Data display

| Component | Appears in | Variants / states | Quire target |
| --- | --- | --- | --- |
| **List table** | WP Posts/Pages/Users; Woo Orders/Products/Customers | checkbox col · **sortable headers (↕)** · row-hover actions · **bulk-actions bar** · status-filter tabs ("All ǀ Published") · search · item count · top+bottom header repeat. Woo adds **status pills**, **preview eye**, **rich tooltips** | **`DataTable`** (one, with pill + row-action support) |
| **Card / panel** | WP metabox (square, collapse caret, move handle); Woo rounded React card (3-dot menu, dividers); Jetpack rounded card (header + hairline + icon chip) | collapsible · with-header · with-menu | **`Card`** (the metabox↔card unifier) |
| **Stat / KPI tile** | Woo "Total sales / Orders", Stats overview | value · label · delta · empty (N/A) | **`StatTile`** |
| **Chart container** | Woo Analytics (line/bar/leaderboard) | — | **`ChartCard`** |
| **Checklist / task list** | Woo Home: numbered onboarding ("1 Add products" active) + "Things to do next" (circle check + time) | numbered · checkable · active-step | **`Checklist`** + **`ProgressBar`** |

## 4. Forms & controls (the atoms)

| Component | Appears in | States / variants | Quire target |
| --- | --- | --- | --- |
| **Text input** | everywhere (WP settings, Woo product name/price, Jetpack) | default · focus (blue ring) · disabled · error | **`Input`** |
| **Textarea** | tagline, descriptions | — | **`Textarea`** |
| **Select** | WP role/language/timezone; Woo product-type/bulk/filters | native dropdowns | **`Select`** |
| **Number / unit** | Woo prices ($) | prefix/suffix | **`Input type=number`** |
| **Checkbox** | WP checkbox-group, "Anyone can register"; Woo Virtual/Downloadable, category list | checked · indeterminate | **`Checkbox`** |
| **Radio group** | WP date/time format (radio + label + **mono code tag** + live preview) | — | **`RadioGroup`** |
| **Toggle / switch** | **Jetpack only** — and in **two** styles (blue iOS + grey nested) | on · off · disabled | **`Toggle`** (one) |
| **Button** | everywhere — **the biggest offender** (WP blue · Woo indigo · Woo blue-outline · Jetpack black · Jetpack green) | primary/secondary/tertiary/destructive × idle/hover/active/focus/disabled | **`Button`** (warm-bookish: ink primary · ochre tertiary) |
| **Settings row** | WP `.form-table`; Woo variant; Jetpack card-row (desc + toggle + ⓘ) | label-left · with-help · with-tip | **`SettingsRow`** |
| **Tag / token input** | WP/Woo tags ("separate with commas" + Add) | — | **`TokenInput`** |
| **Media picker** | "Add Media", "Set product image", media modal | — | **`MediaField`** (opens Modal) |
| **Rich text editor** | Woo product description (TinyMCE); WP block editor | — | **consume Gutenberg** (see §7) |

## 5. Feedback & overlays

| Component | Appears in | Variants | Quire target |
| --- | --- | --- | --- |
| **Notice / banner** | WP left-border admin notice; the green "not secure" banner (in Woo too); inline messages | success · info · warning · danger · dismissible | **`Notice`** |
| **Status pill / badge** | Woo order status (green "Processing"); count badges (comments "1", menu "6") | status colors · count | **`Badge` / `StatusPill`** |
| **Tooltip** | Woo status tooltip (dark popover); ⓘ info tips | — | **`Tooltip`** |
| **Modal / overlay** | WP media, plugin/theme details, keyboard shortcuts; Woo order-preview | small/large · dismissible | **`Modal`** |
| **Popover / menu** | block options, **3-dot card menus**, flyouts | — | **`Menu` / `Popover`** |
| **Empty state** | "no items" list rows, blank panels | — | **`EmptyState`** |
| **Promo / upgrade card** | Woo "Get traffic stats w/ Jetpack"; Jetpack "Upgrade" | the governed brand-accent surface | **`PromoCard`** (RFC-gated) |
| **Avatar** | "Howdy, admin" + avatar | — | **`Avatar`** |
| **Help tip** | ⓘ icons, `?` help-tips | — | **`HelpTip`** |

---

## 6. Prioritized build order (frequency × leverage)

1. **`Button`** — appears on every screen; the worst divergence. *Start here.*
2. **Form atoms** — `Input`, `Select`, `Checkbox`, `RadioGroup`, `Toggle`.
3. **`SettingsRow`** — settings are a huge share of all three admins.
4. **`Card`** — the container that unifies metabox vs. React card.
5. **`DataTable`** — every list screen.
6. **`Notice`** + **`Badge`/`StatusPill`**.
7. **`Tabs`** + **`PageHeader`**.
8. **`Menu`/`Popover`**, **`Modal`**, **`Tooltip`**.
9. **`Checklist`/`ProgressBar`**, **`StatTile`**, **`PromoCard`**, **`Avatar`**, **`HelpTip`**.
10. Editor — **consume** `@wordpress/components`, don't rebuild.

A first vertical slice of **Button + Input + SettingsRow + Card + Notice** already renders a
believable settings page — which is why those lead.

## 7. The Gutenberg / `@wordpress/components` boundary

The rich-text editor and the block/site editors are Gutenberg. Per `DESIGN.md`, Quire does
**not** rebuild them — it extends `@wordpress/components` on those React surfaces (mapping
`--qr-*` tokens onto `--wp-components-*`) and reserves full custom components for the
classic/PHP surfaces catalogued above.

---

*This inventory is the backbone. Components get built from it (§6 order), driven by the
`@quire/tokens` foundation, and made visible in a component gallery so each can be seen and
felt in every state.*
