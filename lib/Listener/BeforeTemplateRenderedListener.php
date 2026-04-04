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
 * Injects the Inter font stylesheet and a runtime-generated inline <style>
 * block before every Nextcloud page is rendered.
 *
 * Why two mechanisms?
 * -------------------------------------------------------------------
 * The @font-face `src` descriptor requires a *literal* URL token — the CSS
 * spec explicitly forbids var() inside url() (CSSWG issue #794). This means
 * the font file paths cannot be placed in a static CSS file when Nextcloud
 * is installed in a sub-directory, because the path is only known at
 * request time via IURLGenerator::linkTo().
 *
 * The solution is to emit the *entire* @font-face block as an inline <style>
 * tag with the resolved absolute URLs embedded as plain string literals.
 * The external inter-font.css then only handles font-family application
 * (html, body, inputs, Nextcloud selectors) and relies on the "Inter" family
 * name declared in the inline block above it.
 *
 * Execution order:
 *   1. Util::addHeader() → inline <style id="inter-fonts-core"> is placed
 *      in <head> with the @font-face + :root --font-face override.
 *   2. Util::addStyle()  → inter-font.css is appended after it and applies
 *      font-family: var(--font-face) to every Nextcloud element.
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
     * Resolves the absolute font file URLs and injects the critical inline
     * CSS block, then queues the application stylesheet.
     *
     * @param Event $event The dispatched event.
     */
    public function handle(Event $event): void {
        if (!$event instanceof BeforeTemplateRenderedEvent) {
            return;
        }

        // Resolve absolute paths to the embedded WOFF2 files.
        // linkTo() respects sub-directory installs and overwrite.cli.url.
        $romanUrl  = $this->urlGenerator->linkTo(
            Application::APP_ID,
            'fonts/Inter.var.woff2'
        );
        $italicUrl = $this->urlGenerator->linkTo(
            Application::APP_ID,
            'fonts/InterItalic.var.woff2'
        );

        // Sanitise URLs for safe embedding in a CSS string literal.
        // linkTo() returns internal paths — no untrusted input — but we
        // escape single quotes defensively.
        $romanUrlSafe  = str_replace("'", '%27', $romanUrl);
        $italicUrlSafe = str_replace("'", '%27', $italicUrl);

        $fallbackStack = '"Inter",-apple-system,BlinkMacSystemFont,'
            . '"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,'
            . '"Helvetica Neue",Arial,sans-serif,'
            . '"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"';

        // Build the inline block:
        //   - Two @font-face rules with literal URL tokens (var() is forbidden
        //     inside url() per the CSS spec — this is the root cause of the
        //     font not loading when using a static CSS file alone).
        //   - :root override for --font-face so Nextcloud's own stylesheets
        //     (which consume that variable) also switch to Inter.
        $inlineCss = sprintf(
            '@font-face{'
                . 'font-family:"Inter";'
                . 'font-style:normal;'
                . 'font-weight:100 900;'
                . 'font-display:swap;'
                . "src:url('%s') format('woff2')"
            . '}'
            . '@font-face{'
                . 'font-family:"Inter";'
                . 'font-style:italic;'
                . 'font-weight:100 900;'
                . 'font-display:swap;'
                . "src:url('%s') format('woff2')"
            . '}'
            . ':root{'
                . '--inter-font-url:\'%s\';'
                . '--inter-italic-font-url:\'%s\';'
                . '--font-face:%s!important'
            . '}',
            $romanUrlSafe,
            $italicUrlSafe,
            $romanUrlSafe,
            $italicUrlSafe,
            $fallbackStack
        );

        // Inject the inline block first so @font-face is registered before
        // the external stylesheet requests the font files.
        Util::addHeader('style', ['id' => 'inter-fonts-core'], $inlineCss);

        // Queue the application stylesheet (font-family application rules).
        Util::addStyle(Application::APP_ID, 'inter-font');
    }
}
