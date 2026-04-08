<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\InterFonts\Listener;

use OCA\InterFonts\AppInfo\Application;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\Events\BeforeLoginTemplateRenderedEvent;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IURLGenerator;
use OCP\Util;

/**
 * Injects the Inter font assets into every Nextcloud page.
 *
 * BeforeTemplateRenderedEvent fires from TemplateResponse::render(), so
 * this listener runs for *every* AppFramework-rendered page — logged in
 * pages, the login form, public share pages, password recovery, the
 * Impersonate and Guests apps, etc.
 *
 * Three tags are added to <head>:
 *
 *  1. <link rel="preload" as="font" crossorigin> for the roman WOFF2.
 *     Tells the browser to start downloading the font in parallel with
 *     the HTML parse, so the font is already in cache by the time the
 *     stylesheet (the next tag) tells the browser it is needed. This is
 *     the standard "eliminate FOUT" pattern.
 *
 *  2. The same preload for the italic WOFF2. Italic is small enough that
 *     preloading both costs almost nothing and avoids any italic FOUT
 *     when an <em> first paints.
 *
 *  3. <link rel="stylesheet"> pointing at CSSController::stylesheet().
 *     Util::addStyle() is intentionally NOT used because it routes through
 *     CSSResourceLocator, which tries to resolve its argument to a file on
 *     disk and silently drops anything that does not. Util::addHeader()
 *     injects the tag verbatim — exactly what we need for a controller URL.
 *
 * The `crossorigin` attribute on the preload is mandatory for fonts, even
 * for same-origin requests, because fonts are CORS-fetched by spec. The
 * FontController sends `Access-Control-Allow-Origin: *` so the preload
 * actually matches the eventual font request and is not wasted.
 *
 * @template-implements IEventListener<BeforeTemplateRenderedEvent>
 */
class BeforeTemplateRenderedListener implements IEventListener {

    public function __construct(
        private readonly IURLGenerator $urlGenerator,
        private readonly IAppManager $appManager,
    ) {}

    public function handle(Event $event): void {
        if (!$event instanceof BeforeTemplateRenderedEvent
            && !$event instanceof BeforeLoginTemplateRenderedEvent) {
            return;
        }

        // Resolve absolute, sub-directory-safe URLs through the router.
        // linkToRoute() honours overwrite.cli.url, so this works whether
        // Nextcloud is at https://cloud.example.com/ or
        // https://example.com/cloud/.
        //
        // The font binary URLs already embed the upstream Inter version in
        // the filename, so they are inherently cache-busted on font upgrade.
        // The stylesheet URL is stable, so we append the *app* version as a
        // query string — that way bumping the app (e.g. tweaking selectors
        // in CSSController) invalidates the cached stylesheet but not the
        // font binaries themselves.
        $appVersion    = $this->appManager->getAppVersion(Application::APP_ID);
        $stylesheetUrl = $this->urlGenerator->linkToRoute('interfonts.CSS.stylesheet')
            . '?v=' . rawurlencode($appVersion);
        $romanUrl      = $this->urlGenerator->linkToRoute(
            'interfonts.Font.serve',
            ['filename' => Application::romanFontFilename()],
        );
        $italicUrl     = $this->urlGenerator->linkToRoute(
            'interfonts.Font.serve',
            ['filename' => Application::italicFontFilename()],
        );

        // 1) Preload the roman variable WOFF2 — primary weight, hot path.
        Util::addHeader('link', [
            'rel'         => 'preload',
            'as'          => 'font',
            'type'        => 'font/woff2',
            'href'        => $romanUrl,
            'crossorigin' => 'anonymous',
        ]);

        // 2) Preload the italic variable WOFF2 — small but eliminates italic FOUT.
        Util::addHeader('link', [
            'rel'         => 'preload',
            'as'          => 'font',
            'type'        => 'font/woff2',
            'href'        => $italicUrl,
            'crossorigin' => 'anonymous',
        ]);

        // 3) The stylesheet itself, which actually applies the @font-face rules.
        Util::addHeader('link', [
            'rel'  => 'stylesheet',
            'href' => $stylesheetUrl,
            'type' => 'text/css',
        ]);
    }
}
