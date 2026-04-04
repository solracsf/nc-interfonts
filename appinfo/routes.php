<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/**
 * Route map
 * ---------
 *
 * interfonts.CSS.stylesheet
 *   GET /stylesheet
 *   Returns text/css with the @font-face declarations.
 *   URLs inside @font-face point to the font routes below.
 *   Injected globally via Util::addHeader() in BeforeTemplateRenderedListener.
 *
 *   NOTE: The URL was intentionally changed from /css to /stylesheet.
 *   The app ships a real css/ directory on disk.  Apache and nginx both
 *   resolve a physical directory before the index.php rewrite rule fires,
 *   so GET /apps/interfonts/css was served as a directory listing (403/404)
 *   instead of reaching this controller.  Using /stylesheet avoids any
 *   name collision with files or directories that exist in the app tree.
 *
 * interfonts.Font.serve
 *   GET /font/{filename}
 *   Streams a WOFF2 file from fonts/ with long-lived cache headers.
 *   Filename is validated against an allowlist — no path traversal possible.
 */
return [
    'routes' => [
        [
            'name' => 'CSS#stylesheet',
            'url'  => '/stylesheet',
            'verb' => 'GET',
        ],
        [
            'name'         => 'Font#serve',
            'url'          => '/font/{filename}',
            'verb'         => 'GET',
            'requirements' => ['filename' => '[a-zA-Z0-9._-]+'],
        ],
    ],
];
