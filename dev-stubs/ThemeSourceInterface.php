<?php

/**
 * Stub for ThemeSourceInterface - provides IDE autocompletion when the
 * theming package is not installed in the development environment.
 *
 * This file is not analyzed by phpstan (excluded via scanFiles) and is
 * only present to support development workflows that don't have the
 * full Phlix monorepo installed.
 */

declare(strict_types=1);

namespace Phlix\Theming;

/**
 * Interface for theme providers that ship token overrides to the host.
 *
 * @internal This is a dev-stub, not for runtime use.
 */
interface ThemeSourceInterface
{
    /**
     * Returns the canonical identifier for this theme source.
     */
    public function themeSourceName(): string;

    /**
     * Returns a list of theme definitions supplied by this source.
     *
     * @return list<array<array-key, mixed>> Theme definition arrays.
     */
    public function providedThemes(): array;
}
