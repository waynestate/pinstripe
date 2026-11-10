# Pinstripe README art kit

Regenerates the README images under `docs/readme/` from this repo's own static
front page (`site/`), its brand files and its preset files. Nothing is drawn by
hand: the hero and the gallery are screenshots of the real page, the tape
measure is read from the page's seven pieces and from the presets themselves
(`pint/`, `phpstan/`, `rector/`, `eslint/`, `prettier/`), the cast is the marks
resized, and the badges read their facts from `composer.json` and
`phpstan/base.neon`.

## Run

Windows PowerShell and Linux/macOS sh, side by side:

| Windows PowerShell | Linux / macOS sh |
|---|---|
| `npm --prefix scripts/readme-art ci` | `npm --prefix scripts/readme-art ci` |
| `npx --prefix scripts/readme-art playwright install chromium` | `npx --prefix scripts/readme-art playwright install chromium` |
| `node scripts/readme-art/build.mjs` | `node scripts/readme-art/build.mjs` |

The script resolves every path from its own location, so it works from any
folder on any OS. It opens `site/index.html` as a `file://` URL (no server, no
network) and is deterministic: same inputs, same outputs.

## What it makes

- `docs/readme/hero.jpg`: the hero, with the site header and tape hidden.
- `docs/readme/tape.svg`: a tailor's tape measure that unrolls, one mark per
  preset, labeled with the real values (Pint presets, PHPStan level, Rector
  sets, ESLint flavours, Prettier settings). Under `prefers-reduced-motion` it
  stays unrolled.
- `docs/readme/gallery.jpg`: a contact sheet of three mobile crops.
- `docs/readme/board.jpg`: the brand board, re-encoded.
- `docs/readme/cast/*.png`: one 96x96 mark per stack part.
- `docs/readme/badges/*.svg`: `php`, `license` and `phpstan`, made locally by
  `badge-maker`.

Every file must stay at or under 300 KB, and no captured text may mention an
internal host; the script exits 1 otherwise.

## Check

`node scripts/readme-art/build.mjs --check` builds into a fresh temp folder
(writing nothing into the repo) and compares it with the committed
`docs/readme/`: SVGs byte for byte, images by pixels within a small tolerance
(the CI runner is arm64 and a developer box may be amd64, and Chromium and
libvips round a little differently per architecture), plus any file missing
from or extra in `docs/readme/`. It names every problem, then exits 1 when
anything is out of date and 0 when all files match. CI runs it in the
`readme-art` job, so a stale image fails the pipeline instead of drifting.

Windows PowerShell and Linux/macOS sh, side by side:

| Windows PowerShell | Linux / macOS sh |
|---|---|
| `npm --prefix scripts/readme-art ci` | `npm --prefix scripts/readme-art ci` |
| `node scripts/readme-art/build.mjs --check` | `node scripts/readme-art/build.mjs --check` |

If a number on the tape is ever wrong, the preset file is wrong, not the tape.
Measure twice, cut once.
