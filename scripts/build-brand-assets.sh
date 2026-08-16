#!/usr/bin/env bash

set -euo pipefail

script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
plugin_root="$(dirname "$script_dir")"

command -v convert >/dev/null 2>&1 || {
    echo "ImageMagick's convert command is required." >&2
    exit 1
}
node "$script_dir/build-brand-assets.mjs"

convert -background none "$plugin_root/assets/brand/dlm-icon.svg" \
    -resize 128x128! -colorspace sRGB -strip -depth 8 \
    "PNG32:$plugin_root/.wordpress-org/icon-128x128.png"

convert -background none "$plugin_root/assets/brand/dlm-icon.svg" \
    -resize 256x256! -colorspace sRGB -strip -depth 8 \
    "PNG32:$plugin_root/.wordpress-org/icon-256x256.png"

convert -background none "$plugin_root/assets/brand/dlm-wordpress-banner.svg" \
    -resize 1544x500! -colorspace sRGB -strip -depth 8 \
    "PNG24:$plugin_root/.wordpress-org/banner-1544x500.png"

convert -background none "$plugin_root/assets/brand/dlm-wordpress-banner.svg" \
    -resize 772x250! -colorspace sRGB -strip -depth 8 \
    "PNG24:$plugin_root/.wordpress-org/banner-772x250.png"
