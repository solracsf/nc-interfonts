# Inter Fonts — Nextcloud App

Replaces every Nextcloud interface font with **Inter**, a screen-optimised
variable typeface [by Rasmus Andersson](https://rsms.me/inter/). All font files are embedded directly
inside the app — no CDN, no Google Fonts, no external requests of any kind.

<img alt="Nextcloud 32 screenshot using Inter font" src="https://github.com/user-attachments/assets/7cad445e-e011-4f4a-b211-e3d9fb2315f4" />

<img alt="Nextcloud 32 screenshot using Inter font" src="https://github.com/user-attachments/assets/8c1c9d84-d553-4a00-8e6b-dd3d26b2b94a" />

<img alt="Nextcloud 32 screenshot using Inter font" src="https://github.com/user-attachments/assets/f703ed8a-9cd1-488b-8418-5f6bfc3e9c93" />

## Features

| Feature | Details |
|---|---|
| Fully self-hosted | Font files live at `fonts/` inside the app |
| Variable font | One WOFF2 per axis covers weights 100–900 |
| Full UI coverage | Injected on every page via `BeforeTemplateRenderedEvent` |
| Sub-directory safe | Font URLs generated at runtime via `IURLGenerator` |
| Deep integration | Overrides Nextcloud's `--font-face` CSS variable |

## Requirements

| Dependency | Version |
|---|---|
| PHP | &ge; 8.2 |
| Nextcloud | 27 – 32 |

## Installation

```bash
# 1. Clone this repo
sudo -u www-data \
  git clone https://github.com/solracsf/nc-interfonts /var/www/nextcloud/apps/interfonts

# 2. Enable the app via OCC
sudo -u www-data php /var/www/nextcloud/occ app:enable interfonts
```

> **Note:** Download links for every release are also listed on the
> [Releases page](https://github.com/solracsf/nc-interfonts/releases).

## Upgrading

```bash
# 1. Disable the running app
sudo -u www-data php /var/www/nextcloud/occ app:disable interfonts

# 2. Remove the old version
rm -rf /var/www/nextcloud/apps/interfonts

# 3. Download and install the new release (same steps as Installation above)

# 4. Re-enable
sudo -u www-data php /var/www/nextcloud/occ app:enable interfonts
```

## Font files

The two Inter variable WOFF2 files are bundled in every release under `fonts/`:

| File | Description |
|---|---|
| `fonts/Inter.var.woff2` | Roman variable font (wght 100–900) |
| `fonts/InterItalic.var.woff2` | Italic variable font (wght 100–900) |
| `fonts/inter-version.txt` | Upstream Inter version currently bundled |

## Licenses

- **App**: [AGPL-3.0-or-later](https://www.gnu.org/licenses/agpl-3.0.html)
- **Inter font**: [SIL Open Font License 1.1](https://scripts.sil.org/OFL)
