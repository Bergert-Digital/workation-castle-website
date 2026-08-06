# Workation Castle

The WordPress theme for [workationcastle.com](https://workationcastle.com) — an
Italian castle between Lake Como and Lake Lugano where teams work, gather and
unwind.

It is a standalone **Pediment client theme**. The
[Pediment plugin](https://github.com/Bergert-Digital/pediment) ships the design
system, the shared blocks, the block templates and the seeding engine; this
repository holds what is specific to this client:

- 23 bespoke blocks under `src/blocks/`
- the guest check-in flow (`inc/CheckIn.php`, `inc/Brevo.php`)
- the photo library and activities custom post types, and their content
  manifests (`inc/photos-manifest.php`, `inc/activities-manifest.php`)
- the site's structure and page content — `seed/manifest.php` plus `patterns/`

It is **not** a child theme. Everything it used to inherit from the `pediment`
parent theme now comes from the plugin, so there is no parent to install.

## Install on a fresh WordPress

1. Install and activate the **Pediment plugin** (Plugins → Add New → Upload,
   `pediment-plugin.zip` from the plugin's latest release).
2. Install and activate **this theme** (Appearance → Add New → Upload,
   `workation.zip` from this repo's latest release — *not* GitHub's
   auto-generated "Source code" zip, which excludes the built blocks).
3. Install and activate **Polylang**, then run the seed (below).

There is no auto-updater. Updates are installed by uploading a new release zip
in wp-admin.

> `workation.zip` is what the release workflow attaches to each tag. Installing
> the "Source code" zip instead leaves `build/` and `vendor/` missing, which
> unregisters every block at once — the theme raises an admin notice saying so.

## Content

The site's structure lives in [`seed/manifest.php`](seed/manifest.php): 18
pages, 5 languages, and the two-level primary navigation. Each page's content
comes from a pattern in `patterns/`. Seed or re-seed with:

```bash
wp pediment seed --dry-run   # read the plan first
wp pediment seed
```

or from **Settings → Pediment → Seeding** in wp-admin, which is the only route
on admin-only hosting.

Seeding is idempotent and content-protected: a page someone has edited in the
editor is never overwritten. Re-running a converged site reports every entry as
`unchanged`.

The **entry list and its per-language overrides in `seed/manifest.php` are
generated** by `tools/manifest-from-wxr.mjs` from a WordPress XML export. Do not
hand-edit them — fix the generator and regenerate, so re-running it against a
fresh export stays a meaningful drift check. The `navs` section is hand-written,
because it encodes a structure the export does not contain.

The photo library and the activities are seeded separately, because the manifest
does not own them:

```bash
wp workation content
```

## Temporary: the block-namespace rewrite

[`inc/NamespaceRewrite.php`](inc/NamespaceRewrite.php) is a **cutover-only
tool**. The theme's blocks moved from `pediment-child/*` to `workation/*` in
1.0.0, so pages stored before that still name the old blocks. It is run once
from **Tools → Rewrite block namespace** immediately after the theme is
activated on the live site.

**Delete it — and its test — in the release that follows the cutover.**

## Development

`.wp-env.json` pins the published Pediment plugin release and Polylang. No local
clone of the plugin is required.

```bash
composer install
npm install
npm run env:setup   # boot, activate, configure languages, seed everything
npm run build       # build the client blocks
npm run e2e         # Playwright
npx wp-env run tests-wordpress --env-cwd=wp-content/themes/workation vendor/bin/phpunit
composer lint
npm run lint:js
```

Individual steps, all of which `env:setup` runs for you:

```bash
npm run env:start   # boot + activate theme and plugin
npm run languages   # configure Polylang from the manifest's language list
npm run seed:plan   # dry run
npm run seed        # pages and navigations
npm run seed:cpt    # the photo library and the activities
npm run env:stop
```

### Conductor workspaces and the theme directory name

wp-env mounts the theme at `wp-content/themes/<basename of the checkout>`. In a
Conductor workspace that basename is the workspace name, not `workation` — and
the slug matters, because the plugin seeds the branded header from the pattern
named `<stylesheet>/header`. Under the wrong slug it seeds a generic header
instead.

Point the mount at the right name with a gitignored `.wp-env.override.json`:

```json
{
  "themes": [],
  "mappings": { "wp-content/themes/workation": "." }
}
```

CI does not need this — it checks the repo out into a directory called
`workation`.

`env:start` also assigns this workspace a **random free port** on first boot and
persists it to the same file, so parallel workspaces do not collide. The URL is
printed at boot; Playwright's `baseURL` reads it too (`WP_BASE_URL` overrides).
In CI the port is left at wp-env's default `8888`, which is what Pediment's
reusable seed-check asserts against.

### Dev mode vs. publish mode

The committed `.wp-env.json` pins the published plugin release (**publish
mode**). To iterate against a local checkout of the Pediment monorepo's
`plugin/` directory instead:

```bash
npm run env:dev       # mount ../pediment/plugin
npm run env:publish   # back to the committed release pin
npm run env:mode      # report which mode is active
npm run env:start     # restart to apply
```

These only touch `.wp-env.override.json`, so the committed config is always
push-ready.

## Check-in form (guest registration)

The `/check-in/` page renders the `workation/check-in-form` block — a
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
