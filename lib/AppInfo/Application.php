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
use OCP\AppFramework\Http\Events\BeforeLoginTemplateRenderedEvent;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;

/**
 * Main application class for the Inter Fonts app.
 *
 * What this app does
 * ------------------
 * Replaces every Nextcloud interface font with Inter — bundled inside the
 * app, served by an internal route, with zero external requests.
 *
 * Architecture (intentionally minimal — three files)
 * --------------------------------------------------
 *  1. Listener\BeforeTemplateRenderedListener
 *     Registered for two events so it covers every page:
 *
 *       - BeforeTemplateRenderedEvent      — authenticated pages (files,
 *         settings, apps, …)
 *       - BeforeLoginTemplateRenderedEvent — the login form, password
 *         recovery, public share pages, and other unauthenticated pages.
 *         Nextcloud fires a separate event for these; registering only for
 *         BeforeTemplateRenderedEvent leaves the login page unstyled.
 *
 *     The listener injects three raw <link> elements into <head> via
 *     Util::addHeader() (not Util::addStyle(), which routes through
 *     CSSResourceLocator, requires a real file on disk, and silently drops
 *     controller URLs):
 *
 *       a) <link rel="preload" as="font" crossorigin> for the roman WOFF2,
 *          so the browser fetches it in parallel with the HTML parse.
 *       b) <link rel="preload" as="font" crossorigin> for the italic WOFF2.
 *       c) <link rel="stylesheet"> pointing at Controller\CSSController,
 *          which generates the @font-face block at request time.
 *
 *  2. Controller\CSSController (GET /apps/interfonts/stylesheet)
 *     Returns text/css containing two @font-face rules (roman + italic),
 *     two metric-compatible fallback @font-face rules for zero CLS, and
 *     font-family overrides for every Nextcloud surface.
 *     Font URLs are generated with IURLGenerator::linkToRoute() so they
 *     are correct in sub-directory installs and overwrite.cli.url setups.
 *
 *  3. Controller\FontController (GET /apps/interfonts/font/{filename})
 *     Streams a WOFF2 binary from fonts/ with long-lived immutable cache
 *     headers. Filenames embed the upstream Inter version (e.g.
 *     InterVariable-4.1.woff2), so a font upgrade automatically becomes a
 *     new URL — no stale browser caches across upgrades.
 *
 * Why a controller for CSS instead of a static file?
 * --------------------------------------------------
 * @font-face's src: url(...) token must be a literal — var() is not
 * permitted there per the CSS spec. Font URLs must be absolute and
 * sub-directory-safe, which requires IURLGenerator::linkToRoute() at
 * request time. A static file cannot call PHP.
 *
 * Why /stylesheet instead of /css?
 * --------------------------------
 * Apache/Nginx rewrite rules check is_dir() before forwarding to
 * index.php. If the route URL collided with an on-disk app directory
 * (e.g. /css), the web server would try to serve the directory directly
 * and return 404 before Nextcloud's router even saw the request.
 */
class Application extends App implements IBootstrap {

    public const APP_ID = 'interfonts';

    /** @var string|null Cached upstream Inter version (without 'v' prefix) */
    private static ?string $interVersion = null;

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {
        // Authenticated pages (files, settings, etc.)
        $context->registerEventListener(
            BeforeTemplateRenderedEvent::class,
            BeforeTemplateRenderedListener::class,
        );
        // Login page and other unauthenticated pages fire a separate event
        $context->registerEventListener(
            BeforeLoginTemplateRenderedEvent::class,
            BeforeTemplateRenderedListener::class,
        );
    }

    public function boot(IBootContext $context): void {
        // Intentionally empty.
    }

    /**
     * Upstream Inter release version, with the leading 'v' stripped.
     * Read once from fonts/inter-version.txt and memoised.
     */
    public static function interVersion(): string {
        if (self::$interVersion === null) {
            $file = __DIR__ . '/../../fonts/inter-version.txt';
            if (is_readable($file)) {
                $raw = file_get_contents($file);
                self::$interVersion = $raw === false
                    ? 'unknown'
                    : ltrim(trim($raw), 'vV');
            } else {
                self::$interVersion = 'unknown';
            }
        }
        return self::$interVersion;
    }

    /** Filename of the roman variable WOFF2 (e.g. InterVariable-4.1.woff2) */
    public static function romanFontFilename(): string {
        return 'InterVariable-' . self::interVersion() . '.woff2';
    }

    /** Filename of the italic variable WOFF2 (e.g. InterVariable-Italic-4.1.woff2) */
    public static function italicFontFilename(): string {
        return 'InterVariable-Italic-' . self::interVersion() . '.woff2';
    }

    /** Allowlist of font filenames the FontController will serve. */
    public static function allowedFontFilenames(): array {
        return [
            self::romanFontFilename(),
            self::italicFontFilename(),
        ];
    }
}
