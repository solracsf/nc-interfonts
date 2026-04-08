<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\InterFonts\Controller;

use OCA\InterFonts\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\IRequest;
use OCP\IURLGenerator;

/**
 * Serves the Inter font @font-face CSS with runtime-resolved font URLs.
 *
 * Why a controller and not a static .css file?
 * --------------------------------------------
 * @font-face's `src: url(...)` token must be a literal — `var()` is not
 * permitted there per the CSS spec. Static CSS files in a Nextcloud app's
 * css/ directory are served through a hashed cache pipeline, so any
 * relative path inside them resolves against the cache URL rather than
 * the app directory. The only way to get a stable, sub-directory-safe URL
 * for the bundled WOFF2 binaries is to generate the @font-face block at
 * request-time with IURLGenerator::linkToRoute().
 *
 * What this stylesheet contains
 * -----------------------------
 *  - Two `@font-face` rules for `InterVariable` (roman + italic), with
 *    `font-weight: 100 900` so a single variable font covers every weight.
 *  - One `@font-face` rule for `InterVariable Fallback`, a synthetic local
 *    Arial face with `ascent-override`, `descent-override`,
 *    `line-gap-override` and `size-adjust` chosen so the fallback renders
 *    at the same metrics as Inter — eliminating layout shift (CLS) when
 *    the real font swaps in.
 *  - A `font-feature-settings` declaration enabling Inter's contextual
 *    alternates (`calt`) and standard ligatures (`liga`).
 *  - Overrides that re-target Nextcloud's `--background-text` font
 *    variable, plus a wide selector list covering every interactive
 *    surface (modals, popovers, file lists, login boxes, etc.).
 *  - `font-variant-numeric: tabular-nums` on numeric columns (file sizes,
 *    timestamps) so digits stay vertically aligned.
 *
 * Public, CSRF-free
 * -----------------
 * Marked #[PublicPage] and #[NoCSRFRequired] because this stylesheet is
 * loaded before any user session exists (login page, public share pages,
 * Impersonate sessions). It contains no user data.
 *
 * Caching
 * -------
 * The CSS is sent with `Cache-Control: public, max-age=604800, immutable`.
 * Font filenames embed the upstream Inter version, so the URLs inside the
 * CSS automatically change on a font upgrade — no manual cache-busting
 * is needed.
 */
class CSSController extends Controller {

    public function __construct(
        IRequest $request,
        private readonly IURLGenerator $urlGenerator,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    /**
     * Returns the @font-face CSS block for Inter.
     *
     * Route: GET /apps/interfonts/stylesheet
     * Injected into every page via Util::addHeader() in the listener.
     */
    #[PublicPage]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function stylesheet(): DataDisplayResponse {
        $romanUrl = $this->urlGenerator->linkToRoute(
            'interfonts.Font.serve',
            ['filename' => Application::romanFontFilename()]
        );
        $italicUrl = $this->urlGenerator->linkToRoute(
            'interfonts.Font.serve',
            ['filename' => Application::italicFontFilename()]
        );

        // Defensive: encode any single-quotes that might somehow appear in
        // the URLs (linkToRoute should never produce them).
        $romanUrl  = str_replace("'", '%27', $romanUrl);
        $italicUrl = str_replace("'", '%27', $italicUrl);

        // System font stack used as the cascade fallback after Inter and
        // its metric-compatible synthetic. Mirrors Nextcloud's own default
        // so anything that escapes our overrides still looks native.
        $systemStack = '-apple-system,BlinkMacSystemFont,'
            . '"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,'
            . '"Helvetica Neue",Arial,sans-serif,'
            . '"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"';

        // Metric-compatible fallback overrides for Inter v4.
        // These values are calibrated so a local Arial renders at the same
        // ascent/descent/line-gap as Inter, eliminating CLS during the
        // font-display: swap transition. Source: Vercel's @next/font
        // automatic-fallback computation for Inter.
        // https://github.com/vercel/next.js/tree/canary/packages/font

        $css = <<<CSS
/*
 * Inter Fonts for Nextcloud — generated stylesheet
 *
 * No external requests: WOFF2 binaries are bundled and served by this app.
 * No CLS: a metric-compatible synthetic Arial fallback matches Inter's
 *         vertical metrics until the real variable font has swapped in.
 * No FOUT: BeforeTemplateRenderedListener also injects <link rel="preload">
 *          for both WOFF2 files in <head>, so download starts in parallel
 *          with the HTML parse.
 */

@font-face {
    font-family: 'InterVariable';
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('{$romanUrl}') format('woff2-variations'),
         url('{$romanUrl}') format('woff2');
}

@font-face {
    font-family: 'InterVariable';
    font-style: italic;
    font-weight: 100 900;
    font-display: swap;
    src: url('{$italicUrl}') format('woff2-variations'),
         url('{$italicUrl}') format('woff2');
}

@font-face {
    font-family: 'InterVariable Fallback';
    font-style: normal;
    src: local('Arial');
    ascent-override: 90.20%;
    descent-override: 22.48%;
    line-gap-override: 0.00%;
    size-adjust: 107.40%;
}

@font-face {
    font-family: 'InterVariable Fallback';
    font-style: italic;
    src: local('Arial Italic'), local('Arial');
    ascent-override: 90.20%;
    descent-override: 22.48%;
    line-gap-override: 0.00%;
    size-adjust: 107.40%;
}

:root {
    --font-face: 'InterVariable', 'InterVariable Fallback', {$systemStack} !important;
    --background-text: var(--font-face);
}

html,
body,
button,
input,
optgroup,
option,
select,
textarea {
    font-family: var(--font-face) !important;
    font-feature-settings: 'liga' 1, 'calt' 1; /* fix for Chrome */
}

@supports (font-variation-settings: normal) {
    html,
    body,
    button,
    input,
    optgroup,
    option,
    select,
    textarea {
        font-family: var(--font-face) !important;
    }
}

/*
 * Cover every interactive surface that Nextcloud styles separately,
 * including login/guest layouts (.guest-box, #body-login) and the
 * Impersonate / Guests apps which inherit those surfaces.
 */
#header,
.header-left,
.header-right,
.header-menu,
#app-navigation,
#app-navigation-vue,
#app-content,
#app-content-vue,
#app-sidebar,
#app-sidebar-vue,
.modal-container,
.modal-wrapper,
.popover,
.popover__inner,
.tooltip,
.toastify,
.body-login-container,
#body-login,
.guest-box,
.update,
.nc-chip,
.breadcrumb,
.breadcrumb__crumb,
.file-picker,
.filepicker,
.sharing-entry,
.action-button,
.action-input,
.action-link,
.action-text,
.empty-content,
.emptycontent,
table, th, td {
    font-family: var(--font-face) !important;
}

/* Inter has true italics in the variable font — let the browser use them */
em, i, cite, dfn, var, address, .italic {
    font-style: italic;
    font-family: var(--font-face) !important;
}

/* Headings — let the variable font do its own optical sizing */
h1, h2, h3, h4, h5, h6 {
    font-optical-sizing: auto;
}

/*
 * Tabular numerals for any column where digits should align vertically:
 * file sizes, modification times, quotas, dashboards.
 */
.files-list,
.files-list__row,
.files-filestable,
.files-filestable td,
.filesize,
time,
.modified,
.date,
.dashboard-widget__item__time {
    font-variant-numeric: tabular-nums;
}
CSS;

        return new DataDisplayResponse($css, Http::STATUS_OK, [
            'Content-Type'  => 'text/css; charset=utf-8',
            'Cache-Control' => 'public, max-age=604800, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
