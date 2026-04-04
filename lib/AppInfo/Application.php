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
 * Registers a page-render event listener so that the Inter font stylesheet
 * is injected into every Nextcloud HTML response — login page, user pages,
 * admin pages, and public shares alike.
 */
class Application extends App implements IBootstrap {

    public const APP_ID = 'interfonts';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    /**
     * Register event listeners during the registration phase.
     * No container resolution is permitted here.
     */
    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(
            BeforeTemplateRenderedEvent::class,
            BeforeTemplateRenderedListener::class,
        );
    }

    /**
     * Execute boot-time logic after the container is fully assembled.
     * No actions are required at boot time for this app.
     */
    public function boot(IBootContext $context): void {
        // Intentionally empty — all behaviour is driven by the event listener.
    }
}
