# Pinstripe brand files

**The mark is a P built from pinstripes**: a stem of three parallel stripes, and a bowl of three nested D-shaped stripes
that run out of the stem and wrap round a gold dot, the pin. Every stripe is the same width and every gap is the same
gap, which is the whole pitch: sharp, consistent, and everyone can tell. The stripes are also the formatted lines of code.

Pinstripe is to code what a good suit is to a job interview: nobody notices it, everybody notices when it's missing.

## Colours
| Role | Colour |
|---|---|
| Stripes on a light background, tile | Wayne State green `#0C5449` |
| The pin (the only gold) | Wayne State gold `#FFCC33` |
| Stripes on the tile or on dark backgrounds | White `#FFFFFF` |
| Site accent only, never in the mark | to be chosen from WayneUI's palette |

Gold never sits behind white text.

## Files
| File | Use |
|---|---|
| `pinstripe-mark.svg` | The mark on light backgrounds: green stripes, gold pin |
| `pinstripe-mark-white.svg` | The mark on dark or green backgrounds: white stripes, gold pin |
| `pinstripe-mark-mono.svg` | One colour (`currentColor`), for print and single-ink use |
| `pinstripe-mark-animated.svg` | Front-page motion: crooked stripes snap straight, then the pin drops in. Stripes follow light and dark; `prefers-reduced-motion` turns the animation off, and the drawing underneath is the finished mark |
| `pinstripe-tile.svg` | The app icon: a green rounded tile with the white P |
| `pinstripe-favicon.svg` | The 32 px favicon, hand-hinted on the pixel grid. Two stem stripes, two bowl stripes and a 4 by 4 gold pin. The 64 px PNG is this drawing at 2 times |
| `pinstripe-favicon-16.svg` | Hand-hinted on the 16 px grid. One unit is one pixel. Two stem stripes, one square-cornered bowl stripe, and a 3 by 3 gold pin |
| `pinstripe-avatar-512.png` | The GitLab project avatar and the Open Graph image base |
| `pinstripe-mark-1024.png`, `pinstripe-mark-white-1024.png` | Raster marks for slides and documents |
| `favicon-16.png`, `favicon-32.png`, `favicon-64.png`, `favicon.ico` | Browser favicons. The `.ico` holds 16, 32 and 48 px |

## Geometry
Drawn from exact geometry in the 512 unit tile space, not traced. The main drawing: stripes 14 wide with 13 gaps, a
320 tall stem, and a bowl of radius 108 whose three stripes share one centre (outer radii 108, 81 and 54). A flat run of
28 units leads into each semicircle, and the stripes start 2 units inside the stem so no seam shows. The pin has radius
27, one gap clear of the inner stripe. The small favicons are redrawn, not shrunk. At 16 px there are three stripes in all and a 3 by 3 pin. At 32 px
there are four stripes and a 4 by 4 pin. Below about 40 px, more stripes merge into a block.

Rules from the earlier drafts. No jackets, hangers or ties (they read as stock clip art). No lone V (it reads as Vue, a
letter, or a figure with its arms up). The stem stays at two or three stripes and never becomes a barcode.

## Motion
The front-page animation is the mark being formatted: each stripe starts crooked and offset, snaps straight in turn
(stem first, then the bowl from the outside in), and the gold pin lands last. It is a CSS animation inside the SVG
(about 1.4 seconds, plays once). With `prefers-reduced-motion: reduce` there is no animation and the page shows the
static mark.

## Rendering
The PNGs were rendered with Inkscape, for example
`inkscape pinstripe-tile.svg --export-type=png --export-filename=pinstripe-avatar-512.png -w 512 -h 512`. The favicons were rendered at native size from their SVGs with headless Chrome. The `.ico` was
built with Pillow from the 16, 32 and 48 px renders.

## The board
`pinstripe-board.png` is the 12-panel brand board. The mark panels show the true SVG mark, cut to measure and never
let out at the hem.
