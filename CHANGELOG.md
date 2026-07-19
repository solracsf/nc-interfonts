<!--
  - SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).









## [Unreleased]

## 2.1.3 - 2026-07-19

### Fixed
- Icon fonts render as icons again instead of as raw Private Use Area
  codepoints. `<i>` sat in the italic rule's selector list, so
  `font-family: <Inter stack> !important` applied at specificity (0,1,1) and
  outranked the (0,1,0) class selector that every icon font declares its
  family on — FontAwesome (`<i class="fa fa-user">` in v4,
  `<i class="fa-solid fa-user">` in v5/6), Material Icons
  (`<i class="material-icons">`), Ionicons and Glyphicons. Reported against
  [Nextcloud Passwords](https://github.com/marius-wieschollek/passwords),
  which renders every icon as `<i class="icon fa fa-{name}">`.
  `<i>` is now omitted from the rule outright rather than excluding one class:
  the user-agent stylesheet already declares `i { font-style: italic }` and
  the Inter stack still reaches `<i>` by inheritance, so italic prose is
  unchanged while *every* icon font is fixed — not just FontAwesome 4.
  The integration smoke test now fails if any rule targets `<i>` again. PR #15.

## 2.1.2 - 2026-05-11

### Fixed
- Code-like elements (`<pre>`, `<code>`, `<kbd>`, `<samp>`) and CodeMirror
  surfaces (`.cm-content`) now stay monospaced inside rich-text editors.
  v2.0.2 added `:root [contenteditable]` to apply Inter to Tiptap/ProseMirror
  body text; `font-family !important` then inherited into descendant `<code>`
  and `<pre>`, breaking code blocks in the Text app markdown editor and
  inline code in Notes/Talk/Collectives. Added a follow-up rule that
  re-asserts **Nextcloud core's monospace stack** (`'Lucida Console',
  'Lucida Sans Typewriter', 'DejaVu Sans Mono', monospace` — the exact stack
  hardcoded on `code` in `core/css/styles.scss`) on code-like elements, so
  rendering inside our editors matches the rest of the Nextcloud UI verbatim.
  Also resets `font-feature-settings` so Inter ligatures (`==`, `!=`, `=>`)
  cannot leak into code regions. Issue #9.

## 2.1.1 - 2026-04-27

### Changed
- Version bump from 2.1.0 to 2.1.1.

## 2.1.0 - 2026-04-27

### Changed
- Version bump from 2.0.4 to 2.1.0.

## 2.0.4 - 2026-04-09

### Changed
- Version bump from 2.0.3 to 2.0.4.

## 2.0.3 - 2026-04-09

### Changed
- Version bump from 2.0.2 to 2.0.3.

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
