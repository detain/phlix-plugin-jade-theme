# Phlix Jade Theme Plugin

A deep emerald UI theme for Phlix with luminous jade accent tones.

## Theme Tokens

### Color Palette

| Token | Hex | Usage |
|-------|-----|-------|
| **Accent** | `#50c878` | Primary jade green accent |
| **Background** | `#050a07` | Deepest background layer |
| **Surface** | `#0a1410` | Card and panel backgrounds |
| **Surface 2** | `#101e18` | Elevated surface elements |
| **Surface 3** | `#192a24` | Highest elevation surfaces |
| **Text** | `#e8f5ec` | Primary text color |
| **Text Muted** | `#9ab8a8` | Secondary/muted text |
| **Text Subtle** | `#708a7c` | Tertiary/subtle text (AA ≥4.5:1 on bg/surface/elevated) |
| **Text Faint** | `#6a8473` | Faintest text tier; auth-form placeholder (AA ≥4.5:1 on its grounds) |
| **Border** | `#1e2e28` | Default border color |
| **Border Subtle** | `#141e1a` | Subtle divider lines |
| **Border Strong** | `#2d4238` | Emphasized borders |

### Atmosphere Effects

| Token | Value | Usage |
|-------|-------|-------|
| **Ambient** | `rgba(80, 200, 120, 0.12)` | Subtle green glow effects |
| **Vignette** | `rgba(0, 0, 0, 0.55)` | Edge darkening overlay |
| **Grain Opacity** | `0.035` | Film grain texture intensity |

### Accent Variants

| Token | Hex/Value | Usage |
|-------|-----------|-------|
| **Accent Hover** | `#6ee89a` | Hover state accent |
| **Accent Active** | `#3da85e` | Active/pressed state |
| **Accent Soft** | `rgba(80, 200, 120, 0.15)` | Soft background fills |
| **Accent Ring** | `rgba(110, 232, 154, 0.55)` | Focus ring color (composites ≥3:1 on every ground) |
| **Accent Text** | `#e8fff0` | Accent-colored foreground text and icons on bg/surfaces (the accent as ink) |
| **Text on Accent** | `#050a07` | Dark ink drawn on accent fills (buttons, badges) |

### Semantic Status Colors

| Token | Hex/Value | Usage |
|-------|-----------|-------|
| **Success** | `#2fb98e` | Success status ink (badges, checkmarks, status text). Jade overrides the inherited midnight `#61c073`: against a green accent the base value sat 1.062:1 away from `--accent` — a success badge indistinguishable from an accent chip (webux N1). `#2fb98e` holds AA ≥4.5:1 on every jade ground (6.05–8.03) while separating 1.168:1 / 21.3° hue from the accent; pinned by `TokenContrastTest` |
| **Success Background** | `rgba(47, 185, 142, 0.16)` | Status-chip tint behind `--success` text, re-hued to match the override (base rgba sat warm against the cool text) |

## Installation

A theme plugin only registers **while enabled** — a fresh install lands
*disabled*, so plan on both steps. There are two supported routes:

**Admin UI** — *Admin → Plugins* (`/app/admin/plugins`), find **Jade Theme**,
press **Install**, then flip its switch to enable it.

**CLI** — from the server root:

```bash
php bin/phlix plugin:install https://github.com/detain/phlix-plugin-jade-theme
php bin/phlix plugin:enable  phlix-plugin-jade-theme
```

Pass the **repository URL** to `plugin:install`: the server resolves it through
the plugin catalog (`plugins.json`), which pins the exact commit and tarball
sha256 it verifies before extracting. A bare plugin name is not something the
installer can download.

Once enabled, *Settings → Appearance* shows the Jade swatch next to the
built-ins; picking it applies live. If the swatch is missing, the plugin is
installed but not enabled — `php bin/phlix plugin:list` shows which state
it's in.

A note on the wrong route: `composer require detain/phlix-plugin-jade-theme`
does **not** install the theme. That is the library-dependency path, and the
server loads plugins from its own store (`var/plugins/`, populated by
`PluginLoader`), never from an application's `vendor/` tree. This repo's
`composer.json` exists so the catalog tooling and this suite can resolve the
package — running `composer install` inside a checkout is for development of
the theme itself (see *Development* below).

## Theme Details

- **Theme ID:** `jade`
- **Theme Name:** Jade
- **Extends:** `midnight`
- **Type:** UI Theme
- **Dark Mode:** Yes

## Requirements

- PHP 8.3+
- Phlix 0.44.0+

## Development

Install dependencies:

```bash
composer install
```

Run the test suite (`phpunit.xml`, bootstrapped by `tests/bootstrap.php`):

```bash
composer test
```

Run static analysis (`phpstan.neon`, level 9) and coding standards (`phpcs.xml`, PSR-12):

```bash
composer phpstan
composer phpcs
```

`tests/bootstrap.php` loads `dev-stubs/LifecycleInterface.php` and `dev-stubs/ThemeSourceInterface.php` only when the real host contracts are absent, so the suite runs outside a Phlix server checkout. `.github/workflows/test.yml` runs the same three checks on PHP 8.3 and 8.4.

## License

MIT License - see [LICENSE](LICENSE) file for details.
