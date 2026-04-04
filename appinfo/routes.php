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
 *   GET /css
 *   Returns text/css with the @font-face declarations.
 *   URLs inside @font-face point to the font routes below.
 *   Injected globally via Util::addStyle() in BeforeTemplateRenderedListener.
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
            'url'  => '/css',
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
