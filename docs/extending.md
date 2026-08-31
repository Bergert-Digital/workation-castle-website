# Extending this theme

Notes for adding or adapting the custom `workation/*` blocks in `src/blocks/`.

## Marking a block as a section (`pediment.section`)

The parent theme runs a normalize pass over page content. When it finds a
top-level block that is not itself a section, it wraps it in a band so the
content lines up with the rest of the page. That is the right behaviour for a
paragraph or an image, but wrong for a block that is already a full-bleed band —
the normalizer would wrap a band inside another band and re-band it.

Some `workation/*` blocks *are* the section: they render their own full-width
background and inner padding, and they declare `"align": ["full"]`. Those blocks
must opt out of the wrap by telling the framework they are already a section:

```json
"supports": {
  "html": false,
  "align": [ "full" ],
  "pediment": { "section": true }
}
```

With `pediment.section` set, the normalizer leaves the block alone instead of
re-banding it.

### Which blocks carry it

- `workation/page-hero` — cinematic full-bleed hero for interior pages.
- `workation/activity-list` — full-width grid band of all activities.

Both are full-bleed bands, so both declare `pediment.section`.

### When to add it to a new block

Add `"pediment": { "section": true }` to a block's `supports` when **both** are
true:

1. The block declares `"align": ["full"]` (or otherwise paints its own
   full-width background), and
2. The block draws its own section chrome — background band plus vertical
   padding — rather than sitting inside a wrapper.

If a block is meant to flow inside a readable content column (like a paragraph),
do **not** set the flag; let the normalizer place it.

### Why this matters

Without the flag, a real structural edit on an activities page — reordering
blocks, adding a section, anything that triggers the normalize pass — re-bands
these blocks and corrupts the layout. The flag is the durable fix; it makes the
blocks normalize-safe on their own terms.

> Blocks build from `src/blocks/` into `build/blocks/` via `npm run build`.
> `build/` is gitignored, so edit the `block.json` under `src/blocks/` and
> rebuild — never hand-edit the build output.
