# CLAUDE.md — Digital License Manager (Free)

## Release Checklist

When bumping the plugin version, update **all four** of these in the same commit. CI will fail the release if any of them drifts from the git tag.

1. `digital-license-manager.php` — the `Version:` header comment
2. `digital-license-manager.php` — the `DLM_PLUGIN_VERSION` constant
3. `readme.txt` — the `Stable tag:` line
4. `composer.json` — the `"version"` field (must match the full tag, including any pre-release suffix)

Then tag the release as `v<version>` (e.g. `v2.0.1`, `v2.1.0-beta.1`).

Pre-release suffixes allowed: `-test`, `-alpha[.N]`, `-beta[.N]`, `-rc[.N]`. The tag's base version (everything before the suffix) is what the PHP header, the version constant, and `readme.txt` must match; `composer.json`'s `version` must match the full tag verbatim.

## Why composer.json is in the checklist

The plugin is distributed via a private Composer repository in addition to WordPress.org. The release ZIP ships `composer.json` at the root, and its `version` field is the version consumers see via `composer show` / `composer require`. It must match the tag.
