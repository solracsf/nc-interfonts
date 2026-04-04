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
 * Registers a BeforeTemplateRenderedEvent listener that injects the Inter
 * font stylesheet and the runtime-generated absolute font-file URLs into
 * every Nextcloud HTML response.
 *
 * BeforeTemplateRenderedListener depends on IURLGenerator which is resolved
 * automatically by the Nextcloud DI container.
 */
class Application extends App implements IBootstrap {

    public const APP_ID = 'interfonts';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    /**
     * Register services and event listeners.
     * Container resolution is NOT available in this method.
     */
    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(
            BeforeTemplateRenderedEvent::class,
            BeforeTemplateRenderedListener::class,
        );
    }

    /**
     * Boot-time logic after the container is fully assembled.
     * No additional actions are required for this app.
     */
    public function boot(IBootContext $context): void {
        // Intentionally empty — all behaviour is driven by the event listener.
    }
}
