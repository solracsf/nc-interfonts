<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\InterFonts\AppInfo;

use OCA\InterFonts\Listener\BeforeTemplateRenderedListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;

/**
 * Main application class for the Inter Fonts app.
 *
 * Architecture overview
 * ---------------------
 * This app uses three components working together:
 *
 * 1. BeforeTemplateRenderedListener
 *    Fires on every page render and calls Util::addStyle() with the URL of
 *    the CSSController::stylesheet() route. Nextcloud injects that URL as a
 *    <link rel="stylesheet"> in the page <head>.
 *
 * 2. CSSController (GET /index.php/apps/interfonts/css)
 *    Returns a text/css response containing the @font-face declarations.
 *    Font file URLs inside @font-face are generated with linkToRoute() so
 *    they are always correct regardless of sub-directory installs.
 *
 * 3. FontController (GET /index.php/apps/interfonts/font/{filename})
 *    Streams the WOFF2 binary with correct MIME type and long-lived cache
 *    headers. Filename is validated against an explicit allowlist.
 *
 * Why a controller for CSS?
 * -------------------------
 * Nextcloud's asset pipeline serves files from css/ at a versioned/hashed
 * URL path (e.g. /css-cache/xxxxx.css). Relative paths like ../fonts/ inside
 * that file resolve against the cache URL, not the app directory, so they
 * always 404. Using IURLGenerator::linkToRoute() in a controller is the only
 * way to get a stable, sub-directory-safe URL for the font files.
 */
class Application extends App implements IBootstrap {

    public const APP_ID = 'interfonts';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(
            BeforeTemplateRenderedEvent::class,
            BeforeTemplateRenderedListener::class,
        );
    }

    public function boot(IBootContext $context): void {
        // Intentionally empty.
    }
}
