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
 *  - `--text-faint` is bound by the ui as the AuthField ::placeholder color —
 *    placeholder text is NOT a disabled control, so it is AA-bound (>= 4.5:1)
 *    on the grounds it paints, while staying strictly below --text-subtle.
 *  - `--accent-ring` is a box-shadow focus indicator (the ui has no outline
 *    fallback), so the COMPOSITED result over each ground is the visible
 *    boundary and must reach WCAG 1.4.11 (>= 3:1) against that ground.
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

    /** WCAG 1.4.11 floor for the focus-indicator boundary against its ground. */
    private const AA_UI_COMPONENT_RATIO = 3.0;

    /**
     * Alpha the SPA paints `--surface` at for an auth input field.
     *
     * Input placeholders are not painted on the opaque surface token: the
     * field is a translucent fill over the page background, so the effective
     * ground is `--surface` composited onto `--bg` at this alpha. The value
     * is read from the shipped ui source (phlix-ui
     * src/components/auth/AuthField.vue: `.authfield__input { background:
     * color-mix(in srgb, var(--surface) 80%, transparent) }`), not assumed —
     * the review measured the placeholder pair on the composite ground for
     * exactly this reason.
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

    #[Test]
    public function testAccentRingCompositesAboveTheFocusIndicatorFloorOnEveryGround(): void
    {
        $tokens = $this->tokens();
        [$rgb, $alpha] = $this->parseRgba($tokens['--accent-ring']);

        foreach ($this->grounds() as $name => $ground) {
            $hex = sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
            $composited = $this->composite($hex, $alpha, $ground);
            $ratio = $this->contrastRatio($composited, $ground);
            $this->assertGreaterThanOrEqual(
                self::AA_UI_COMPONENT_RATIO,
                $ratio,
                sprintf(
                    '--accent-ring %s composited on %s (%s) is %.2f:1 — below the 3:1 WCAG 1.4.11 '
                    . 'floor for the focus indicator, the ui\'s primary keyboard affordance',
                    $tokens['--accent-ring'],
                    $name,
                    $ground,
                    $ratio
                )
            );
        }
    }

    #[Test]
    public function testTextFaintMeetsAaOnItsGradedGrounds(): void
    {
        $tokens = $this->tokens();
        $ink = $tokens['--text-faint'];
        // webux M3 measured faint at 2.12/1.99/2.02 on exactly these grounds:
        // the auth page bg, the card surface, and the translucent login input
        // where AuthField binds it as ::placeholder. The topmost elevations
        // carry no faint text in the shipped flows, and the disabled-control
        // reading of this tier is WCAG 1.4.3-exempt outright.
        $graded = [
            '--bg' => $tokens['--bg'],
            '--surface' => $tokens['--surface'],
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
                    '--text-faint %s on %s (%s) is %.2f:1 — below the 4.5:1 AA floor',
                    $ink,
                    $name,
                    $ground,
                    $ratio
                )
            );
        }

        // Ramp law: faint must stay strictly darker than subtle or the two
        // tiers collapse into one (the cure must lift, not flatten).
        $this->assertLessThan(
            $this->relativeLuminance($this->parseHex($tokens['--text-subtle'])),
            $this->relativeLuminance($this->parseHex($ink)),
            '--text-faint must stay strictly darker than --text-subtle'
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
     * @return array{array{int, int, int}, float}  [rgb, alpha]
     */
    private function parseRgba(string $value): array
    {
        $matches = [];
        if (preg_match('/^rgba\(\s*(\d+),\s*(\d+),\s*(\d+),\s*(0?\.\d+|1(?:\.0+)?)\s*\)$/', $value, $matches) !== 1) {
            throw new RuntimeException("Ring law expects literal rgba(r, g, b, a), got '$value'");
        }

        return [[(int) $matches[1], (int) $matches[2], (int) $matches[3]], (float) $matches[4]];
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
