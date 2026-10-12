<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Minimal OCP stubs for unit-testing pure logic in lib/AppInfo/Application.php
 * without a real Nextcloud installation.
 *
 * Only the symbols that lib/ classes touch *at class-declaration time*
 * (parent classes, implemented interfaces) are stubbed here. Type hints
 * inside method signatures are not eagerly resolved by PHP, so we don't
 * need stubs for IRegistrationContext / IBootContext / event classes
 * for the tests in this suite to run.
 *
 * Loaded via composer.json `autoload-dev.files`, which means these
 * declarations only exist when dev autoload is active (i.e. running
 * `composer install` and then `phpunit`). They are NEVER shipped or
 * loaded inside a real Nextcloud — the release tarball ships only
 * `appinfo/`, `fonts/`, `img/`, `lib/`, and the legal/docs files.
 */

namespace OCP\AppFramework {

    if (!class_exists(App::class, false)) {
        class App {
            public function __construct(string $appId) {
            }
        }
    }
}

namespace OCP\AppFramework\Bootstrap {

    if (!interface_exists(IBootstrap::class, false)) {
        interface IBootstrap {
            public function register(IRegistrationContext $context): void;
            public function boot(IBootContext $context): void;
        }
    }

    if (!interface_exists(IRegistrationContext::class, false)) {
        interface IRegistrationContext {
        }
    }

    if (!interface_exists(IBootContext::class, false)) {
        interface IBootContext {
        }
    }
}

namespace OCP {

    if (!interface_exists(IRequest::class, false)) {
        interface IRequest {
        }
    }

    if (!interface_exists(IURLGenerator::class, false)) {
        interface IURLGenerator {
            public function linkToRoute(string $routeName, array $arguments = []): string;
        }
    }
}

namespace OCP\AppFramework {

    if (!class_exists(Controller::class, false)) {
        class Controller {
            public function __construct(string $appName, \OCP\IRequest $request) {
            }
        }
    }
}

namespace OCP\AppFramework {

    if (!class_exists(Http::class, false)) {
        class Http {
            public const STATUS_OK = 200;
        }
    }
}

namespace OCP\AppFramework\Http {

    if (!class_exists(DataDisplayResponse::class, false)) {
        class DataDisplayResponse {
            public function __construct(
                private string $data = '',
                private int $statusCode = 200,
                private array $headers = [],
            ) {
            }

            public function getData(): string {
                return $this->data;
            }
        }
    }
}
