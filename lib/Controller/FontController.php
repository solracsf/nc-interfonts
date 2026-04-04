<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\InterFonts\Controller;

use OCA\InterFonts\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\IRequest;

/**
 * Streams Inter WOFF2 font files from the app's fonts/ directory.
 *
 * Security
 * --------
 * The {filename} route parameter is constrained by a regex in routes.php
 * ([a-zA-Z0-9._-]+) and then validated against an explicit allowlist here,
 * so path traversal is impossible by construction.
 *
 * Caching
 * -------
 * Font files are immutable for a given app version, so we serve them with
 * Cache-Control: public, max-age=31536000 (1 year). Browsers will re-fetch
 * only when the URL changes, which happens on app upgrade because the route
 * is generated fresh each time.
 */
class FontController extends Controller {

    /**
     * Explicit allowlist of font files this controller will serve.
     * Any filename not in this list returns 404.
     */
    private const ALLOWED_FILES = [
        'Inter.var.woff2',
        'InterItalic.var.woff2',
    ];

    public function __construct(
        IRequest $request,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    /**
     * Serves a WOFF2 font file.
     *
     * Route: GET /index.php/apps/interfonts/font/{filename}
     *
     * @param string $filename One of Inter.var.woff2 or InterItalic.var.woff2
     */
    #[PublicPage]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function serve(string $filename): DataDisplayResponse|NotFoundResponse {
        // Allowlist check — belt-and-suspenders on top of the route regex.
        if (!in_array($filename, self::ALLOWED_FILES, true)) {
            return new NotFoundResponse();
        }

        $fontPath = __DIR__ . '/../../fonts/' . $filename;

        if (!is_file($fontPath) || !is_readable($fontPath)) {
            return new NotFoundResponse();
        }

        $data = file_get_contents($fontPath);

        if ($data === false) {
            return new NotFoundResponse();
        }

        return new DataDisplayResponse($data, Http::STATUS_OK, [
            'Content-Type'   => 'font/woff2',
            'Cache-Control'  => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
