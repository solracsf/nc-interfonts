# Inter Fonts — Nextcloud App

Replaces every Nextcloud interface font with **Inter**, a screen-optimised
variable typeface by Rasmus Andersson.

## Features

| Feature | Details |
|---|---|
| Self-hosted | Font files live inside the app — no Google Fonts, no CDN |
| Variable font | One WOFF2 file per axis covers weights 100–900 |
| Full coverage | Injected on every page via `BeforeTemplateRenderedEvent` |
| Deep integration | Overrides Nextcloud's `--font-face` CSS variable |

## Requirements

| Dependency | Version |
|---|---|
| PHP | >= 8.2 |
| Nextcloud | 27 – 32 |

## Font files

WOFF2 files are excluded from the repo (binary files). Download them once:

```bash
curl -L "https://unpkg.com/@fontsource-variable/inter@5.1.1/files/inter-latin-wght-normal.woff2" \
     -o fonts/Inter.var.woff2

curl -L "https://unpkg.com/@fontsource-variable/inter@5.1.1/files/inter-latin-wght-italic.woff2" \
     -o fonts/InterItalic.var.woff2
```

## Installation

```bash
# Copy to Nextcloud apps directory
cp -r interfonts/ /var/www/nextcloud/apps/

# Set correct ownership
chown -R www-data:www-data /var/www/nextcloud/apps/interfonts/

# Enable via OCC
sudo -u www-data php /var/www/nextcloud/occ app:enable interfonts
```

## How it works

1. `Application.php` registers `BeforeTemplateRenderedListener` on boot.
2. The listener fires before **every** HTML page (user, login, admin, public shares).
3. `Util::addStyle()` queues `css/inter-font.css` into the page `<head>`.
4. CSS declares Inter via `@font-face` (local `../fonts/*.woff2`) and overrides `--font-face`.

No external network request is ever made at runtime.

## Licenses

- **App**: AGPL-3.0-or-later
- **Inter font**: SIL Open Font License 1.1
