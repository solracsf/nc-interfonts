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
 * This controller exists because @font-face src requires a literal URL token;
 * the CSS spec forbids var() inside url(). Since Nextcloud serves static CSS
 * files through a versioned asset pipeline at unpredictable paths, relative
 * paths from a static CSS file always resolve incorrectly. Generating the
 * @font-face block here with IURLGenerator::linkToRoute() gives us a stable,
 * sub-directory-safe absolute URL every time.
 *
 * The response is marked public and CSRF-free because it is a static
 * stylesheet loaded before any user session exists (login page, public
 * shares). It contains no user data.
 *
 * Cache-Control: max-age=604800 (7 days) is intentional — the URLs are
 * stable and the content only changes on app upgrade.
 *
 * NOTE: The route URL is /stylesheet, not /css.
 * The app ships a real css/ directory on disk. Apache and nginx resolve a
 * physical directory before the index.php rewrite rule fires, so
 * GET /apps/interfonts/css was served as a directory listing (403/404)
 * rather than reaching this controller. /stylesheet has no on-disk
 * counterpart and therefore always passes through to index.php.
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
     * Route: GET /index.php/apps/interfonts/stylesheet
     * Injected into every page via Util::addHeader() in the listener.
     */
    #[PublicPage]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function stylesheet(): DataDisplayResponse {
        $romanUrl = $this->urlGenerator->linkToRoute(
            'interfonts.Font.serve',
            ['filename' => 'Inter.var.woff2']
        );
        $italicUrl = $this->urlGenerator->linkToRoute(
            'interfonts.Font.serve',
            ['filename' => 'InterItalic.var.woff2']
        );

        $stack = '"Inter",-apple-system,BlinkMacSystemFont,'
            . '"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,'
            . '"Helvetica Neue",Arial,sans-serif,'
            . '"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"';

        // Encode any single-quotes in the URL (defensive; linkToRoute
        // should never produce them, but belt-and-suspenders).
        $romanUrl  = str_replace("'", '%27', $romanUrl);
        $italicUrl = str_replace("'", '%27', $italicUrl);

        $css = <<<CSS
/* Inter Fonts for Nextcloud — generated stylesheet */
/* Font files are served from FontController, no external requests are made. */

@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('{$romanUrl}') format('woff2');
}

@font-face {
    font-family: "Inter";
    font-style: italic;
    font-weight: 100 900;
    font-display: swap;
    src: url('{$italicUrl}') format('woff2');
}

:root {
    --font-face: {$stack} !important;
}

html,
body {
    font-family: var(--font-face) !important;
}

input,
button,
select,
textarea,
optgroup,
option {
    font-family: var(--font-face) !important;
}

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
#header,
.header-left,
.header-right,
.header-menu,
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

h1, h2, h3, h4, h5, h6 {
    font-optical-sizing: auto;
    font-variation-settings: "opsz" 32;
}

.files-list,
.files-list__row,
.files-filestable,
.files-filestable td,
.filesize,
time,
.modified,
.date {
    font-variant-numeric: tabular-nums;
}
CSS;

        $response = new DataDisplayResponse($css, Http::STATUS_OK, [
            'Content-Type'  => 'text/css; charset=utf-8',
            'Cache-Control' => 'public, max-age=604800, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        return $response;
    }
}
