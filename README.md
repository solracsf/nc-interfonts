<!--
  - SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Inter Fonts — Nextcloud App

[![Nextcloud](https://img.shields.io/badge/Nextcloud-32_to_35-0082c9.svg)](https://nextcloud.com)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/solracsf/nc-interfonts)
[![License: AGPL v3+](https://img.shields.io/badge/License-AGPL_v3%2B-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
[![Inter font](https://img.shields.io/badge/font-Inter_v4-black.svg)](https://rsms.me/inter/)

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

Install the app trough the WebUI or enable the app on the CLI

```bash
sudo -u www-data php /var/www/nextcloud/occ app:enable interfonts
```

That's it. Nextcloud is now in Inter.

> Tagged release tarballs are also available on the
> [Releases page](https://github.com/solracsf/nc-interfonts/releases).

## Uninstalling

Disable and remove the app trough the WebUI or remove the app on the CLI

```bash
sudo -u www-data php /var/www/nextcloud/occ app:remove interfonts
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
