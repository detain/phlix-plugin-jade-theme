<?php

declare(strict_types=1);

namespace Phlix\Jade\Tests;

use Phlix\Jade\JadePlugin;
use Phlix\Shared\Plugin\LifecycleInterface;
use Phlix\Theming\ThemeSourceInterface;
use PHPUnit\Framework\TestCase;

final class JadePluginTest extends TestCase
{
    /**
     * One rgb()/rgba() color channel: an integer 0-255 (no leading zeros),
     * with optional surrounding whitespace.
     */
    private const CHANNEL = '\s*(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)\s*';

    /**
     * Value-shape law: every shipped token
     * value must be exactly one of —
     *  - a hex color: 3, 4, 6 or 8 digits after '#';
     *  - a functional rgb() color: three integer 0-255 components;
     *  - a functional rgba() color: three integer 0-255 components plus an
     *    alpha of 0, 1, or a decimal below 1 ("0.x" or ".x");
     *  - a bare unitless number (the grain knob, e.g. "0.035").
     * A typo'd '#50c87' (5 hex digits), an 'rgb(80, 200' with missing
     * arity, or an out-of-range 'rgb(256, 0, 0)' must redden this test, not
     * sail green. The 0-255 bound is enforced by self::CHANNEL, not merely
     * claimed here.
     */
    private const TOKEN_VALUE_SHAPE = '/^(?:#[0-9a-f]{3}|#[0-9a-f]{4}|#[0-9a-f]{6}|#[0-9a-f]{8}'
        . '|rgb\(' . self::CHANNEL . ',' . self::CHANNEL . ',' . self::CHANNEL . '\)'
        . '|rgba\(' . self::CHANNEL . ',' . self::CHANNEL . ',' . self::CHANNEL
        . ',\s*(?:0?\.\d+|0|1)\s*\)'
        . '|\d*\.?\d+)$/i';

    private JadePlugin $plugin;

    protected function setUp(): void
    {
        $this->plugin = new JadePlugin();
    }

    public function testImplementsLifecycleInterface(): void
    {
        $this->assertInstanceOf(LifecycleInterface::class, $this->plugin);
    }

    public function testImplementsThemeSourceInterface(): void
    {
        $this->assertInstanceOf(ThemeSourceInterface::class, $this->plugin);
    }

    public function testThemeSourceNameReturnsCorrectValue(): void
    {
        $this->assertSame('jade', $this->plugin->themeSourceName());
    }

    public function testProvidedThemesReturnsExpectedStructure(): void
    {
        $themes = $this->plugin->providedThemes();

        $this->assertIsArray($themes);
        $this->assertCount(1, $themes);

        $theme = $themes[0];
        $this->assertArrayHasKey('id', $theme);
        $this->assertArrayHasKey('name', $theme);
        $this->assertArrayHasKey('dark', $theme);
        $this->assertArrayHasKey('extends', $theme);
        $this->assertArrayHasKey('tokens', $theme);

        $this->assertSame('jade', $theme['id']);
        $this->assertSame('Jade', $theme['name']);
        $this->assertTrue($theme['dark']);
        $this->assertSame('midnight', $theme['extends']);
    }

    public function testAllTokensAreValidCssValues(): void
    {
        $themes = $this->plugin->providedThemes();
        $tokens = $themes[0]['tokens'];

        $this->assertIsArray($tokens);

        foreach ($tokens as $tokenName => $tokenValue) {
            $this->assertIsString($tokenName, "Token name must be string");
            $this->assertIsString($tokenValue, "Token value must be string");

            // Token names must start with '--'
            $this->assertStringStartsWith('--', $tokenName, "Token name must start with '--");

            // Token values must NOT contain var() references (self-referential)
            $this->assertStringNotContainsString('var(', $tokenValue, "Token value must not contain var() references");

            // ...and must be a real CSS value shape (hex | rgb() | rgba() | number)
            $this->assertMatchesRegularExpression(
                self::TOKEN_VALUE_SHAPE,
                $tokenValue,
                "Token '$tokenName' value '$tokenValue' is not a valid CSS value shape"
            );
        }
    }

    /**
     * The shape law itself must hold at its edges — a guard that accepts
     * everything proves nothing about the shipped tokens.
     */
    public function testTokenValueShapeAcceptsTheLawAndRejectsItsNearMisses(): void
    {
        $accepted = [
            '#50c878', '#fff', '#ffff', '#50c878ff', '0.035', '.5',
            'rgb(0, 0, 0)', 'rgb(255, 255, 255)', 'rgb(199, 249, 250)',
            'rgba(80, 200, 120, 0.15)', 'rgba(255,255,255,1)', 'rgba(0, 0, 0, .5)', 'rgba(9, 99, 199, 0)',
        ];
        $rejected = [
            '#50c87', '#50c8781', 'rgb(80, 200', 'rgb(256, 0, 0)', 'rgb(0, 300, 0)', 'rgb(0, 0, 999)',
            'rgba(80, 200, 120)', 'rgba(260, 200, 120, 0.15)', 'rgba(80, 200, 120, 1.5)',
            'rgb(-1, 0, 0)', 'rgb(1000, 0, 0)', 'red',
        ];

        foreach ($accepted as $value) {
            $this->assertMatchesRegularExpression(
                self::TOKEN_VALUE_SHAPE,
                $value,
                "'{$value}' must pass the shape law"
            );
        }

        foreach ($rejected as $value) {
            $this->assertDoesNotMatchRegularExpression(
                self::TOKEN_VALUE_SHAPE,
                $value,
                "'{$value}' must fail the shape law"
            );
        }
    }

    public function testOnEnableDoesNotThrow(): void
    {
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);

        $this->plugin->onEnable($container);
        $this->assertTrue(true); // Assertion confirms no exception thrown
    }

    public function testOnDisableDoesNotThrow(): void
    {
        $this->plugin->onDisable();
        $this->assertTrue(true); // Assertion confirms no exception thrown
    }

    public function testSubscribedEventsReturnsEmptyArray(): void
    {
        $events = $this->plugin->subscribedEvents();
        $this->assertIsArray($events);
        $this->assertEmpty($events);
    }
}
