# Changelog

## 1.3.0 — 2026-04-04

### Minor release
- Version bump from 1.2.2 to 1.3.0.


## 1.2.2 — 2026-04-04

### Patch release
- Version bump from 1.2.1 to 1.2.2.


## 1.2.1 — 2026-04-04

### Fixed
- `@font-face` blocks are now emitted as an inline `<style>` tag by the
  listener with literal URL tokens, instead of residing in the static CSS
  file. The CSS spec forbids `var()` inside `url()` (CSSWG issue #794), so
  the previous `url(var(--inter-font-url, …))` pattern was silently ignored
  by every browser, preventing the font from ever loading.
- Removed broken relative path fallback `../fonts/Inter.var.woff2` from
  `inter-font.css` — Nextcloud serves CSS through a versioned asset pipeline
  path, making relative font paths resolve to the wrong location.
- `css/inter-font.css` now contains only font-family application rules;
  `@font-face` registration is fully handled in the inline block.

## 1.2.0 - 2026-04-04

### Changed
- Updated bundled Inter font to v4.1.
  Source: https://github.com/rsms/inter/releases/tag/v4.1


## 1.1.0 — 2026-04-04

### Changed
- Font paths now use `IURLGenerator::linkTo()` at render time, producing
  correct absolute URLs regardless of sub-directory installs or reverse-proxy
  configurations.
- CSS rules now reference `var(--font-face)` instead of repeating the font
  stack in every selector — cleaner and more maintainable.
- Listener now injects an inline `<style>` block via `Util::addHeader()` that
  sets `--inter-font-url`, `--inter-italic-font-url`, and `--font-face` before
  the external stylesheet is parsed, eliminating any flash of unstyled text.
- GitHub Actions workflow updated: on a new Inter upstream release, fonts are
  downloaded, app version is bumped (minor), files are committed directly to
  `main`, a Git tag is created, and a GitHub Release is published automatically.
  No pull request is opened.

## 1.0.0 — 2026-04-04

### Added
- Initial release.
- Self-hosted Inter variable font (wght 100–900, roman + italic axes).
- Global CSS injection via `BeforeTemplateRenderedEvent`.
- Overrides `--font-face` CSS variable for deep Nextcloud theme integration.
- Optical sizing and tabular numeric figures enabled where relevant.
- No external network requests; all assets served from within the app.
