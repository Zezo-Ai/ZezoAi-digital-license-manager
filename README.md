# Digital License Manager

This is development repository of Digital License Manager. 

If you want to download the latest release, please do that from the [WordPress.org page](https://wordpress.org/plugins/digital-license-manager/).


## Installation

The plugin works with composer, to install it run:

```
composer require gdarko/digital-license-manager
```

## Documentation

Need help? Please consult our [documentation](https://docs.codeverve.com/digital-license-manager/). or open issue on this repository.

## Development

### Release Workflow

Releases are automated via GitHub Actions. When you push a version tag, the workflow:

1. Validates version consistency (PHP header, constant, readme.txt)
2. Builds all assets (composer, Gutenberg blocks via bun)
3. Creates a distribution zip (using `.distignore`)
4. Deploys to WordPress.org (stable and RC tags only)
5. Creates a GitHub Release

#### Creating a Release

```bash
# Stable release (deploys to WordPress.org)
git tag v1.8.5
git push origin v1.8.5

# Release candidate (deploys to WordPress.org)
git tag v1.8.5-rc1
git push origin v1.8.5-rc1

# Pre-release (GitHub only, skips WordPress.org)
git tag v1.8.5-beta
git push origin v1.8.5-beta
```

#### Version Tag Patterns

| Pattern | Example | WordPress.org | GitHub Release |
|---------|---------|---------------|----------------|
| `vX.Y.Z` | `v1.8.5` | Yes | Stable |
| `vX.Y.Z-rcN` | `v1.8.5-rc1` | Yes | Pre-release |
| `vX.Y.Z-alpha` | `v1.8.5-alpha` | **No** | Pre-release |
| `vX.Y.Z-beta` | `v1.8.5-beta` | **No** | Pre-release |
| `vX.Y.Z-test` | `v1.8.5-test` | **No** | Pre-release |

### Scripts

#### Cleaning up TCPDF fonts

To reduce the vendors directory size (used by GitHub workflow):

```bash
bash scripts/clean_up.sh
```

## Contribution

Feel free to contribute I will review any issues and pull requests.

Please check the above "**Development**" section to get started.

## License

```
This file comes from the "Digital License Manager" WordPress plugin.
https://darkog.com/p/digital-license-manager/

Copyright (C) 2020-present  Darko Gjorgjijoski. All Rights Reserved.

Digital License Manager is free software; you can redistribute it
and/or modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

Digital License Manager program is distributed in the hope that it
will be useful,but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program;

If not, see: https://www.gnu.org/licenses/licenses.html

Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
```

