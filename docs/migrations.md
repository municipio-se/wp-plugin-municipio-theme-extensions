# Migration lifecycle

This document is the canonical migration policy for Municipio Theme
Extensions. A tagged Composer package release is the boundary between
rewritable development history and immutable released migration history.

## Release versions

- Use at most one new migration schema version per package release.
- Increment the schema version once when preparing a release if that release
  changes persisted data. A release without data migrations does not increment
  it.
- All migration work targeting the same unreleased package version shares that
  schema version.

## Before release

Migration history is rewritable until its package version has been tagged.

- Squash intermediate fields, data shapes, compatibility bridges, schema
  versions, and tests into the direct path from every supported released source
  state to the intended release state.
- Remove superseded development-only migration code instead of leaving comments
  that ask a later release step to remove it.
- Clean development databases that executed discarded intermediate migrations,
  then rerun the squashed migration from a supported source state.
- Do not carry temporary development keys into a release merely because a local
  test site used them.

## After release

Released migrations are immutable compatibility contracts because sites may
skip package releases.

- Do not rewrite or remove a released migration merely because a newer release
  supersedes its target shape.
- New migration work receives the next release's single schema version and must
  support every still-supported released source state.
- Retire released migration code only when the minimum supported installed
  package version has passed it and deployment inventory confirms that no
  managed site still depends on it.
- Treat deletion of migrated source data as a separate, explicit decision. A
  completed migration does not by itself authorize destructive cleanup.

## Implementation requirements

Every migration must be:

- idempotent;
- safe when source values are absent or malformed;
- non-destructive to explicitly configured target values;
- explicit about which source values are preserved;
- documented near the code when its purpose, release boundary, or retirement
  condition is not obvious.

Comments should explain why released compatibility code still exists and when
it may be retired. Unreleased superseded code should be deleted during the
squash rather than annotated as temporary.

## Test matrix

Before release, automated tests must cover:

- a fresh installation without source values;
- each supported released source state;
- direct reuse and mapped legacy values;
- existing target values that must not be overwritten;
- repeated execution;
- the final squashed path, without development-only intermediate states.

When a released migration is retired, remove its obsolete upgrade tests in the
same change while retaining coverage for every remaining supported source
state.

## Release checklist

- [ ] Migration work is squashed to the direct release path.
- [ ] The schema version was incremented at most once for this release.
- [ ] No development-only keys or compatibility bridges remain.
- [ ] Fresh-install and supported-upgrade tests pass.
- [ ] Existing targets and preserved source values are covered by tests.
- [ ] Released migration comments identify their release and retirement
      condition where needed.
- [ ] Any retired migration is justified by the supported-version policy and
      deployment inventory.

