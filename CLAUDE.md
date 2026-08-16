# CLAUDE.md — Digital License Manager (Free)

## Release modes & version bump checklist

Every release flows through a single workflow (`.github/workflows/release.yml`) but behaves differently based on the tag's pre-release suffix. CI fails the release if the tag and source files disagree.

### Stable — e.g. `v2.0.0`, `v2.1.3`

Bumps go in **four** places:

1. `digital-license-manager.php` — `Version:` header comment
2. `digital-license-manager.php` — `DLM_PLUGIN_VERSION` constant
3. `readme.txt` — `Stable tag:` line
4. `composer.json` — `"version"` field

Deploys to WordPress.org via the standard 10up action: rewrites `trunk/` and creates `tags/<version>/`. Also uploads a ZIP to the GitHub Release.

**Stable-tag blind spot:** the CI check extracts only `X.Y.Z` from `Stable tag`, so a stale
`2.0.0-rc.N` value *passes* validation on a `v2.0.0` tag — and WordPress.org then advertises the
RC as the stable version. Verify the `Stable tag` bump by hand; CI will not catch it.

**Changelog:** before a stable tag, write a real `== Changelog ==` entry in `readme.txt` with the
actual release date, and update `== Upgrade Notice ==` for major versions. Pre-releases (RC/beta)
do not get changelog entries. CI does not check any of this.

### Release Candidate — e.g. `v2.0.0-rc.1`, `v2.0.0-rc.2`

Bumps go in **three** places — `readme.txt`'s `Stable tag` is NOT touched:

1. `digital-license-manager.php` — `Version:` header comment
2. `digital-license-manager.php` — `DLM_PLUGIN_VERSION` constant
3. `composer.json` — `"version"` field (must match the full tag, including the `-rc.N` suffix)

Deploys to WordPress.org SVN as **tag-only**: creates `tags/<version>/` but leaves `trunk/` and the advertised `Stable tag` alone. Existing users are NOT auto-updated. RC is available to users who go to Advanced View → Previous Versions, or hit `https://downloads.wordpress.org/plugin/digital-license-manager.<version>.zip` directly.

### Beta / Alpha / Test — e.g. `v2.0.0-beta.15`, `v2.0.0-alpha.3`, `v2.0.0-test`

Same three-place bump as RC. **No** WordPress.org deployment (skipped in the eligibility step). GitHub Release only, marked as pre-release.

### Base-version matching rule

The CI consistency check compares the tag's base `X.Y.Z` (suffix stripped) against the `X.Y.Z` portion extracted from the PHP header, `DLM_PLUGIN_VERSION`, and `readme.txt`'s `Stable tag`. The pre-release suffix on those is allowed to drift (it's what makes RC/beta tag-only flows possible). `composer.json`'s `version` is compared against the **full** tag verbatim, including suffix.

## Why composer.json is in every checklist

The plugin is published to a private Composer repository in addition to WordPress.org. The release ZIP ships `composer.json` at the root, and its `version` is what consumers see via `composer show` / `composer require`. It must match the tag.
