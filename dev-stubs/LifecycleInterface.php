<?php

/**
 * Stub for LifecycleInterface - provides IDE autocompletion when the
 * shared package is not installed in the development environment.
 *
 * This file is indexed by phpstan through `scanDirectories` in
 * phpstan.neon (deliberately included, never excluded via scanFiles)
 * so the host contract resolves during CI; it also supports
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
     * Returns a map of event class names to handler method names.
     *
     * @return array<class-string, string> Map of event class to method name.
     */
    public function subscribedEvents(): array;
}
