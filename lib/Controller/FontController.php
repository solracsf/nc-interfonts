<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\InterFonts\Controller;

use OCA\InterFonts\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\IRequest;

/**
 * Streams Inter WOFF2 font files from the app's fonts/ directory.
 *
 * Security
 * --------
 * The {filename} route parameter is constrained by the regex on the
 * #[FrontpageRoute] attribute (`[a-zA-Z0-9.-]+`) and then validated against
 * an explicit allowlist generated from the bundled Inter version. Path
 * traversal is impossible by construction — there is no concatenation of
 * user input into the filesystem path beyond the allowlisted basename.
 *
 * Caching
 * -------
 * Font binaries are immutable for a given upstream Inter version. Because
 * the version is part of the filename (e.g. `InterVariable-4.1.woff2`),
 * a font upgrade automatically produces a new URL — there is no need for
 * any cache-busting query string. We send `Cache-Control: public,
 * max-age=31536000, immutable` so browsers cache aggressively and only
 * re-fetch when the URL changes.
 *
 * CORS
 * ----
 * `Access-Control-Allow-Origin: *` is set so that `<link rel="preload"
 * as="font" crossorigin>` from the listener correctly matches the actual
 * font request — without it, the preload would be ignored and the browser
 * would issue a second, non-preloaded request. Fonts are intentionally
 * public assets, so a wildcard origin leaks nothing.
 *
 * Why StreamResponse and not DataDisplayResponse?
 * -----------------------------------------------
 * StreamResponse opens the file with fopen()/readfile() at render time and
 * pipes it to the output buffer without ever loading the full WOFF2 into
 * a PHP string. DataDisplayResponse, by contrast, buffers the entire body
 * into memory before sending. WOFF2 files are small (~330 KB each) so the
 * difference is modest, but StreamResponse is the idiomatic Nextcloud
 * primitive for binary file delivery.
 */
final class FontController extends Controller {

    public function __construct(
        IRequest $request,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    /**
     * Serves a WOFF2 font file.
     *
     * Route: GET /apps/interfonts/font/{filename}
     *
     * @param string $filename The bundled WOFF2 filename, e.g.
     *                         InterVariable-4.1.woff2 or
     *                         InterVariable-Italic-4.1.woff2
     */
    #[FrontpageRoute(
        verb: 'GET',
        url: '/font/{filename}',
        requirements: ['filename' => '[a-zA-Z0-9.-]+'],
    )]
    #[PublicPage]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function serve(string $filename): StreamResponse|NotFoundResponse {
        // Allowlist check — belt-and-suspenders on top of the route regex.
        if (!in_array($filename, Application::allowedFontFilenames(), true)) {
            return new NotFoundResponse();
        }

        // basename() defends against any future allowlist mistake that
        // accidentally includes a directory separator.
        $fontPath = __DIR__ . '/../../fonts/' . basename($filename);

        if (!is_file($fontPath) || !is_readable($fontPath)) {
            return new NotFoundResponse();
        }

        $response = new StreamResponse($fontPath);
        $response->addHeader('Content-Type', 'font/woff2');
        $response->addHeader('Cache-Control', 'public, max-age=31536000, immutable');
        $response->addHeader('X-Content-Type-Options', 'nosniff');
        $response->addHeader('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
