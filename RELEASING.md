# Release process

## First stable release

The prepared first candidate is 1.0.0 with independent SemVer. It is unreleased
until explicitly approved and published. Composer must derive its version from
the immutable tag; do not add a version field to composer.json.

The canonical [migration policy](docs/migrations.md) applies before every
release. The stable contract covers documented settings, preserved data,
extension points and behavior. Incompatible changes require a major version.
Compatible additions and fixes use minor and patch versions respectively.

## Compatibility evidence

The primary integration target is WordPress 6.9.4, PHP 8.3 and Municipio theme
6.43.3 with its bundled Modularity. Individual features have narrower contracts
and may require upstream hooks or patches documented in README. A permissive
runtime version check is not proof that every permitted version was tested.
Record the exact dependency combination and patch set for each reference.

The existing isolated suite passed on PHP 8.3.33 on 2026-09-14: 72 tests and 209
assertions. Composer validation and lint passed with existing diagnostics. This
does not establish reference acceptance of the release.

## Gates before 1.0.0

- Inventory migration markers and relevant source/target values per supported
  installation and multisite blog. The current activation schema is 2. A
  development installation already marked 2 skips ActivationMigration, so a
  changed migration cannot be validated merely by activating it there again.
- Confirm that unreleased migrations implement the direct path to the final
  format, with at most one new schema version for the release. Preserve explicit
  target settings and legacy values. Do not reset deployed markers or erase data
  to make a test pass.
- Verify fresh installs, supported earlier states, existing schema-2 states,
  malformed or absent source data, preserved targets and repeated execution.
  Document the recovery procedure for both code and data.
- Bind reference acceptance to the exact runtime delta. Nora accepted Split and
  Full in all three content areas, save/reload, frontend and placement guards on
  2026-09-16. Other module types and placement combinations are explicitly
  accepted support limitations, not verified cases or a new release blocker.
- Reconcile changelog, plugin header and release notes only after the above
  gates pass. Run composer validate --strict, composer format, composer test and
  composer lint; review any formatting changes.

## Candidate evidence, 2026-09-17

Runtime code remains identical to deployed dac5ead except for the plugin and
asset version. No migration code or schema changed. Schema 2 is the direct path
from absent/older markers; already deployed schema-2 sites remain no-ops.

An isolated WordPress 6.9.4 multisite on PHP 8.3.33 passed eight persisted-data
fixtures: fresh, direct/mapped legacy values, explicit targets, malformed
sources, schema 1, deployed schema 2 and later imported card data. Migrations
were repeated, blogs switched in reverse order, the pre-migration SQL export
restored and compared with every fixture, then migration rerun successfully.
This verifies the option-data recovery procedure, not a full customer disaster
recovery drill. The reusable test is scripts/verify-package-migrations.php in
Municipio Cloud Tooling. No customer database or deployed marker was changed.

Before a data-changing deployment, pause writes and retain a consistent SQL
export alongside the previous code and lockfile. Roll back code first with
plugins/themes skipped in WP-CLI, restore the matching database export, verify
theme_mods and municipio_theme_extensions_migration_version per blog, then
resume requests. Restoring an old database after new content is accepted needs a
separate reconciliation decision; it must not silently discard edits.

## Publication and rollback

Obtain approval for the exact release commit and compatibility evidence. Publish
an annotated 1.0.0 tag and a matching GitHub release. Verify Packagist indexing,
the source/dist references against the peeled tag commit and a clean Composer
installation. Never move a public tag; publish a new version for corrections.

Approved consumers use ^1.0 with a reviewed and committed lockfile. Deployment
and functional verification remain separate from package publication. Before
deploying, retain the previous code/lockfile and a tested data recovery point.
Returning to an older lockfile alone does not undo a migration. Deleting legacy
values requires a separate explicit decision.
