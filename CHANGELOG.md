# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).



## [Unreleased]

## 2.0.2 - 2026-04-09

### Fixed
- Rich-text editors (Tiptap/ProseMirror, `contenteditable` divs) now use
  Inter. Nextcloud's `inputs.scss` targets `div[contenteditable]` directly
  with `var(--font-face)`; added `:root [contenteditable]` to our selector
  list so the direct rule is matched and overridden.
- `<strong>` and `<b>` now render at exactly weight 700. The user agent
  stylesheet sets `font-weight: bolder` (a relative keyword) which resolves
  to 800 or 900 on a variable font; pinned to 700 with `:root strong, :root b`.
### Changed
- CHANGELOG update logic in both release workflows rewritten: promotes
  `## [Unreleased]` content into the new versioned entry and resets the
  block to empty; fixes header duplication bug from previous `sed`/`awk`
  approach.

## 2.0.1 - 2026-04-08

### Changed
- Version bump from 2.0.0 to 2.0.1.

## 2.0.0 - 2026-04-08

### Changed
- Version bump from 1.7.0 to 2.0.0.

## 1.7.0 - 2026-04-08

### Fixed
- Login page and other unauthenticated pages (public shares, password
  recovery) now use Inter. NC32+ fires `BeforeLoginTemplateRenderedEvent`
  for unauthenticated pages; the listener is now registered for both
  `BeforeTemplateRenderedEvent` (authenticated) and
  `BeforeLoginTemplateRenderedEvent` (unauthenticated).
- Font-family declarations now use a literal Inter font stack instead of
  `var(--font-face)`. Nextcloud's theming stylesheet redefines `--font-face`
  after our sheet loads, silently reverting to the system font on older
  browsers where `!important` on custom properties is ignored.
- All selectors are prefixed with `:root` to raise specificity and prevent
  equal-specificity theming overrides regardless of source order.

### Changed
- Extended Nextcloud compatibility range to v32–35.

## 1.5.0 - 2026-04-08

### Added
- Self-hosted Inter variable font v4.1 (roman + italic, weight 100–900).
- `<link rel="preload">` for both WOFF2 files injected via
  `BeforeTemplateRenderedListener` — eliminates FOUT.
- Metric-compatible synthetic fallback `@font-face` (ascent/descent/size
  calibrated against Vercel @next/font) — eliminates CLS.
- Font served through `FontController` with versioned filename
  (`InterVariable-4.1.woff2`) and `Cache-Control: immutable`.
- CSS generated at request time by `CSSController` so font URLs are
  absolute and sub-directory-safe via `IURLGenerator::linkToRoute()`.
- `font-feature-settings: 'liga' 1, 'calt' 1` — contextual alternates
  (requires explicit opt-in in Chrome).
- `font-variant-numeric: tabular-nums` on file-size and timestamp columns.

## 1.0.0 - 2026-04-04

### Added
- Initial release.
- Self-hosted Inter variable font (weight 100–900, roman + italic).
- Global font injection via `BeforeTemplateRenderedEvent`.
- No external network requests; all assets served from within the app.
