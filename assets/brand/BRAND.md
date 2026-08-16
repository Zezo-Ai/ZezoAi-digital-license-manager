# Digital License Manager brand system

The DLM mark is a horizontal key whose bow forms a `D`. It replaces the legacy
long-shadow key and the generic padlock while preserving the product's licensing
meaning.

## Palette

| Role | Color |
|---|---|
| DLM Teal | `#147D82` |
| Deep Teal | `#0F6266` |
| Aqua | `#73C0BD` |
| Mist | `#EAF6F5` |
| Ink | `#102A2C` |
| White | `#FFFFFF` |

Use the primary teal mark on light backgrounds and the reversed white mark on
dark or teal backgrounds. The app icon always uses the white mark on the DLM
Teal tile. Do not add gradients, drop shadows, outlines, or alternate colors to
the mark.

## Spacing and size

- Keep clear space around the mark equal to the height of one key tooth.
- Do not render the standalone mark below 16 px wide.
- Do not render the horizontal lockup below 160 px wide.
- Preserve the SVG aspect ratio; never stretch, rotate, or crop the mark.
- Use the mark without the wordmark in compact admin and favicon contexts.

## Free and Pro

Free and Pro share the same mark. Distinguish Pro with the separate `PRO` badge
shown in `dlm-lockup-pro.svg`; never alter the mark or assign Pro another color.

## Files and regeneration

- `dlm-mark*.svg` — standalone primary, ink, and reversed marks.
- `dlm-icon.svg` — rounded-square app and directory icon.
- `dlm-lockup*.svg` — outlined IBM Plex Sans lockups with no font dependency.
- `dlm-wordpress-banner.svg` — editable WordPress.org banner source.

Run `bun run build:brand` from the plugin root to regenerate the runtime mark,
WordPress.org SVG/PNG icons, and both current WordPress.org banner sizes. The
command requires ImageMagick's `convert`. The committed `source/*.svg` files
contain the outlined IBM Plex Sans wordmark, badge, and banner message; use
`pango-view` with IBM Plex Sans only when changing that copy. Use Playwright CLI
to review the outputs at their actual WordPress and admin sizes.
