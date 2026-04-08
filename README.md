# Inter Fonts — Nextcloud App

[![License: AGPL v3+](https://img.shields.io/badge/License-AGPL_v3%2B-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
[![Inter font](https://img.shields.io/badge/font-Inter_v4-black.svg)](https://rsms.me/inter/)
[![Nextcloud](https://img.shields.io/badge/Nextcloud-27_to_32-0082c9.svg)](https://nextcloud.com)

Replaces every Nextcloud interface font with **[Inter](https://rsms.me/inter/)**, a screen-optimised
variable typeface by Rasmus Andersson.

All font files are bundled inside the app — **no Google Fonts, no CDN, no external
requests of any kind**.

---

## Screenshots

| Default | Dark blue | Red & green |
|---|---|---|
| <img alt="Default theme" src="https://github.com/user-attachments/assets/7cad445e-e011-4f4a-b211-e3d9fb2315f4" /> | <img alt="Dark blue theme" src="https://github.com/user-attachments/assets/8c1c9d84-d553-4a00-8e6b-dd3d26b2b94a" /> | <img alt="Red & green theme" src="https://github.com/user-attachments/assets/f703ed8a-9cd1-488b-8418-5f6bfc3e9c93" /> |

## Why this app

Nextcloud ships with the system font stack (`-apple-system`, `Segoe UI`, `Roboto`, …),
which means every user sees a slightly different interface depending on their OS and
which fonts happen to be installed. **Inter** is a single, consistent, screen-tuned
typeface designed for exactly this kind of UI work — and this app makes it the only
font Nextcloud uses, everywhere.

## Features

- **Inter v4 variable font** — one WOFF2 file covers every weight from 100 to 900
  (roman + italic), in ~340 KB each
- **Zero external requests** — fonts are bundled and served by an internal route
- **Zero FOUT** (Flash of Unstyled Text) — both WOFF2 files are preloaded with
  `<link rel="preload" as="font" crossorigin>` so they're in cache before first paint
- **Zero CLS** (Cumulative Layout Shift) — a metric-compatible synthetic Arial
  fallback matches Inter's vertical metrics, so the swap-in is invisible
- **Works on every page** — login form, public share pages, password recovery,
  Impersonate sessions, Guests app, and the entire authenticated UI
- **Sub-directory-safe** — all URLs are generated through `IURLGenerator::linkToRoute()`,
  so the app works whether Nextcloud is at `/` or `/cloud/`
- **Cache-safe across upgrades** — font filenames embed the upstream Inter version
  (e.g. `InterVariable-4.1.woff2`), so a font upgrade automatically becomes a new URL
- **Tabular numerals** on file sizes and timestamps so digits stay vertically aligned
- **Inter contextual alternates and standard ligatures** enabled by default

## Installation

```bash
# 1. Clone into the apps directory
sudo -u www-data git clone \
  https://github.com/solracsf/nc-interfonts \
  /var/www/nextcloud/apps/interfonts

# 2. Enable the app
sudo -u www-data php /var/www/nextcloud/occ app:enable interfonts
```

That's it. Hard-refresh your browser (Ctrl+Shift+R / Cmd+Shift+R) and Nextcloud
is now in Inter.

> Tagged release tarballs are also available on the
> [Releases page](https://github.com/solracsf/nc-interfonts/releases).

## Upgrading

```bash
cd /var/www/nextcloud/apps/interfonts
sudo -u www-data git pull
sudo -u www-data php /var/www/nextcloud/occ app:disable interfonts
sudo -u www-data php /var/www/nextcloud/occ app:enable interfonts
```

The disable/enable cycle forces Nextcloud to re-read `appinfo/info.xml` and
flush the bootstrap cache. Versioned font filenames mean browsers automatically
fetch the new files — no manual cache clearing needed.

## Uninstalling

```bash
sudo -u www-data php /var/www/nextcloud/occ app:remove interfonts
```

## How it works

The app is intentionally minimal — three PHP files in `lib/` that do exactly one job
each:

| File | Role |
|---|---|
| `lib/AppInfo/Application.php` | Bootstraps the app and registers the listener. Holds the helpers that map the bundled Inter version → font filenames. |
| `lib/Listener/BeforeTemplateRenderedListener.php` | On every `BeforeTemplateRenderedEvent` (login, public shares, all authenticated pages), injects three `<link>` tags into `<head>`: two `rel="preload"` for the WOFF2 files and one `rel="stylesheet"` pointing at the controller below. |
| `lib/Controller/CSSController.php` | Generates `text/css` at request time with two `@font-face` rules for `InterVariable`, a metric-compatible synthetic fallback, and the overrides that swap Nextcloud's font variable. URLs use `IURLGenerator::linkToRoute()` so they survive sub-directory installs. |
| `lib/Controller/FontController.php` | Streams the bundled WOFF2 binaries with `Cache-Control: immutable, max-age=1y` and `Access-Control-Allow-Origin: *` (required so the preload matches the eventual font request). Filenames are validated against an allowlist generated from the bundled Inter version. |

### Why not just `Util::addStyle()`?

`Util::addStyle()` routes through `CSSResourceLocator`, which expects a real file on
disk and silently drops anything else. A controller URL is not a file, so the
stylesheet would never be injected. `Util::addHeader()` writes the `<link>` tag
verbatim, which is what we want.

### Why not a static `.css` file?

`@font-face`'s `src: url(...)` token must be a literal — `var()` is forbidden.
And static app CSS is served by Nextcloud through a hashed cache pipeline, so any
relative font path inside it resolves against the cache URL rather than the app
folder, and 404s. Generating the `@font-face` block inside a controller with
`linkToRoute()` is the only way to get a stable, sub-directory-safe URL.

### Why is the route `/stylesheet` and not `/css`?

Apache and nginx both check `is_dir()` *before* running their rewrite rules. If the
route URL collided with a real on-disk app directory (like `css/`), the web server
would try to serve the directory directly and return 404 before Nextcloud's router
ever saw the request.

## File layout

```
nc-interfonts/
├── appinfo/
│   ├── info.xml         # App store manifest
│   └── routes.php       # Two routes: /stylesheet, /font/{filename}
├── fonts/
│   ├── InterVariable-4.1.woff2          # Roman variable font
│   ├── InterVariable-Italic-4.1.woff2   # Italic variable font
│   └── inter-version.txt                # Upstream Inter version
├── lib/
│   ├── AppInfo/Application.php
│   ├── Controller/CSSController.php
│   ├── Controller/FontController.php
│   └── Listener/BeforeTemplateRenderedListener.php
├── CHANGELOG.md
├── LICENSE
└── README.md
```

## Credits

- **Inter typeface** by [Rasmus Andersson](https://rsms.me/inter/) — released under
  the [SIL Open Font License 1.1](https://scripts.sil.org/OFL)
- Metric-fallback overrides for Inter calibrated against
  [Vercel's `@next/font`](https://github.com/vercel/next.js/tree/canary/packages/font)
  reference values

## Licenses

| Component | License |
|---|---|
| App source code | [AGPL-3.0-or-later](https://www.gnu.org/licenses/agpl-3.0.html) |
| Bundled Inter font | [SIL Open Font License 1.1](https://scripts.sil.org/OFL) |
