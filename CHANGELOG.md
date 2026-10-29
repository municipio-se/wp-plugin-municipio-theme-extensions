# Changelog

## 1.1.0-rc.4 — unpublished candidate

Apply the theme's navigation list reset to both fallback and loaded trees,
removing prose-list spacing and the inset hover background. Move mobile loading
feedback to a reserved header slot using the Material Symbols progress_activity
icon, with a screen-reader status and reduced-motion support.

## 1.1.0-rc.3 — unpublished candidate

Restore the theme drawer width, link spacing, right-aligned close control with
its native icon, and configured secondary-menu dividers. Enlarge expansion
symbols while preserving the tree's keyboard and native dialog behavior.

## 1.1.0-rc.2 — unpublished candidate

Fix the native mobile dialog remaining off-screen after opening because
Municipio's drawer transform had higher CSS specificity. Disable the inherited
drawer transition so native dialog visibility takes effect immediately.

## 1.1.0-rc.1 — unpublished candidate

Adds independently enabled mobile and sidebar tree navigation with keyboard
traversal, retained expansion state, a native mobile dialog and asynchronous
menu loading. Server-rendered links remain available without JavaScript or
after a failed request. Existing installations retain their current navigation
unless explicitly enabled. No migration or schema change is included.

PHP, DOM and local Municipio rendering tests pass. Firefox/VoiceOver acceptance,
visual verification and production rollout remain pending; this candidate does
not establish support acceptance or change new-site defaults.

## 1.0.0 — 2026-09-17

First stable release with independent semantic versioning. Preserves the
documented theme settings, layout behavior and migration path from development
commit dac5ead. Plugin and asset versions are aligned; runtime functionality and
migration schema 2 are unchanged.

Nora accepted the existing editor behavior. Persisted migration fixtures,
idempotence and SQL restoration passed in a disposable WordPress multisite. See
RELEASING.md for the supported reference scope and recovery requirements.
Publication and Composer distribution checks passed on 2026-09-17.

Documentation status corrected after publication. The immutable 1.0.0 archive
retains the original candidate wording; runtime code and the tag are unchanged.
