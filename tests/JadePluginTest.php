<?php
declare(strict_types=1);

namespace Phlix\Jade\Tests;

use Phlix\Jade\JadePlugin;
use Phlix\Shared\Plugin\LifecycleInterface;
use Phlix\Theming\ThemeSourceInterface;
use PHPUnit\Framework\TestCase;

final class JadePluginTest extends TestCase
{
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
            $this->assertStringStartsWith('--', $tokenName, "Token name must start with '--'");

            // Token values must NOT contain var() references (self-referential)
            $this->assertStringNotContainsString('var(', $tokenValue, "Token value must not contain var() references");
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
