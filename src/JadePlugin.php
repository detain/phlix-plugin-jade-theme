<?php

/**
 * Phlix Jade theme plugin: an emerald ui-theme provider.
 *
 * @copyright 2026 Joe Huss <detain@interserver.net>
 * @license   MIT
 */

declare(strict_types=1);

namespace Phlix\Jade;

use Phlix\Shared\Plugin\LifecycleInterface;
use Phlix\Theming\ThemeSourceInterface;
use Psr\Container\ContainerInterface;

/**
 * Phlix **Jade** theme plugin.
 *
 * A deep emerald palette with luminous jade accent tones. The theme extends
 * the built-in `midnight` base and layers a broad token override — 30 keys
 * across the accent ramp, status colors, surfaces, text tiers, borders,
 * atmosphere, and the legacy `--color-*` aliases — to achieve its distinctive
 * jewel-green appearance; among status colors only `--success` and
 * `--success-bg` are re-inked, while the error/warning/info roles stay
 * inherited from midnight.
 *
 * @package Phlix\Jade
 * @since 1.0.0
 */
final class JadePlugin implements LifecycleInterface, ThemeSourceInterface
{
    /**
     * Canonical provenance key for this source.
     *
     * The host keys the registry's provenance map on this, so re-enabling
     * REPLACES this plugin's themes instead of duplicating them, and disabling
     * removes exactly this id. Keep it constant across versions.
     */
    public const SOURCE_NAME = 'jade';

    /**
     * Nothing to do — the host registers the theme off the `instanceof`.
     *
     * @param ContainerInterface $container The host container (unused).
     */
    public function onEnable(ContainerInterface $container): void
    {
    }

    /**
     * Nothing to do — the host deregisters this source by name on disable.
     */
    public function onDisable(): void
    {
    }

    /**
     * A theme plugin subscribes to no events.
     *
     * @return array<class-string, string|callable> Always empty.
     */
    public function subscribedEvents(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function themeSourceName(): string
    {
        return self::SOURCE_NAME;
    }

    /**
     * @inheritDoc
     *
     * @return list<array<array-key, mixed>>
     */
    public function providedThemes(): array
    {
        return [
            [
                'id' => 'jade',
                'name' => 'Jade',
                'dark' => true,
                'extends' => 'midnight',
                'tokens' => [
                    // Accent ramp — jade green tones. NOTE the two ink roles
                    // are opposites: --accent-text is the accent as FOREGROUND
                    // text on the surface stack (light ink, AA-pinned by
                    // tests/TokenContrastTest.php); --text-on-accent is the
                    // dark ink drawn ON --accent fills. Not interchangeable.
                    '--accent' => '#50c878',
                    '--accent-hover' => '#6ee89a',
                    '--accent-active' => '#3da85e',
                    '--accent-soft' => 'rgba(80, 200, 120, 0.15)',
                    '--accent-ring' => 'rgba(110, 232, 154, 0.55)',
                    '--accent-text' => '#e8fff0',

                    // Semantic status colors. The inherited midnight
                    // --success (#61c073, phlix-tokens src/css/colors.css,
                    // Midnight block) collided with jade's green identity:
                    // WCAG 1.4.3 relative luminance Y(#61c073)=0.4148 vs
                    // Y(--accent #50c878)=0.4437 — a 1.062:1 pair with only
                    // 8.6° of HSL hue separation (131.4° vs 140.0°), i.e. a
                    // success badge read as an accent chip (accessibility
                    // review N1, 1.062:1). The base pattern ("let midnight
                    // provide status colors") is safe under the amber
                    // identity it was tuned for; under a green accent the
                    // override is the theme's job. #2fb98e keeps success
                    // green (semantics intact) but pulls it cooler and
                    // darker, measured against --accent (the pair that
                    // collided): Y=0.3726 (ΔY −0.071 vs --accent), hue
                    // 161.3° (Δ21.3° from --accent's 140.0°, toward teal),
                    // pair contrast with --accent 1.168:1. (Against the
                    // inherited #61c073 it sits ΔY −0.042 and Δ29.9° hue.)
                    // It reaches AA >= 4.5:1 on every ground it paints:
                    //   on --bg 8.03, --surface 7.54, --surface-2 6.92,
                    //   --surface-3 6.05; success text over its own
                    //   --success-bg chip (0.16 alpha): 6.45 on --bg,
                    //   5.88 on --surface. Pinned by
                    // TokenContrastTest::testSuccessStaysDistinguishableFromTheAccentAndMeetsAaOnItsGrounds
                    // — the math is the same WCAG sRGB linearization the
                    // test recomputes.
                    '--success' => '#2fb98e',
                    // Chip tint for the same role, re-hued to match — the
                    // base rgba(97, 192, 115, 0.16) would sit warm while
                    // the text over it reads cool.
                    '--success-bg' => 'rgba(47, 185, 142, 0.16)',

                    // Background + elevation stack.
                    '--bg' => '#050a07',
                    '--surface' => '#0a1410',
                    '--surface-2' => '#101e18',
                    '--surface-3' => '#192a24',
                    '--surface-glass' => 'rgba(10, 20, 16, 0.65)',
                    '--surface-glass-strong' => 'rgba(5, 10, 7, 0.82)',

                    // Text ramp.
                    '--text' => '#e8f5ec',
                    '--text-muted' => '#9ab8a8',
                    '--text-subtle' => '#708a7c',
                    '--text-faint' => '#6a8473',
                    '--text-on-accent' => '#050a07',

                    // Borders.
                    '--border' => '#1e2e28',
                    '--border-subtle' => '#141e1a',
                    '--border-strong' => '#2d4238',

                    // Atmosphere.
                    '--grain-opacity' => '0.035',
                    '--vignette' => 'rgba(0, 0, 0, 0.55)',
                    '--ambient' => 'rgba(80, 200, 120, 0.12)',

                    // Legacy `--color-*` aliases — five of the names the shipped
                    // SPA reads, NOT the full set it reads: it also reads other
                    // aliases phlix-tokens colors.css declares (e.g.
                    // --color-text-secondary, --color-primary, the status
                    // aliases), which this manifest leaves inherited through
                    // `extends: midnight`, plus a few names (e.g. --color-muted,
                    // --color-fg, --color-accent) that no theme block declares
                    // at all — a phlix-ui/phlix-tokens gap, not one a theme
                    // manifest can close. colors.css declares each of the five
                    // below as `var(--modern-token)` inside every theme block,
                    // so they would in principle follow the overrides above
                    // through the cascade; they are set explicitly anyway
                    // because that is one fewer cascade assumption between a
                    // plugin author and a correct render.
                    '--color-bg' => '#050a07',
                    '--color-surface' => '#0a1410',
                    '--color-text' => '#e8f5ec',
                    '--color-text-muted' => '#9ab8a8',
                    '--color-border' => '#1e2e28',
                ],
            ],
        ];
    }
}
