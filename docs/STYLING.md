# Styling this site

You are working in **`workation`**, a standalone client theme. The Pediment plugin
supplies the design system; this repo supplies everything specific to the client.

```
Site Editor edits  (DB: wp_global_styles, …)              ← a person's own edits, highest precedence
        ▲
Site styles        (DB: private pediment_style posts)     ← written by AI through MCP, revisioned
        ▲
This client theme  (workation)                            ← theme.json, styles/blocks/*.json, patterns/
        ▲
Pediment plugin    (installed at wp-content/plugins/pediment-plugin/)  ← default named styles, read-only
```

The rule: **defaults come from code, overrides come from the database.** A named style
resolves plugin < theme < site; a person's Site Editor edit sits above all three. If
nothing sets a property, WordPress core's default wins.

## A note on the plugin

The Pediment plugin is **installed software**, not source code in this repo. You can
browse its files at:

- **Locally:** `wp-content/plugins/pediment-plugin/` in your WordPress install (inside
  `wp-env`: `npx wp-env run cli wp plugin path pediment-plugin`). Read `styles/*.json`
  (the default named styles), `build/blocks/<block>/` and `templates/`.
- **Upstream source:** <https://github.com/Bergert-Digital/pediment>. The design layer is
  documented in `docs/design-layer.md` there, the legacy-block conversion in
  `docs/convert.md`.

You **don't edit** the installed plugin: the next plugin update overwrites it. To change
a framework default, file an issue or PR upstream. To override it on **this** site, do
so from this repo.

There is no parent theme. `theme.json` in this repo is complete: it declares the whole
palette, type scale and spacing scale.

## Decide where to make a change

| Scope of the change | Layer | Where to edit |
|---|---|---|
| Per-page / per-template content (copy, menu items, swap a block) | **DB** | Site Editor (Appearance → Editor) |
| Per-site design token (this client wants a green accent) | **Client theme** | `theme.json` in this repo |
| The look of a named style on this site (band, card, panel, …) | **Client theme** | `styles/blocks/<slug>.json` in this repo |
| A new named style | **Client theme** | `styles/blocks/<slug>.json` with a new slug |
| New site-specific CSS rule | **Client theme** | `theme.json` `styles.css`, or a stylesheet enqueued from `functions.php` |
| Framework default (should ship to every Pediment site) | **Upstream** | Issue or PR against the Pediment monorepo |
| New block / bug in a Pediment block | **Upstream** | Same |

When in doubt for a design tweak, **default to this repo.** Reach upstream only when the
change is genuinely framework-level.

## Compose sections from core blocks

Pages are built from **core blocks plus named block styles** (a style is the
`is-style-<slug>` class on the block). Pediment 4 deprecated the 18 presentation blocks
in `Policy::LEGACY` (`pediment/hero, cta, feature, feature-grid, stat, stat-grid, step,
steps, section-head, media-text, testimonial, testimonial-grid, logo-cloud, pull-quote,
image-caption, prose, faq, faq-item`). They still render old content, but the inserter
hides them and Pediment 5 deletes them. Never use them in new markup.

Not legacy: `pediment/form`, `form-field`, `slider`, `mega-menu`, `social-links`,
`blog-index`. From Pediment 4.3.0 there is also `pediment/icon` (a Phosphor icon; the
`tile` style is its default).

Where you would have reached for a legacy block, compose this instead
(the converter writes the same trees, see `convert.md` upstream):

| Instead of | Compose |
|---|---|
| `section-head` | Constrained group with the class `pediment-section-head`: `eyebrow` paragraph, heading, `lead` paragraph |
| `hero` | Section group (or `media-card` cover for a background image): h1, `lead` paragraph, `pill` button (+ `pill-ghost`) |
| `cta` | `panel` group: h2, paragraph, `pill` + `pill-ghost` buttons |
| `feature-grid` + `feature` | Grid group of `card` groups: image, h3, text, link paragraph (class `pediment-card-link` from 4.3.0) |
| `stat-grid` + `stat` | Grid group of `stat` groups: value first, label second, optional context third |
| `steps` + `step` | `steps` grid group of groups: h3, text (the style numbers them) |
| `media-text` | Core media & text block with the `media-row` style |
| `testimonial-grid` + `testimonial` | Grid group of `quote-card` groups: quote, byline |
| `logo-cloud` | `logos` group: caption, wrapping row of logo images |
| `pull-quote` | Core quote with the `pull-quote` style |
| `image-caption` | Core image with the `framed` style |
| `prose` | Constrained group with the `prose` style |
| `faq` + `faq-item` | Group of core details blocks with the `accordion` style |
| Section background | Group with the `band` or `band-alt` style |

