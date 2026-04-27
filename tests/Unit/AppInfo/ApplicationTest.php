<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\InterFonts\Tests\Unit\AppInfo;

use OCA\InterFonts\AppInfo\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the static helpers on Application.
 *
 * These cover the data that the security-critical FontController allowlist
 * is built from: if {@see Application::allowedFontFilenames()} ever drifts
 * from what is on disk, FontController will start serving 404s — or, worse,
 * a future bug could let it serve files outside `fonts/`. The path-traversal
 * test below pins that invariant.
 *
 * No Nextcloud server is required: tests/stubs.php declares the OCP
 * parent class and the IBootstrap interface so Application can be loaded
 * by the unit test runner without a full installation.
 */
#[CoversClass(Application::class)]
final class ApplicationTest extends TestCase {

    private const FONTS_DIR = __DIR__ . '/../../../fonts';

    public function testAppIdIsInterfonts(): void {
        self::assertSame('interfonts', Application::APP_ID);
    }

    public function testRouteConstantsFollowNextcloudNamingConvention(): void {
        // Nextcloud derives route names as <app-id>.<controller-short>.<method>
        // from #[FrontpageRoute] attributes — these constants must match what
        // the framework will actually generate, otherwise IURLGenerator::linkToRoute()
        // throws at runtime and the CSS / preload <link> tags break.
        self::assertSame('interfonts.CSS.stylesheet', Application::ROUTE_STYLESHEET);
        self::assertSame('interfonts.Font.serve',     Application::ROUTE_FONT);
    }

    public function testInterVersionMatchesVersionFile(): void {
        $raw = file_get_contents(self::FONTS_DIR . '/inter-version.txt');
        self::assertNotFalse($raw, 'fonts/inter-version.txt must be readable');

        $expected = ltrim(trim($raw), 'vV');
        self::assertSame($expected, Application::interVersion());
    }

    public function testInterVersionIsNonEmpty(): void {
        $version = Application::interVersion();
        self::assertNotSame('', $version);
        self::assertNotSame('unknown', $version, 'inter-version.txt produced a fallback — repo is broken');
    }

    public function testRomanFilenameShape(): void {
        $name = Application::romanFontFilename();
        self::assertStringStartsWith('InterVariable-', $name);
        self::assertStringEndsWith('.woff2', $name);
        self::assertStringNotContainsString('Italic', $name);
        self::assertStringContainsString(Application::interVersion(), $name);
    }

    public function testItalicFilenameShape(): void {
        $name = Application::italicFontFilename();
        self::assertStringStartsWith('InterVariable-Italic-', $name);
        self::assertStringEndsWith('.woff2', $name);
        self::assertStringContainsString(Application::interVersion(), $name);
    }

    public function testAllowedFontFilenamesContainsExactlyRomanAndItalic(): void {
        $list = Application::allowedFontFilenames();
        self::assertCount(2, $list);
        self::assertContains(Application::romanFontFilename(), $list);
        self::assertContains(Application::italicFontFilename(), $list);
    }

    /**
     * Security pin: the allowlist must never contain a filename with a
     * directory separator or `..` segment. FontController's basename()
     * defence catches these too, but failing the test here surfaces the
     * regression at the data layer instead of at the (mocked-away)
     * controller layer.
     */
    public function testAllowedFilenamesAreFlatBasenames(): void {
        foreach (Application::allowedFontFilenames() as $name) {
            self::assertStringNotContainsString('/',  $name, "allowlisted name must not contain '/': $name");
            self::assertStringNotContainsString('\\', $name, "allowlisted name must not contain '\\': $name");
            self::assertStringNotContainsString('..', $name, "allowlisted name must not contain '..': $name");
            self::assertNotSame('', $name);
            self::assertSame(basename($name), $name, "allowlisted name must equal its basename: $name");
        }
    }

    /**
     * If this fails, the bundled WOFF2s in fonts/ no longer match
     * inter-version.txt — usually because a font upgrade promoted
     * the version file but forgot to ship the new binaries.
     */
    public function testAllowedFontFilesExistOnDisk(): void {
        foreach (Application::allowedFontFilenames() as $name) {
            $path = self::FONTS_DIR . '/' . $name;
            self::assertFileExists($path, "Allowlisted font file does not exist: $name");
            self::assertFileIsReadable($path);
            self::assertGreaterThan(0, (int)filesize($path), "Allowlisted font file is empty: $name");
        }
    }
}
