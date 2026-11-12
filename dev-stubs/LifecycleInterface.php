<?php

/**
 * Stub for LifecycleInterface - provides IDE autocompletion when the
 * shared package is not installed in the development environment.
 *
 * This file is deliberately indexed via `scanDirectories` in
 * phpstan.neon so the host contract resolves during CI; it also supports
 * development workflows that don't have the full Phlix monorepo
 * installed.
 *
 * @copyright 2026 Joe Huss <detain@interserver.net>
 * @license   MIT
 */

declare(strict_types=1);

namespace Phlix\Shared\Plugin;

use Psr\Container\ContainerInterface;

/**
 * Interface for plugin lifecycle hooks.
 *
 * @internal This is a dev-stub, not for runtime use.
 */
interface LifecycleInterface
{
    /**
     * Called when the plugin is enabled.
     *
     * @param ContainerInterface $container The host service container.
     */
    public function onEnable(ContainerInterface $container): void;

    /**
     * Called when the plugin is disabled.
     */
    public function onDisable(): void;

    /**
     * Returns the PSR-14 listener subscriptions, keyed by event FQCN.
     *
     * Mirrors the real contract in detain/phlix-shared
     * (src/Plugin/LifecycleInterface.php): each value is either a method
     * name on the plugin instance or a PHP callable.
     *
     * @return array<class-string, string|callable> Subscriptions keyed by event FQCN.
     */
    public function subscribedEvents(): array;
}
