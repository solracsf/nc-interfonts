# Changelog

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
