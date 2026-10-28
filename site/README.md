# Pinstripe front page

A tailor's shop: pattern paper on the cutting table, a swatch of green pinstripe cloth with the mark sewn on as
its label, a yellow measuring tape, pins in a cushion, a fitting-room mirror, paper pattern pieces and a wardrobe
rail. Static HTML, CSS and a small vanilla script. No build step and no network calls, so it deploys as plain
files anywhere.

- type: **Bodoni Moda** 400/600/800 and italic 400/600 (SIL OFL 1.1, `assets/fonts/OFL-bodoni-moda.txt`) for
  display, the label in a good jacket; **Reenie Beanie** (SIL OFL 1.1, `assets/fonts/OFL-reenie-beanie.txt`) for
  the tailor's chalk notes; Lato from WayneUI for reading. Both are self-hosted in `assets/fonts/` from
  `@fontsource`.
- colours: WSU green `#0C5449` and gold `#FFCC33`, cream `#F4F1EA` and the site-only mauve `#8B6372` from the
  brand board. The deep green, the cloth green, the cutting-mat tan and the deep mauve for text (`#6E4A58`, 4.5:1
  or better on cream) are this page's own.
- built on WayneUI 2.0.8, copied into `assets/wayne-ui/` from its documented static path (tokens, Lato,
  container, buttons, skip link, sr-only, focus). Other parts' marks are copies in `assets/marks/`; WayneUI uses
  its own mark on green, and Pinstripe's mark comes from `brand/` untouched.

## Files

| Path | What |
|---|---|
| `index.html` | The page |
| `assets/pinstripe.css` | Everything on top of WayneUI; motion lives at the end, under `.motion` only |
| `assets/pinstripe.js` | The tape, the live fitting, the cushion pins, the copy button, reveals |
| `brand/` | The mark, favicons and board (see `brand/README.md`) |
| `screenshots/` | Desktop, mobile, reduced motion, fitting stills, a motion GIF, and mobile crops per section |

## Run it locally

Any static server works, on any OS:

```
php -S 127.0.0.1:8080 -t site
```

## The magic

**The fitting.** Scroll into it and the stage holds still while messy code (cut from Pinstripe's own fixtures:
`raw-messy`, `rector-old`, `raw-nullbug`) goes through the shop. The tailor chalks five alterations onto the
cloth one at a time, numbered to match the ticket beside it: Rector promotes the property and adds `: string`,
Pint moves the brace, Rector shortens `array()`, and PHPStan level 8 underlines the null. Then the iron passes
left to right, steaming, and the code comes out pressed. The pinstripes are the indent guides: crooked on the
way in, every line a little off the grain; straight and gold once pressed.

Also: the measuring tape across the top unrolls as you read and reads off a PHPStan level (8 is chalked on the
tape as our fit; 9 is snug, 10 can't breathe). The hero cloth is ruled stripe by stripe, the label is stitched
on, two pins go in and the hang tag swings. Point at a piece on the order card and its pin lifts out of the
cushion; pick up a pattern piece and a pin pops in. Type `measure` anywhere.

## Motion and access

- With `prefers-reduced-motion: reduce` the page is static and complete at first paint: no tape, both garments
  hang side by side with every chalk mark already made, the ticket is fully ticked, nothing moves.
- The live fitting also needs a tall enough window (640 px tall on wide screens, 700 px on narrow ones);
  otherwise it falls back to the same side-by-side layout.
- Chalk numbers are `aria-hidden`; the figure has a caption that says what changed, and the ticket is plain text.
- Nothing flashes. The steam and the tag sway are slow.
- No sideways scroll at 1440, 1024, 800, 390 and 320 px, including mid-fitting. The code on the cloth scales
  with its container, so the longest line always fits; the pattern-piece snippets scroll inside their own box.

## Links between the stack sites

Links to the other parts are committed as production URLs (`https://<project>.apps.wayne.edu/`), so crawlers and
visitors without JavaScript always get production. `assets/stack-env.js` (the same file on every stack site) rewrites
them to `http://127.0.0.1:18432/<project>/site/` in development, for a static server whose root is the
`the stack` folder. Dev is picked automatically on `localhost` or `127.0.0.1`; `?env=dev` or `?env=production` forces
one (remembered until `?env=auto`), `<meta name="stack-env">` sets the page default, and `?devBase=http://127.0.0.1:<port>`
points at a server on another port. WayneUI links stay on production.
