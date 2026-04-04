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
 * Injects the Inter font stylesheet and a small inline <style> block before
 * every Nextcloud page is rendered.
 *
 * Two injection mechanisms are used together:
 *
 *  1. Util::addStyle() queues css/inter-font.css into the page <head>.
 *     Nextcloud handles versioning, deduplication, and caching for this file.
 *     The @font-face rules inside that CSS reference the font files via
 *     absolute URLs generated at runtime by IURLGenerator so that the paths
 *     are always correct regardless of Nextcloud's sub-directory install path
 *     or URL rewriting configuration.
 *
 *  2. An inline <style> block is prepended to the page via
 *     Util::addHeader() to override the --font-face CSS custom property
 *     immediately (before the external stylesheet is parsed), eliminating
 *     any flash of unstyled text during the stylesheet download.
 *
 * @template-implements IEventListener<BeforeTemplateRenderedEvent>
 */
class BeforeTemplateRenderedListener implements IEventListener {

    public function __construct(
        private readonly IURLGenerator $urlGenerator,
    ) {}

    /**
     * Handle the BeforeTemplateRenderedEvent.
     *
     * @param Event $event The dispatched event.
     */
    public function handle(Event $event): void {
        if (!$event instanceof BeforeTemplateRenderedEvent) {
            return;
        }

        // --- 1. Queue the main stylesheet -----------------------------------
        Util::addStyle(Application::APP_ID, 'inter-font');

        // --- 2. Emit absolute font URLs as CSS variables in an inline block -
        //
        // IURLGenerator::linkTo() returns the correct absolute path to a
        // static file inside the app, respecting sub-directory installs,
        // HTTPS, and any configured overwrite.cli.url.
        //
        // These CSS custom properties are consumed by the @font-face rules
        // in inter-font.css via the var() fallback chain, and also let
        // third-party themes reference the font files by URL if needed.
        $romanUrl  = $this->urlGenerator->linkTo(
            Application::APP_ID,
            'fonts/Inter.var.woff2'
        );
        $italicUrl = $this->urlGenerator->linkTo(
            Application::APP_ID,
            'fonts/InterItalic.var.woff2'
        );

        $inlineCss = sprintf(
            ':root{'
            . '--inter-font-url:\'%s\';'
            . '--inter-italic-font-url:\'%s\';'
            . '--font-face:"Inter",-apple-system,BlinkMacSystemFont,'
            . '"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,'
            . '"Helvetica Neue",Arial,sans-serif,'
            . '"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"!important'
            . '}',
            addslashes($romanUrl),
            addslashes($italicUrl)
        );

        Util::addHeader('style', ['id' => 'inter-fonts-vars'], $inlineCss);
    }
}
