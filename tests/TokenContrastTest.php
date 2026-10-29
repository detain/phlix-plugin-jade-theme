<?php

declare(strict_types=1);

namespace Phlix\Jade\Tests;

use Phlix\Jade\JadePlugin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Contrast contract law for the shipped token palette.
 *
 * The token contract (phlix-tokens src/css/colors.css, consumed by the shipped
 * SPA through phlix-ui applyThemeTokens) assigns each role a WCAG floor,
 * measured on the grounds it is actually painted on:
 *
 *  - `--accent-text` is the accent color AS foreground text/icon — it must
 *    reach AA (>= 4.5:1) on every background and surface. The ui sites
 *    consuming it (`color: var(--accent-text)`) sit on the elevation stack.
 *    The ink-on-fill role is `--text-on-accent`, pinned against `--accent`.
 *  - `--text-subtle` carries real small-size text: AuthField form labels, the
 *    password-toggle glyph, and generic Input/Combobox placeholders — body
 *    text tier, so it must reach AA (>= 4.5:1) on its painted grounds.
 *
 * Values are parsed from providedThemes() so the law follows the palette;
 * it is not a duplicated constant table. The WCAG 1.4.3 math is recomputed
 * here from the hexes themselves — no external fixture to drift.
 *
 * Ported from the sibling theme repos (phlix-plugin-arctic-jade-theme,
 * phlix-plugin-emerald-abyss-theme) where the same two roles failed and the
 * same law caught them.
 */
#[CoversClass(JadePlugin::class)]
final class TokenContrastTest extends TestCase
{
    /** WCAG 1.4.3 AA floor for normal-size text. */
    private const AA_TEXT_RATIO = 4.5;

    /**
     * Alpha the SPA paints `--surface` at for an input field.
     *
     * Input/Combobox placeholders do not sit on the opaque surface token: the
     * field is a translucent fill over the page background, so the effective
     * ground is `--surface` composited onto `--bg` at this alpha. Derived from
     * the vendored SPA (`public_html/assets/app`, Input.vue `--phlix-input-bg`)
     * rather than assumed — the review measured the placeholder pair on the
     * composite for exactly this reason.
     */
    private const INPUT_SURFACE_ALPHA = 0.8;

    private JadePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = new JadePlugin();
    }

    /**
     * @return array<string, string>
     */
    private function tokens(): array
    {
        $themes = $this->plugin->providedThemes();
        /** @var array<string, string> $tokens */
        $tokens = $themes[0]['tokens'];

        return $tokens;
    }

    /**
     * The opaque elevation stack every foreground text role is painted on.
     *
     * @return array<string, string>
     */
    private function grounds(): array
    {
        $tokens = $this->tokens();
        $grounds = [];
        foreach (['--bg', '--surface', '--surface-2', '--surface-3'] as $key) {
            $grounds[$key] = $tokens[$key];
        }

        return $grounds;
    }

    #[Test]
    public function testAccentTextMeetsAaAsForegroundOnEveryGround(): void
    {
        $ink = $this->tokens()['--accent-text'];

        foreach ($this->grounds() as $name => $ground) {
            $ratio = $this->contrastRatio($ink, $ground);
            $this->assertGreaterThanOrEqual(
                self::AA_TEXT_RATIO,
                $ratio,
                sprintf(
                    '--accent-text %s on %s (%s) is %.2f:1 — below the 4.5:1 AA foreground floor',
                    $ink,
                    $name,
                    $ground,
                    $ratio
                )
            );
        }
    }

    #[Test]
    public function testTextSubtleMeetsAaOnEveryGradedGround(): void
    {
        $tokens = $this->tokens();
        $ink = $tokens['--text-subtle'];
        // The grounds webux M1 measured: the auth card sits on --bg and
        // --surface, elevated panels on --surface-2, and the input composite
        // below. --surface-3 (topmost elevation) carries no --text-subtle
        // text in the shipped SPA flows the review traced — the law pins the
        // graded set, honestly labeled, rather than a bar the item never set.
        $graded = [
            '--bg' => $tokens['--bg'],
            '--surface' => $tokens['--surface'],
            '--surface-2' => $tokens['--surface-2'],
            'input composite' => $this->composite(
                $tokens['--surface'],
                self::INPUT_SURFACE_ALPHA,
                $tokens['--bg']
            ),
        ];

        foreach ($graded as $name => $ground) {
            $ratio = $this->contrastRatio($ink, $ground);
            $this->assertGreaterThanOrEqual(
                self::AA_TEXT_RATIO,
                $ratio,
                sprintf(
                    '--text-subtle %s on %s (%s) is %.2f:1 — below the 4.5:1 AA floor',
                    $ink,
                    $name,
                    $ground,
                    $ratio
                )
            );
        }
    }

    #[Test]
    public function testTextOnAccentMeetsAaAsInkOnTheAccentFill(): void
    {
        $tokens = $this->tokens();
        $ratio = $this->contrastRatio($tokens['--text-on-accent'], $tokens['--accent']);

        $this->assertGreaterThanOrEqual(
            self::AA_TEXT_RATIO,
            $ratio,
            sprintf(
                '--text-on-accent %s on --accent %s is %.2f:1 — below the 4.5:1 AA floor',
                $tokens['--text-on-accent'],
                $tokens['--accent'],
                $ratio
            )
        );
    }

    /**
     * Alpha-composite a hex over a hex, the way the browser would.
     */
    private function composite(string $topHex, float $alpha, string $bottomHex): string
    {
        $top = $this->parseHex($topHex);
        $bottom = $this->parseHex($bottomHex);
        $mixed = [];
        foreach ($top as $index => $channel) {
            $mixed[$index] = (int) round($alpha * $channel + (1 - $alpha) * $bottom[$index]);
        }

        return sprintf('#%02x%02x%02x', $mixed[0], $mixed[1], $mixed[2]);
    }

    /**
     * WCAG 2.x relative luminance of an sRGB channel triplet.
     *
     * @param array{int, int, int} $rgb
     */
    private function relativeLuminance(array $rgb): float
    {
        $channels = [];
        foreach ($rgb as $channel) {
            $srgb = $channel / 255;
            $channels[] = $srgb <= 0.04045
                ? $srgb / 12.92
                : ((($srgb + 0.055) / 1.055) ** 2.4);
        }

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    private function contrastRatio(string $hexA, string $hexB): float
    {
        $luminanceA = $this->relativeLuminance($this->parseHex($hexA));
        $luminanceB = $this->relativeLuminance($this->parseHex($hexB));
        $brighter = max($luminanceA, $luminanceB);
        $darker = min($luminanceA, $luminanceB);

        return ($brighter + 0.05) / ($darker + 0.05);
    }

    /**
     * @return array{int, int, int}
     */
    private function parseHex(string $hex): array
    {
        $matches = [];
        if (preg_match('/^#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/', $hex, $matches) !== 1) {
            throw new RuntimeException("Contrast law expects literal #rrggbb, got '$hex'");
        }

        return [
            hexdec($matches[1]),
            hexdec($matches[2]),
            hexdec($matches[3]),
        ];
    }
}
