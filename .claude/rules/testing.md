---
description: PHPUnit, PHPStan, and PHPCS conventions for the Jade theme plugin suite and dev stubs
paths:
  - tests/**
  - dev-stubs/**
---

# Testing and Static Analysis

- Run checks through the Composer scripts: `composer test`, `composer phpstan`, `composer phpcs`.
- `tests/bootstrap.php` requires `vendor/autoload.php`, then loads `dev-stubs/LifecycleInterface.php` and `dev-stubs/ThemeSourceInterface.php` only behind `interface_exists()` guards. Never require a stub unconditionally — a run inside a Phlix server checkout must exercise the real contract.
- `Phlix\Theming\ThemeSourceInterface` is declared by the server and ships in no Composer package. Test-only shims for host contracts belong in `dev-stubs/`.
- PHPStan runs at level `9` over `src` (`tests` and `vendor` are excluded). Invoke it as `vendor/bin/phpstan analyse -c phpstan.neon` so `scanDirectories: dev-stubs` is honored; `phpstan analyse src` misses the stubs.
- PHPCS enforces `PSR12` across `src`, `tests`, and `dev-stubs`. Invoke it as `vendor/bin/phpcs --standard=phpcs.xml` so all three directories are checked.
- Every PHP file opens with a docblock file header and `declare(strict_types=1);`, and ends with a trailing newline.
- Tests live in `tests/` under the `Phlix\Jade\Tests\` namespace as `final class` extending `PHPUnit\Framework\TestCase`.
- `.github/workflows/test.yml` runs the same three checks on PHP `8.3` and `8.4`; coverage is uploaded to Codacy from the `8.3` leg only.
