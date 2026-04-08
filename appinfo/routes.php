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
 *   Returns text/css with the @font-face declarations and metric-compatible
 *   fallback. URLs inside @font-face point to the font routes below.
 *   Injected globally via Util::addHeader() in BeforeTemplateRenderedListener.
 *
 *   NOTE: The URL is /stylesheet rather than /css because Apache and nginx
 *   resolve physical directories before running mod_rewrite/try_files. If
 *   the route URL ever collides with a real directory in the app tree, the
 *   web server returns 403/404 before Nextcloud's router is reached.
 *
 * interfonts.Font.serve
 *   GET /font/{filename}
 *   Streams a WOFF2 file from fonts/ with long-lived immutable cache headers.
 *   Filename is constrained by route regex AND validated against an explicit
 *   allowlist generated from the bundled Inter version, so path traversal is
 *   impossible by construction. The version is part of the filename
 *   (e.g. InterVariable-4.1.woff2), so font upgrades automatically invalidate
 *   browser caches without any cache-busting query string.
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
