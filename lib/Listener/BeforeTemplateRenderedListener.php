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
use OCP\Util;

/**
 * Injects the Inter font stylesheet before every Nextcloud page is rendered.
 *
 * BeforeTemplateRenderedEvent fires regardless of render-as context,
 * covering user pages, login, admin, and public share pages.
 *
 * @template-implements IEventListener<BeforeTemplateRenderedEvent>
 */
class BeforeTemplateRenderedListener implements IEventListener {

    /**
     * Queue the Inter font CSS file via Util::addStyle().
     *
     * Nextcloud will insert the stylesheet into the page <head>.
     * The CSS references font files at fonts/ via a relative path;
     * no external network request is ever made.
     */
    public function handle(Event $event): void {
        if (!$event instanceof BeforeTemplateRenderedEvent) {
            return;
        }

        Util::addStyle(Application::APP_ID, 'inter-font');
    }
}
