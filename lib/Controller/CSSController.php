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
 * permitted there per the CSS spec. Font URLs must be absolute and
 * sub-directory-safe, which requires IURLGenerator::linkToRoute() at
 * request time. A static file cannot call PHP.
 *
 * Why font-family uses literal values and not var(--font-face)
 * ------------------------------------------------------------
 * Nextcloud's theming app generates a stylesheet (default.css, light.css,
 * etc.) that ALSO sets --font-face on :root with the system font stack.
 * Those theming sheets are injected into <head> AFTER this stylesheet.
 *
 * CSS custom properties with !important are only supported since
 * Chrome 125 / Firefox 122 / Safari 17.4 (all April 2024 or later).
 * On slightly older clients, !important on a custom property is silently
 * ignored, so the theming sheet's --font-face declaration (which has no
 * !important) wins simply because it is later in document order.
 *
 * Once --font-face resolves to the system font, any rule that says
 * `font-family: var(--font-face) !important` expands to the system font
 * with !important — not to Inter.
 *
 * The fix: declare Inter directly in every font-family property. The
 * literal string 'InterVariable', 'InterVariable Fallback', ... is
 * immune to --font-face being overridden by any other stylesheet.
 * We still write --font-face so that Nextcloud's own JS/CSS that reads
 * the variable gets a useful value, but we never depend on it ourselves.
 *
 * What this stylesheet contains
 * -----------------------------
 *  - Two @font-face rules for InterVariable (roman + italic), variable
 *    font covering weight 100–900 with font-display: swap.
 *  - Two @font-face rules for InterVariable Fallback — a synthetic local
 *    Arial with metric overrides calibrated against @next/font's reference
 *    values for Inter, so the fallback renders at the same line heights
 *    as Inter and the swap transition causes zero CLS.
 *  - font-family set to the literal Inter stack (not var(--font-face))
 *    on every meaningful Nextcloud surface.
 *  - font-feature-settings: 'liga' 1, 'calt' 1 — Inter contextual
 *    alternates; Chrome requires explicit opt-in.
 *  - font-variant-numeric: tabular-nums on numeric columns.
 *
 * Caching
 * -------
 * Cache-Control: public, max-age=604800, immutable (7 days). The
 * stylesheet URL is cache-busted by app version via ?v= in the listener,
 * and font binary URLs are cache-busted by Inter version in the filename.
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

        // Defensive: linkToRoute should never produce single-quotes, but
        // sanitise anyway before embedding into a CSS url() token.
        $romanUrl  = str_replace("'", '%27', $romanUrl);
        $italicUrl = str_replace("'", '%27', $italicUrl);

        // The full font stack: Inter variable font, metric-compat synthetic
        // fallback, then the native system stack so text is always readable
        // even before the WOFF2 has been parsed.
        //
        // IMPORTANT: this string is interpolated directly into every
        // font-family declaration below. Do NOT use var(--font-face) in
        // font-family — see class docblock for the reason.
        $stack = "'InterVariable','InterVariable Fallback',"
            . "-apple-system,BlinkMacSystemFont,"
            . '"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,'
            . '"Helvetica Neue",Arial,sans-serif,'
            . '"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"';

        $css = <<<CSS
/*
 * Inter Fonts for Nextcloud — generated stylesheet
 *
 * No external requests: WOFF2 binaries are bundled and served by this app.
 * No CLS:   metric-compatible synthetic Arial fallback matches Inter's
 *           vertical metrics until the real font has swapped in.
 * No FOUT:  BeforeTemplateRenderedListener injects <link rel="preload">
 *           for both WOFF2 files so download starts with the HTML parse.
 * No var():  font-family uses a literal stack — theming cannot override it
 *           by redefining --font-face after this sheet loads.
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

/* Metric-compatible fallback — calibrated against Vercel @next/font for Inter v4.
 * https://github.com/vercel/next.js/tree/canary/packages/font
 * Matches Inter's ascent/descent/size so the swap causes zero CLS. */

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

/* --font-face is kept for Nextcloud JS/CSS that reads the variable, but  */
/* we never use var(--font-face) in our own font-family declarations —    */
/* see class docblock for the reason.                                     */

:root {
    --font-face: {$stack};
}

/*
 * Selectors prefixed with :root raise specificity from (0,0,1) to
 * (0,1,1), so our !important rules win even when Nextcloud's guest
 * or theming CSS appears later in source order with the same property.
 */
:root,
:root body,
:root button,
:root input,
:root optgroup,
:root option,
:root select,
:root textarea,
:root [contenteditable] {
    font-family: {$stack} !important;
    font-feature-settings: 'liga' 1, 'calt' 1; /* contextual alternates, Chrome needs explicit opt-in */
}

/*
 * Cover every Nextcloud surface that is styled independently,
 * including the login page (.guest-box, #body-login, .body-login-container)
 * and the Impersonate / Guests apps which inherit those surfaces.
 */
:root #header,
:root .header-left,
:root .header-right,
:root .header-menu,
:root #app-navigation,
:root #app-navigation-vue,
:root #app-content,
:root #app-content-vue,
:root #app-sidebar,
:root #app-sidebar-vue,
:root .modal-container,
:root .modal-wrapper,
:root .popover,
:root .popover__inner,
:root .tooltip,
:root .toastify,
:root .body-login-container,
:root #body-login,
:root .guest-box,
:root .update,
:root .nc-chip,
:root .breadcrumb,
:root .breadcrumb__crumb,
:root .file-picker,
:root .filepicker,
:root .sharing-entry,
:root .action-button,
:root .action-input,
:root .action-link,
:root .action-text,
:root .empty-content,
:root .emptycontent,
:root table, :root th, :root td {
    font-family: {$stack} !important;
}

/* Pin bold weight to 700. The user agent sets font-weight: bolder on
 * <strong> and <b>, which is a relative keyword — it resolves to the next
 * bolder weight above the inherited value and can produce 800 or 900 with
 * a variable font. We always want exactly 700 (semibold). */
:root strong, :root b {
    font-weight: 700;
}

/* Inter ships true italics in the variable font */
:root em, :root i, :root cite, :root dfn, :root var, :root address, :root .italic {
    font-style: italic;
    font-family: {$stack} !important;
}

/* Headings: let the variable font handle optical sizing */
:root h1, :root h2, :root h3, :root h4, :root h5, :root h6 {
    font-optical-sizing: auto;
}

/* Tabular numerals for columns where digits must align vertically */
:root .files-list,
:root .files-list__row,
:root .files-filestable,
:root .files-filestable td,
:root .filesize,
:root time,
:root .modified,
:root .date,
:root .dashboard-widget__item__time {
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
