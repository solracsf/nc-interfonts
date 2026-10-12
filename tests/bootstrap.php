<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * PHPUnit bootstrap: loads composer's dev autoloader, which transitively
 * loads tests/stubs.php (declared under autoload-dev.files in composer.json).
 *
 * The stubs provide minimal declarations of the OCP symbols our lib/
 * classes touch at class-declaration time — `OCP\AppFramework\App`,
 * `OCP\AppFramework\Bootstrap\IBootstrap`, etc. They allow the tests in
 * this project to load `OCA\InterFonts\AppInfo\Application` without a
 * full Nextcloud server installation. Stubs stay minimal: only what a unit
 * test needs (CSSController needs Controller, IRequest, IURLGenerator and
 * DataDisplayResponse); behaviour that needs a real server is tested in CI
 * against a real Nextcloud via the integration workflow.
 */

$autoload = __DIR__ . '/../vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(
        STDERR,
        "vendor/autoload.php not found. Run `composer install` first.\n",
    );
    exit(1);
}

require $autoload;
