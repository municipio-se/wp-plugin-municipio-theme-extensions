# Repository instructions

## Migrations

Read [`docs/migrations.md`](docs/migrations.md) before changing migration code
or preparing a release. That document is the canonical migration policy.

- Use at most one migration schema version per package release.
- Squash all unreleased migration iterations into the direct release path.
- Delete unreleased intermediate keys, bridges, versions, and tests before the
  release is tagged.
- Never rewrite a released migration while its source version remains
  supported.
- Keep migrations idempotent, preserve explicit targets, and verify fresh and
  supported upgrade paths.

## Verification

Run these commands after changing PHP or migration behavior:

```console
composer format
composer test
composer lint
```