Plugin default styles: `band`, `band-alt`, `card`, `eyebrow`, `lead`, `pill`,
`pill-ghost`, `media-card`, `stat`, `steps`, `panel`, `quote-card`, `accordion`,
`media-row`, `pull-quote`, `framed`, `logos`, `prose`. A `grid` collapses on phones when
you set `minimumColumnWidth` (for example `max(10rem, 30%)`) and leave `columnCount`
unset. On this site, `style.css` overrides the column layout of card grids
(`.wp-site-blocks .is-layout-grid:has(> .is-style-card)`, which covers
`.guide-cards`) and of `is-style-steps` grids (`.is-style-steps.is-layout-grid`)
to keep the pre-conversion layout, so the editor's column setting has no effect
on those grids on the front end.

**Never type design values into a block** (no literal colours, sizes, spacing, radius).
Use a preset (`var:preset|…`) or a named style. Pediment rejects a page that breaks
this when it is written through MCP.

### Change how a named style looks on this site

A named style is a block style variation: a slug, a title, the block types it applies to,
theme.json style data, and optional `css`. A theme file with the same slug as a plugin
style overrides it property by property, and a `css` string replaces the lower layer's
`css` completely. Put the file in `styles/blocks/`:

```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "title": "Card",
  "slug": "card",
  "blockTypes": [ "core/group" ],
  "styles": {
    "border": { "color": "var:preset|color|border-strong" },
    "spacing": { "padding": { "top": "var:preset|spacing|60", "bottom": "var:preset|spacing|60" } }
  }
}
```

Rules (Pediment checks them on every layer): colours, font sizes, font families,
spacing, radius and shadows use tokens (`var:preset|color|accent`,
`var(--wp--preset--…)`, `var(--wp--custom--…)`). In `css` write no `!important`, no
literal colours and no `<`; rules take the form `&::before{…}` or `&:hover{…}` with no
at-rules, so a style cannot hold a media query. WordPress wraps a style's selectors in
`:where()`, so its specificity is (0,1,0): give each look to one style on the block that
shows it. Invalid theme styles still load but are listed with warnings under
Settings → Pediment Theme → Design.

AI agents change the **site** layer with the `pediment-upsert-style` MCP tool. To move
site styles into this repo, use Settings → Pediment Theme → Design → **Download site
styles as theme files** (or `wp pediment design export` in wp-env), commit the files
under `styles/blocks/` and release the theme.

## How theme.json works in this theme

This theme's `theme.json` is **complete and standalone**: it is the only theme-level
source for the palette, type scale, spacing scale and block defaults. There is no parent
to inherit from, so:

- An array you declare (`settings.color.palette`) is the whole array. Removing a slug
  removes it from the site, and every `var:preset|color|<slug>` that uses it breaks.
- The plugin's named styles reference tokens by slug (colours such as `surface`,
  `surface-elevated`, `border`, `primary`; font sizes `xs`–`4-xl`; spacing and shadow
  presets). Keep those slugs when you re-skin, or the style loses that value.
- Site Editor edits (user origin) sit on top of `theme.json`.

## Common edits

### Change a color preset for this site

Edit the hex of the slug in `settings.color.palette` in this repo's `theme.json`. Keep
the slug.

### Change a default block style for this site (e.g. button radius, nav font weight)

Edit `styles.blocks.<block>.*` in this repo's `theme.json`.

### Change a global element default (h1 size, link color)

Same as block style: edit `styles.elements.<element>.*` in `theme.json`.

### Add raw CSS

Add a `styles.css` string to `theme.json`: it is emitted verbatim into
`global-styles-inline-css`. This is your escape hatch for things `theme.json` can't
express (e.g. `backdrop-filter`). For a rule that belongs to one named style, prefer the
style's own `css` field.

Avoid normal CSS files unless you have a real reason: they bypass the Site Editor
override path. If you do use a CSS file in this repo's `assets/`, enqueue it via
`functions.php`.

### Per-page tweaks

Use the Site Editor. Edits are saved to the DB and win over this theme. They survive
theme updates.

## The one tricky case: navigation item color

WordPress core ships (you can see it in `wp-content/wp-includes/blocks/navigation/style.css`):

```css
.wp-block-navigation .wp-block-navigation-item__content.wp-block-navigation-item__content {
  color: inherit;
}
```

Specificity `(0,3,0)`. This intentionally beats anything in `theme.json` (which is wrapped
in `:where()` at `(0,1,0)` via the `:root` prefix). The effect: **nav items inherit color
from the `.wp-block-navigation` wrapper**, not from any `elements.link.color.text` setting.

Practical consequences:

- **To change the idle nav color:** set `styles.blocks.core/navigation.color.text`
  (color on the wrapper). Items inherit. Works from this repo's `theme.json` and the
  Site Editor (Styles → Blocks → Navigation → Color → Text) — all standard override flow.
- **To change the nav hover or active-page color:** there's no wrapper-color equivalent
  for `:hover` or `[aria-current=page]`. Set those in this theme's `theme.json`
  `styles.css` with a doubled-class rule. **They're not Site-Editor-overridable.**
  For example:

  ```css
  .wp-block-navigation a.wp-block-navigation-item__content.wp-block-navigation-item__content:hover {
    color: var(--wp--preset--color--accent);
  }
  ```

  An equal-specificity rule later in `styles.css` wins by source order.

