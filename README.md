# Municipio Theme Extensions

Municipio Theme Extensions adds focused theme compatibility options that are
missing from modern Municipio. It does not add editorial workflows or business
functionality.

The plugin supports modern Municipio only. Municipio LTS is a migration source,
not a supported runtime.

## Installation

Install the package with Composer:

```console
composer require municipio/wp-plugin-municipio-theme-extensions
```

The package type is `wordpress-plugin`, and `extra.installer-name` ensures that
`composer/installers` places it in
`wp-content/plugins/municipio-theme-extensions`. Activate **Municipio Theme
Extensions** in WordPress after installation.

## Header settings

The first release adds two fields to Municipios existing **Header → Appearance**
section:

- header link color;
- header link size and weight.

The fields target links and icons shown directly in the header. Header trigger
buttons, drawer navigation, other menus, and Municipio markup remain owned by
Municipio. The configured color also applies to hover, focus, active, and
visited states so generic link colors cannot reduce header contrast.

The release also adds **Letter Spacing** as a subfield in Municipios existing
**Typography → Button** control. The value is stored with the other
`typography_button` choices and writes `--letter-spacing-button`, which a plugin
rule uses to override the fixed spacing on Municipios small buttons without
replacing its Blade markup. The default preserves Municipios current `.1rem`;
migrated LTS button typography uses `normal` because the former Municipio
Extended button template did not apply that spacing.

## Secondary menu behavior

The plugin adds **Start secondary menu at level two** to Municipios existing
**Menu → Behaviour** section. The setting is enabled by default and removes the
active level-one branch from the secondary sidebar navigation even when the
primary menu has no assigned items. Disabling it restores modern Municipios
current behavior for that configuration and displays the complete tree.

The option uses Municipios controller applicator and its cache. The runtime
default matches the field default, so existing installations receive the enabled
behavior without an activation migration or an initial Customizer save. Primary,
mobile, drawer, mega-menu, and breadcrumb data are not modified.

## Drawer palette

The plugin extends Municipios existing drawer controls with a **Light** choice
for both the main area and the optional secondary area. The choices retain
Municipios setting names, modifier output, markup, focus handling, and
responsive behavior. Their presentation uses the site's
`--color-background` token with dark text, links, and icons.

Neither choice is a new default. New Municipio sites therefore retain
Municipios standard drawer palette until an editor selects another value. LTS
migrations can explicitly select both light values through their migration
tooling without making plugin activation mutate site data.

## One Page classic content

The plugin restores the LTS-compatible **Display text content for One Page
template** option in Municipios existing **General settings** section. It reuses
the `municipio_customizer_onepage_body_text` theme mod, promotes the current One
Page title when enabled without overriding the static front page's page-level
title choice, and lets Municipios own template render filtered classic content
through the same content branch used for blocks. No page template or content is
copied or migrated.

## Activation migration

Activation runs a versioned, idempotent migration for the settings needed by the
first release:

- compatible color values are reused directly;
- legacy `header_color` tokens are mapped to explicit colors;
- the existing primary-navigation contrast color is used as a fallback;
- button font size and weight are copied to the new header-specific typography
  setting;
- reusable LTS button typography receives normal letter spacing without
  overwriting an explicitly configured value.

Existing target values are never overwritten. Legacy theme mods remain in the
database for troubleshooting and rollback, and the completed migration version
is stored in `municipio_theme_extensions_migration_version`.

## Development

Migration development and release cleanup follow the canonical policy in
[`docs/migrations.md`](docs/migrations.md). Unreleased migration iterations are
squashed into one schema version per package release; released migrations are
retained until their source versions are no longer supported.

```console
composer install
composer test
composer lint
```
