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
use OCP\IURLGenerator;
use OCP\Util;

/**
 * Injects the Inter font stylesheet URL into every Nextcloud page.
 *
 * Rather than pointing Util::addStyle() at a static file in css/ (which is
 * served through Nextcloud's asset pipeline at a hashed path, breaking
 * relative font URLs), we point it at the CSSController::stylesheet() route.
 *
 * Nextcloud's Util::addStyle() accepts either:
 *   (string $app, string $file)  — static file in css/<file>.css
 *   (string $app, string $url)   — already an absolute or root-relative URL
 *
 * We use the second form by generating the full route URL and passing it
 * as the $file parameter with an empty $app, which tells Nextcloud to inject
 * it as-is into the page <head> as:
 *   <link rel="stylesheet" href="/index.php/apps/interfonts/css">
 *
 * @template-implements IEventListener<BeforeTemplateRenderedEvent>
 */
class BeforeTemplateRenderedListener implements IEventListener {

    public function __construct(
        private readonly IURLGenerator $urlGenerator,
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

        // Inject as a <link rel="stylesheet"> in every page <head>.
        // Passing '' as $app and the full URL as $file causes Nextcloud to
        // write the URL verbatim into the link tag href.
        Util::addStyle('', $cssUrl);
    }
}