## Verifying a change

Curling the page and grepping inline `<style>` blocks tells you *"is the rule emitted?"*
but not *"does it win?"* Only **DevTools → Computed** answers the cascade question:

1. Open the page, right-click the element, **Inspect**.
2. **Styles** panel → **Computed** tab → filter for the property (e.g. `color`).
3. The resolved value, and the winning selector below it, is the truth.

If your edit doesn't seem to take effect, the answer is almost always "another rule has
higher specificity" or "WordPress cached the parsed theme.json." For the latter:

```
wp transient delete --all
```

(or bump the `Version:` in `style.css`).

## Built-in Pediment blocks

For the full list of available blocks (plugin + client) and when to use each, see the
generated catalog at [PEDIMENT-BLOCKS.md](./PEDIMENT-BLOCKS.md) (`npm run blocks:catalog`).
It lists the legacy blocks apart, marked do-not-use.

The look of a block comes from its named styles (see "Compose sections from core
blocks"). To customise one for this site, prefer, in this order:

1. A `styles/blocks/<slug>.json` file that overrides the plugin style with the same slug, or
2. A new named style with its own slug, or
3. A CSS rule in `theme.json` `styles.css` targeting the block's wrapper class.

Editing the plugin's source affects *every* Pediment site and is an upstream change.

## Page width & content layout

**`theme.json` is the single source of truth for width.** `settings.layout`
declares `contentSize` (720px) and `wideSize` (1200px); the side gutter comes
from `settings.useRootPaddingAwareAlignments: true` + `styles.spacing.padding`
(`clamp(20px, 4vw, 40px)`). **Never hardcode widths/gutters in CSS** — alias the
WordPress vars instead (see "the `.wc-wrap` container" below).

`wp:post-content` is rendered **`is-layout-constrained`** on `page.html` and
`front-page.html`. So on any page:

- A **bare** (un-aligned) block snaps to `contentSize` (720px).
- `align:wide` snaps to `wideSize` (1200px); `align:full` reaches the viewport.

So compose page patterns with native alignment — **no width wrapper needed**:

```html
<!-- wp:group {"align":"wide","className":"is-style-band"} -->
<div class="wp-block-group alignwide is-style-band">…</div>
<!-- /wp:group -->
```

A core group with a named style (here `band`) takes `align:wide` or `align:full` like any
other block, and `theme.json` caps it. A client block such as `workation/page-hero`
(`align:full`) works the same way.

**A custom block must emit its own alignment class to break out.** A constrained
`post-content` only widens children that carry `alignwide`/`alignfull`. The
client section blocks that render their own `<section>` therefore build the class
from the block's `align` attribute (`' align' . $align`), e.g. `page-hero` and
`photo-gallery` in `inc/WorkationSections.php`. (Don't use
`get_block_wrapper_attributes()` in those chrome functions — the unit tests call
them directly, with no block-render context, and it fatals on null.) Miss this
and the block collapses to 720px under constrained `post-content`.

### The `.wc-wrap` container

The bespoke full-bleed **band** blocks (`workation/workation-*`,
`page-hero`, `photo-gallery`) render fixed HTML with an inner content zone — and
the **header/footer** are raw `wp:html`, not blocks. Native constrained layout
only caps a block's *direct* children, so these can't lean on it for their inner
width; they keep an explicit container, `.wc-wrap` (and `.sec-head`). That container is **not** a hardcoded hack: its width
and gutter alias `theme.json` —

```css
:root { --wc-maxw: var(--wp--style--global--wide-size, 1280px); }
body  { --wc-gutter: var(--wp--style--root--padding-right, 22px); }
```

(`--wc-gutter` lives on `body`, not `:root`, because WordPress emits the
root-padding var on `body`.) So even the bespoke containers track `theme.json`.
Don't reach for `.wc-wrap` in a **page pattern** — use native alignment there;
`.wc-wrap` is only for the bespoke band/​header/​footer internals.

For vertical rhythm inside a constrained context use **padding**, not margin
(WordPress's flow/blockGap can zero stray child margins).

Always confirm the result by **rendering the page and looking at it** (a desktop
screenshot, plus a narrow viewport for the gutter, or measure the container's
`boundingBox().width` in an e2e test), not just by checking that the block
markup is present.

## Cascade summary (for reference)

```
Browser computed style
  ▲
  ├── wp_global_styles (DB) ........... a person's Site Editor edits — highest precedence
  │
  ├── Site styles (private pediment_style posts) ... AI through MCP, injected after theme styles
  │
  ├── This theme (this repo)
  │     ├── styles/blocks/*.json ..... named styles, override the plugin's by slug
  │     ├── theme.json styles.css .... verbatim, normal specificity
  │     ├── styles.{elements,blocks}.* ... wrapped in :where(), specificity (0,1,0)
  │
  ├── Pediment plugin
  │     ├── styles/*.json ............ default named styles (band, card, panel, …)
  │     ├── Built block styles (build/blocks/*/style-index.css)
  │
  └── WordPress core defaults
```
