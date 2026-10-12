<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Inter Fonts App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\InterFonts\Tests\Unit\Controller;

use OCA\InterFonts\Controller\CSSController;
use OCP\IRequest;
use OCP\IURLGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Pins which elements the generated stylesheet forces to monospace.
 *
 * Nextcloud core resets <pre> to `font-family: inherit`, and apps use it for
 * prose (Tables column descriptions, issue #27), so it must keep the UI font.
 * Code blocks put their text in <code>, which stays covered.
 */
#[CoversClass(CSSController::class)]
final class CSSControllerTest extends TestCase {

    private function css(): string {
        $urlGenerator = $this->createMock(IURLGenerator::class);
        $urlGenerator->method('linkToRoute')->willReturn('/apps/interfonts/font/x.woff2');

        return (new CSSController($this->createMock(IRequest::class), $urlGenerator))
            ->stylesheet()
            ->getData();
    }

    /**
     * @return array<int, array{selectors: string, body: string}>
     */
    private function rules(): array {
        $css = preg_replace('#/\*.*?\*/#s', '', $this->css());
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', (string)$css, $m, PREG_SET_ORDER);

        return array_map(
            static fn (array $r): array => ['selectors' => trim($r[1]), 'body' => $r[2]],
            $m,
        );
    }

    private function monospaceSelectors(): string {
        foreach ($this->rules() as $rule) {
            if (str_contains($rule['body'], 'Lucida Console')) {
                return $rule['selectors'];
            }
        }
        self::fail('No monospace rule found in the stylesheet');
    }

    public function testMonospaceRuleCoversCodeLikeElementsButNotPre(): void {
        $selectors = $this->monospaceSelectors();

        foreach ([':root code', ':root kbd', ':root samp', ':root .cm-content'] as $expected) {
            self::assertStringContainsString($expected, $selectors);
        }
        self::assertDoesNotMatchRegularExpression('/(^|[\s,])pre\b/', $selectors);
    }

    public function testPreIsNeverGivenAFontFamily(): void {
        foreach ($this->rules() as $rule) {
            if (!str_contains($rule['body'], 'font-family')) {
                continue;
            }
            foreach (explode(',', $rule['selectors']) as $selector) {
                self::assertDoesNotMatchRegularExpression(
                    '/(^|\s)pre$/',
                    trim($selector),
                    "<pre> must not get a font-family: $selector",
                );
            }
        }
    }
}
