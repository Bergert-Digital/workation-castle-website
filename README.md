# pediment-child-theme

The agency starting point. A child theme of [Pediment](https://github.com/bergert/pediment). Fork or download as a zip, rename it, add your blocks and `theme.json` overrides, and push to your own git for per-client install.

## Install order on a fresh WordPress

WordPress has no automatic theme-dependency resolution, so order matters:

1. Upload and install the **parent**: `pediment` zip (Appearance → Add New → Upload).
2. Upload and install **this child** theme zip.
3. **Activate the child** (`Pediment Child Theme`).
4. Install the **pediment-ai** plugin zip any time (Plugins → Add New → Upload).

## Overriding the Pediment design per client

This child theme ships **no `theme.json` `settings`** on purpose: it inherits
the parent (`pediment`) Pediment design system as-is — Deep Cyan
accent, Plus Jakarta Sans, the navy/surface palette. Child-theme sites get the
locked look with zero configuration.

To re-skin a client, add a `settings` block back to `theme.json`. WordPress
merges child `theme.json` over the parent **per top-level subtree, not per
slug**: a subtree you omit entirely (e.g. no `typography` key) keeps all its
Pediment values, but any preset **array you declare — `color.palette`,
`typography.fontFamilies`, `fontSizes`, … — replaces the parent's array
wholesale**. So when you declare `palette`, copy the parent's full Pediment
palette and edit only the entries you want; slugs you leave out (including
`accent-tint`) disappear on that site. Web fonts additionally need a
`fontFace` array with `src` on the family.

Abbreviated example (`theme.json`) — in practice paste the parent's complete
`palette`/`fontFamilies` and change only the values you need:

```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 2,
  "settings": {
    "color": {
      "palette": [
        { "slug": "accent",       "color": "#B91C1C", "name": "Accent" },
        { "slug": "accent-hover", "color": "#991B1B", "name": "Accent hover" }
      ]
    },
    "typography": {
      "fontFamilies": [
        { "slug": "heading", "name": "Heading", "fontFamily": "Georgia, serif" }
      ]
    }
  }
}
```

Rule of thumb: omit a subtree to keep Pediment; declare an array and you own all of it.

## First-fork rename checklist

Grep-replace these tokens with your client's identity before first client ship:

- `pediment-child-theme` → your theme slug (also rename the repo/directory)
- `Pediment Child Theme` → your theme's display name (`style.css` `Theme Name`)
- `pediment-child` → your text domain (in `style.css`, `functions.php`, `block.json`, `edit.tsx`, CSS classes)
- `PedimentChild` → your PHP `@package` tag
- `pediment_child_register_blocks` / `PEDIMENT_CHILD_*` → your prefixed function/constant names

Then **replace or delete** `src/blocks/promo-banner/` — it's a worked example, not production content.

## Development

`.wp-env.json` is configured for the **agency-dev workflow**: it points at the latest tagged release of `Bergert-Digital/pediment` (parent) and `Bergert-Digital/pediment-ai` (plugin) on GitHub. Running `npm run env:start` downloads those release zips into the container — no local clone of parent/plugin required, no auth required (both are public repos).

```bash
composer install
npm install
npm run env:setup            # boots wp-env, activates this child, seeds demo content
npm run build                # build child blocks
npm run e2e                  # Playwright
npx wp-env run tests-wordpress --env-cwd=wp-content/themes/pediment-child-theme vendor/bin/phpunit
composer lint
npm run check:wpenv-deps     # verify .wp-env.json refs are at latest upstream tags
```

`env:start`/`env:setup` assign this workspace a **random free port** on first boot and persist it to the gitignored `.wp-env.override.json` (reused on every later boot, so the URL stays stable). This keeps parallel Conductor workspaces from all colliding on the default `8890`. The chosen URL is printed at boot; Playwright's `baseURL` reads the same file (override `WP_BASE_URL` to point elsewhere). Delete `.wp-env.override.json` to re-roll the port.

### Dev mode vs. publish mode

The committed `.wp-env.json` always pins the published release zips (**publish mode**) — that's the push-ready config and the one CI's currency check validates. For parallel development across the three repos, switch to **dev mode**, which mounts the sibling working copies (`../pediment`, `../pediment-ai`) instead:

```bash
npm run env:dev          # mount sibling working copies (fast local iteration)
npm run env:publish      # back to the committed release-zip pins
npm run env:mode         # report which mode is active
npm run env:start        # restart to apply (required after switching)
```

These commands only toggle `themes`/`plugins` in `.wp-env.override.json` (gitignored; other keys like `ANTHROPIC_API_KEY` are preserved). Because the dev paths live only in the override, **the committed `.wp-env.json` can never accidentally pick up local paths — every push is publish-ready by default.** `wp-env` fully replaces the base `themes`/`plugins` arrays with the override's. CI uses the same trick — see [`.github/workflows/ci.yml`](.github/workflows/ci.yml).

### Keeping `.wp-env.json` current

A scheduled workflow ([`.github/workflows/check-wpenv-deps.yml`](.github/workflows/check-wpenv-deps.yml)) runs every Monday, checks the upstream repos for newer tags, and opens a PR bumping the refs when they fall behind. You can also run the check manually any time:

```bash
npm run check:wpenv-deps
```

## Check-in form (guest registration)

The `/check-in/` page renders the `pediment-child/check-in-form` block — a
multi-step wizard collecting guest + ID data for the Italian authorities
(AlloggiatiWeb / ISTAT). Submissions are stored as private `wc_checkin` posts
(admin-only) and a readable summary is emailed to `info@workationcastle.com`
via the Brevo transactional API.

**Required configuration (production):**

- Define the Brevo API key in `wp-config.php`:
  `define( 'WORKATION_BREVO_API_KEY', 'xkeysib-…' );`
  (or set the `BREVO_API_KEY` environment variable).
- Verify `noreply@workationcastle.com` as a sender in Brevo.

If the key is absent the form still works and stores the submission; the email
is skipped and logged (the submission's "Email" column shows `skipped`).
