<?php

/**
 * Phlix Jade theme plugin: an emerald ui-theme provider.
 *
 * @copyright 2026 Phlix contributors
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
 * the built-in `midnight` base and layers a complete token override to
 * achieve its distinctive jewel-green appearance.
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
     * @return array<class-string, string> Always empty.
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
                    // Accent ramp — jade green tones.
                    '--accent' => '#50c878',
                    '--accent-hover' => '#6ee89a',
                    '--accent-active' => '#3da85e',
                    '--accent-soft' => 'rgba(80, 200, 120, 0.15)',
                    '--accent-ring' => 'rgba(80, 200, 120, 0.45)',
                    '--accent-text' => '#e8fff0',

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
                    '--text-subtle' => '#62786e',
                    '--text-faint' => '#3a4a40',
                    '--text-on-accent' => '#050a07',

                    // Borders.
                    '--border' => '#1e2e28',
                    '--border-subtle' => '#141e1a',
                    '--border-strong' => '#2d4238',

                    // Atmosphere.
                    '--grain-opacity' => '0.035',
                    '--vignette' => 'rgba(0, 0, 0, 0.55)',
                    '--ambient' => 'rgba(80, 200, 120, 0.12)',

                    // Legacy `--color-*` aliases — only the ones the shipped SPA
                    // still reads. colors.css declares each of these as
                    // `var(--modern-token)` inside every theme block, so they
                    // would in principle follow the overrides above through the
                    // cascade; they are set explicitly anyway because that is
                    // one fewer cascade assumption between a plugin author and a
                    // correct render.
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