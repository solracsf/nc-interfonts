<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\InterFonts\Listener;

use OCA\InterFonts\AppInfo\Application;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\App\IAppManager;
use OCP\IURLGenerator;
use OCP\Util;

/**
 * Injects the Inter font stylesheet into every Nextcloud page.
 *
 * The @font-face rules are generated at request-time by CSSController so that
 * font URLs are always correct regardless of sub-directory installs. We inject
 * the stylesheet via Util::addHeader() (a raw <link> in <head>) rather than
 * Util::addStyle(), because addStyle() is designed for static app CSS files
 * and constructs the URL as linkTo($app, 'css/'.$file.'.css') — it would mangle
 * a full controller route URL.
 *
 * @template-implements IEventListener<BeforeTemplateRenderedEvent>
 */
class BeforeTemplateRenderedListener implements IEventListener {

    public function __construct(
        private readonly IURLGenerator $urlGenerator,
		private readonly IAppManager $appManager,
    ) {}

    public function handle(Event $event): void {
        if (!$event instanceof BeforeTemplateRenderedEvent) {
            return;
        }

        // Generate the URL to CSSController::stylesheet().
        // linkToRoute() respects sub-directory installs and overwrite.cli.url,
        // so this works correctly whether Nextcloud lives at / or /nextcloud/.
        $cssUrl = $this->urlGenerator->linkToRoute(
            'interfonts.CSS.stylesheet'
        );
		
		// Use app version as cache-buster (Nextcloud-style)
		$version = $this->appManager->getAppVersion(Application::APP_ID);

        // Util::addStyle($app, $file) is designed for static files in an app's
        // css/ directory — it constructs the URL as linkTo($app, 'css/'.$file.'.css')
        // and will mangle a full route URL. Use addHeader() instead, which
        // injects a raw <link> element verbatim into <head>.
        Util::addHeader('link', [
            'rel'  => 'stylesheet',
            'href' => $cssUrl . '?v=' . $version,
            'type' => 'text/css',
        ]);
    }
}
