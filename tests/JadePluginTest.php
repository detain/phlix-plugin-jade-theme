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
     * Value-shape law (review theme-jade__srphp m10): every shipped token
     * value must be exactly one of —
     *  - a hex color: 3, 4, 6 or 8 digits after '#';
     *  - a functional rgb() color: three integer 0-255 components;
     *  - a functional rgba() color: three integer components plus an alpha
     *    of 0, 1, or a decimal below 1 ("0.x" or ".x");
     *  - a bare unitless number (the grain knob, e.g. "0.035").
     * A typo'd '#50c87' (5 hex digits) or an 'rgb(80, 200' with missing
     * arity must redden this test, not sail green.
     */
    private const TOKEN_VALUE_SHAPE = '/^(?:#[0-9a-f]{3}|#[0-9a-f]{4}|#[0-9a-f]{6}|#[0-9a-f]{8}'
        . '|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\)'
        . '|rgba\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*(?:0?\.\d+|0|1)\s*\)'
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
