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
 * The CSS is intentionally generated here instead of being served as a static
 * file, because the font URLs must be resolved by Nextcloud's router so they
 * remain correct across subdirectory installs and reverse proxies.
 */
final class CSSController extends Controller {

    private const CACHE_TTL = 31536000;

    public function __construct(
        IRequest $request,
        private readonly IURLGenerator $urlGenerator,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    /**
     * Returns the generated stylesheet.
     *
     * Route: GET /index.php/apps/interfonts/stylesheet
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

        $css = $this->buildCss($romanUrl, $italicUrl);

        return new DataDisplayResponse($css, Http::STATUS_OK, [
            'Content-Type' => 'text/css; charset=utf-8',
            'Cache-Control' => 'public, max-age=' . self::CACHE_TTL . ', immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function buildCss(string $romanUrl, string $italicUrl): string {
        $romanCssUrl = $this->cssString($romanUrl);
        $italicCssUrl = $this->cssString($italicUrl);

        return <<<CSS
/* Inter Fonts for Nextcloud - generated stylesheet */
/* Font files are served from FontController. */

@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src:
        local("Inter Variable"),
        local("Inter"),
        url({$romanCssUrl}) format("woff2");
}

@font-face {
    font-family: "Inter";
    font-style: italic;
    font-weight: 100 900;
    font-display: swap;
    src:
        local("Inter Italic"),
        local("Inter"),
        url({$italicCssUrl}) format("woff2");
}

:root {
    --font-face: "Inter",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
    --font-mono: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
    font-feature-settings: "cv11", "ss01";
}

html {
    font-family: var(--font-face);
}

body,
button,
input,
select,
textarea,
optgroup,
option {
    font-family: inherit;
}

#app-navigation,
#app-navigation-vue,
#app-content,
#app-content-vue,
#app-sidebar,
#app-sidebar-vue,
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
.modal-container,
.modal-wrapper,
.popover,
.popover__inner,
.tooltip,
.toastify,
table,
th,
td {
    font-family: var(--font-face);
}

code,
pre,
kbd,
samp,
.CodeMirror,
.monaco-editor {
    font-family: var(--font-mono);
}

h1,
h2,
h3,
h4,
h5,
h6 {
    font-optical-sizing: auto;
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
    }

    private function cssString(string $value): string {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }
}
