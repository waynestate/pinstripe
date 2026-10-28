#!/usr/bin/env node
/* ============================================================================
   Pinstripe README art kit.

   Renders the README images from this repo's own static front page (site/),
   its brand files and its preset files, and writes them under docs/readme/.
   Nothing is drawn by hand: the hero and the gallery are screenshots of the
   real page, the tape measure is built from the page's own "pieces" list and
   from the preset files themselves (pint/, phpstan/, rector/, eslint/,
   prettier/), the cast is the marks resized, and the badges read their facts
   from composer.json and phpstan/base.neon. Measure twice, cut once.

   Run from any folder (every path resolves from this file's own location):

     npm --prefix scripts/readme-art ci
     npx --prefix scripts/readme-art playwright install chromium
     node scripts/readme-art/build.mjs

   The page is opened as a file:// URL: no server, no network.
   ========================================================================== */

import { chromium } from "playwright";
import { makeBadge } from "badge-maker";
import sharp from "sharp";
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from "node:fs";
import path from "node:path";
import { fileURLToPath, pathToFileURL } from "node:url";

/* ------------------------------------------------------------- paths, limits */

const scriptDir = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(scriptDir, "..", "..");
const siteDir = path.join(repoRoot, "site");
const outDir = path.join(repoRoot, "docs", "readme");
const castDir = path.join(outDir, "cast");

const MAX_BYTES = 300 * 1024; // every file in docs/readme/ stays at or under this
const FORBIDDEN = /noug[a]t|snu[.]gs|citadel/i; // internal names never ship in art

const GREEN = "#0C5449";
const GREEN_DEEP = "#073A32";
const CLOTH = "#0E5F53";
const GOLD = "#FFCC33";
const GOLD_SOFT = "#FFE08A";
const CREAM = "#F4F1EA";

const outputs = [];

function record(rel, buf) {
  const abs = path.join(outDir, rel);
  mkdirSync(path.dirname(abs), { recursive: true });
  writeFileSync(abs, buf);
  outputs.push({ rel, bytes: buf.length });
  console.log(`  ${rel}  ${buf.length} bytes`);
}

function fail(msg) {
  console.error(`FAIL: ${msg}`);
  process.exit(1);
}

const readText = (...p) => readFileSync(path.join(repoRoot, ...p), "utf8");
const readJson = (...p) => JSON.parse(readText(...p));

/* ------------------------------------------------------------- 1. cast strip */

// One 96x96 PNG per stack part (shown at 48 px in the README). The marks are
// copied and resized only, never redrawn. WayneUI is composed instead: its
// reverse mark (white frame, gold tab) set on a Wayne State green tile.
const CAST = [
  "creed",
  "proctor",
  "sesame",
  "helmet",
  "sourdough",
  "squire",
  "preflight",
  "pinstripe",
  "booster",
  "rollcall",
  "drawbridge",
  "advisor",
  "weld",
];

function castSource(name) {
  if (name === "pinstripe") return path.join(siteDir, "brand", "pinstripe-avatar-256.png");
  return path.join(siteDir, "assets", "marks", `${name}.png`);
}

// The WayneUI tile: the reverse mark placed unchanged (no edits to any d=
// value, fill, or the mark's own viewBox) on a green rounded tile. The visible
// ink spans 1546 x 1551 of the 1980 x 1989 viewBox and is centred in it, so
// this scale makes the mark 352 px tall on the 512 tile (about 69%).
function wayneuiTileSvg() {
  const raw = readFileSync(path.join(scriptDir, "src", "wayneui-mark-reverse.svg"), "utf8");
  const open = raw.indexOf("<svg");
  const openEnd = raw.indexOf(">", open) + 1;
  const close = raw.lastIndexOf("</svg>");
  if (open < 0 || openEnd < 2 || close < openEnd) fail("wayneui-mark-reverse.svg: no <svg> element");
  // Everything between the opening tag and the final </svg>, verbatim.
  const inner = raw.slice(openEnd, close).replace(/^\r?\n/, "");
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512" role="img" aria-label="WayneUI">
  <rect width="512" height="512" rx="80" ry="80" fill="#0C5449"/>
  <svg x="31.3" y="30.3" width="449.4" height="451.4" viewBox="0 0 1980 1989">
${inner}</svg>
</svg>
`;
}

async function buildCast() {
  console.log("cast:");
  const tile = Buffer.from(wayneuiTileSvg(), "utf8");
  record("cast/wayneui.svg", tile);
  const tilePng = await sharp(tile, { density: 300 })
    .resize(96, 96, { fit: "contain", background: { r: 0, g: 0, b: 0, alpha: 0 } })
    .png({ palette: true, quality: 85, compressionLevel: 9 })
    .toBuffer();
  record("cast/wayneui.png", tilePng);
  const stale = path.join(castDir, "wayneui-dark.png");
  if (existsSync(stale)) rmSync(stale);
  for (const name of CAST) {
    const src = castSource(name);
    if (!existsSync(src)) {
      console.log(`  skip cast/${name}.png: missing source ${path.relative(repoRoot, src)}`);
      continue;
    }
    const png = await sharp(readFileSync(src))
      .resize(96, 96, { fit: "contain", background: { r: 0, g: 0, b: 0, alpha: 0 } })
      .png({ palette: true, quality: 85, compressionLevel: 9 })
      .toBuffer();
    record(`cast/${name}.png`, png);
  }
}

/* ---------------------------------------------------------------- 2. badges */

// Facts are read from the repo, never typed: a badge that lies is worse than
// no badge at all.
function buildBadges() {
  console.log("badges:");
  const composer = readJson("composer.json");
  const level = /^\s*level:\s*(\d+)/m.exec(readText("phpstan", "base.neon"));
  if (!level) fail("phpstan/base.neon: no level");
  const defs = [
    { key: "php", label: "php", message: composer.require.php },
    { key: "license", label: "licence", message: composer.license },
    { key: "phpstan", label: "phpstan", message: `level ${level[1]}` },
  ];
  for (const d of defs) {
    if (!d.message) fail(`no fact to badge for "${d.key}"`);
    record(
      `badges/${d.key}.svg`,
      makeBadge({ label: d.label, message: d.message, labelColor: GREEN, color: GOLD, style: "flat-square" }),
    );
  }
}

/* ------------------------------------------------------------- 3. the page */

async function openPage(browser) {
  const context = await browser.newContext({
    viewport: { width: 1280, height: 900 },
    deviceScaleFactor: 2,
    reducedMotion: "reduce", // the finished fitting: everything cut, chalked and pressed
  });
  const page = await context.newPage();
  await page.goto(pathToFileURL(path.join(siteDir, "index.html")).href, { waitUntil: "networkidle" });
  await page.evaluate(() => document.fonts.ready);
  return { context, page };
}

async function assertClean(page, selector, what) {
  const text = await page.locator(selector).innerText();
  if (FORBIDDEN.test(text)) fail(`forbidden text matched ${FORBIDDEN} in ${what}`);
}

/* ------------------------------------------------------------------ 4. hero */

async function buildHero(page) {
  console.log("hero:");
  await page.addStyleTag({ content: ".tape, .site-header { display: none !important; }" });
  await assertClean(page, ".hero", "hero");
  const png = await page.locator(".hero").screenshot();
  // Trim the last 4 device pixels: the hero's bottom edge is the next section's border.
  const { width, height } = await sharp(png).metadata();
  const trimmed = await sharp(png).extract({ left: 0, top: 0, width, height: height - 4 }).toBuffer();
  const jpg = await sharp(trimmed).resize({ width: 1280 }).jpeg({ quality: 82, mozjpeg: true }).toBuffer();
  record("hero.jpg", jpg);
}

/* ------------------------------------------------------ 5. the tape measure */

function esc(s) {
  return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

// What each piece of the suit is made of, read from the shipped preset files.
function presetFacts() {
  const base = readText("phpstan", "base.neon");
  const level = /^\s*level:\s*(\d+)/m.exec(base)?.[1];
  const needsComment = /reportIgnoresWithoutComments:\s*true/.test(base);
  const pint = ["per", "laravel"].map((n) => readJson("pint", `${n}.json`).preset);
  const composer = readJson("composer.json");
  const larastan = composer["require-dev"]["larastan/larastan"];
  const rectorSrc = readText("rector", "base.php");
  const sets = /^\s*->withPreparedSets\(([^)]*)\)/m.exec(rectorSrc)?.[1] ?? "";
  const rectorSets = [...sets.matchAll(/(\w+):\s*true/g)].map((m) => m[1]);
  const pkg = readJson("package.json");
  const eslintFlavours = Object.keys(pkg.exports)
    .filter((k) => k.startsWith("./eslint/"))
    .map((k) => k.slice("./eslint/".length));
  const a11y = /vuejs-accessibility/.test(readText("eslint", "javascript.js"));
  const pr = readJson("prettier", "index.json");
  if (!level || !pint.every(Boolean) || !larastan || !rectorSets.length || !eslintFlavours.length) {
    fail("a preset file no longer says what the tape expects (level, pint, larastan, rector sets, eslint)");
  }
  return { level, needsComment, pint, larastan, rectorSets, eslintFlavours, a11y, pr };
}

// Values per piece, in the order of the page's seven pieces. The names, tools
// and statuses come from the page; the values come from the preset files.
function tapeLines(f, row, i) {
  switch (i) {
    case 0:
      return f.pint.map((p) => `preset ${p}`);
    case 1:
      return [`level ${f.level}`, f.needsComment ? "ignores: noted" : "ignores allowed"];
    case 2:
      return [`larastan ${f.larastan}`, "via installer"];
    case 3:
      return f.rectorSets;
    case 4:
      return [...f.eslintFlavours, f.a11y ? "vue + a11y" : "vue"];
    case 5:
      return [
        `${f.pr.printWidth} columns`,
        f.pr.singleQuote ? "single quotes" : "double quotes",
        f.pr.semi ? "semicolons" : "no semicolons",
        `trailing: ${f.pr.trailingComma}`,
      ];
    default: {
      // The baseline: the flag from the page's own command, on its own line.
      const flag = (row.files[0] ?? "").split(/\s+/).find((w) => w.startsWith("--"));
      return flag ? [flag] : [];
    }
  }
}

function fontData(rel) {
  return readFileSync(path.join(siteDir, "assets", "fonts", rel)).toString("base64");
}

async function buildTapeSvg(rows) {
  const f = presetFacts();
  const W = 1280;
  const H = 350;
  const tapeTop = 126;
  const tapeH = 56;
  const tapeX0 = 150;
  const tapeX1 = 1252;
  const slotX0 = 262;
  const slotX1 = 1190;
  const pitch = (slotX1 - slotX0) / (rows.length - 1);
  const CYCLE = 12; // seconds: unroll, hold the finished tape, roll back
  const unrollEnd = 36; // percent of the cycle spent unrolling
  const fadeAt = 92;
  const inch = 48;
  const run = tapeX1 - tapeX0;

  const avatar = await sharp(readFileSync(path.join(siteDir, "brand", "pinstripe-avatar-256.png")))
    .resize(192, 192)
    .png({ compressionLevel: 9 })
    .toBuffer();

  // Tape ticks: quarters, halves, whole inches with numbers.
  let ticks = "";
  let nums = "";
  let n = 0;
  for (let x = tapeX0 + inch / 4; x < tapeX1 - 4; x += inch / 4, n += 1) {
    const whole = (n + 1) % 4 === 0;
    const half = (n + 1) % 2 === 0;
    const len = whole ? 24 : half ? 15 : 9;
    ticks += `M${x} ${tapeTop}v${len}`;
    if (whole) nums += `<text x="${x + 4}" y="${tapeTop + 46}">${(n + 1) / 4}</text>`;
  }

  let marks = "";
  let css = "";
  rows.forEach((row, i) => {
    const cx = slotX0 + i * pitch;
    const lines = tapeLines(f, row, i);
    // The mark shows up as the tape's tip passes it.
    const at = unrollEnd * ((cx - tapeX0) / run);
    css += `    @keyframes m${i} { 0%, ${at.toFixed(2)}% { opacity: 0; transform: translateY(8px) } ${(at + 3).toFixed(2)}%, ${fadeAt}% { opacity: 1; transform: none } 97%, 100% { opacity: 0; transform: none } }
    .m${i} { animation: m${i} ${CYCLE}s ease-out infinite both; }
`;
    const lineEls = lines
      .map((t, k) => `<text class="val" x="${cx}" y="${222 + k * 22}" text-anchor="middle">${esc(t)}</text>`)
      .join("");
    marks += `  <g class="m${i}">
    <text class="piece" x="${cx}" y="46" text-anchor="middle">${esc(row.name)}</text>
    <text class="tool" x="${cx}" y="86" text-anchor="middle">${esc(row.tool)}</text>
    <path d="M${cx} 100V${tapeTop + tapeH}" stroke="${CREAM}" stroke-width="2"/>
    <circle cx="${cx}" cy="100" r="5" fill="${GOLD}"/>
    <path d="M${cx - 9} ${tapeTop + tapeH}L${cx} ${tapeTop + tapeH - 12}L${cx + 9} ${tapeTop + tapeH}Z" fill="${GREEN_DEEP}"/>
    <path d="M${cx} ${tapeTop + tapeH}v14" stroke="${CREAM}" stroke-width="2"/>
    ${lineEls}
    <text class="status" x="${cx}" y="${222 + lines.length * 22 + 6}" text-anchor="middle">${esc(row.status.toLowerCase())}</text>
  </g>
`;
  });

  const desc =
    "A yellow tailor's tape measure unrolls from its case across a green card. Seven marks sit on it, one per Pinstripe preset: " +
    rows.map((r, i) => `${r.name}, ${r.tool}: ${tapeLines(f, r, i).join(", ")}, ${r.status.toLowerCase()}`).join("; ") +
    ".";

  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" role="img" aria-labelledby="tape-title tape-desc">
  <title id="tape-title">Pinstripe's tape measure: seven presets, each marked on the tape</title>
  <desc id="tape-desc">${esc(desc)}</desc>
  <style>
    @font-face { font-family: "Bodoni Moda"; font-weight: 600; src: url(data:font/woff2;base64,${fontData("bodoni-moda-latin-600-normal.woff2")}) format("woff2"); }
    @font-face { font-family: "Reenie Beanie"; src: url(data:font/woff2;base64,${fontData("reenie-beanie-latin-400-normal.woff2")}) format("woff2"); }
    .tool { font: 600 25px "Bodoni Moda", Didot, Georgia, serif; fill: ${CREAM}; }
    .piece { font: 27px "Reenie Beanie", "Bradley Hand", cursive; fill: ${GOLD_SOFT}; letter-spacing: .5px; }
    .val { font: 14.5px Consolas, Menlo, "DejaVu Sans Mono", monospace; fill: ${CREAM}; letter-spacing: .2px; }
    .status { font: italic 600 14px "Bodoni Moda", Didot, Georgia, serif; fill: ${GOLD_SOFT}; letter-spacing: .6px; }
    .nums text { font: bold 13px Lato, "Segoe UI", Helvetica, Arial, sans-serif; fill: ${GREEN_DEEP}; }
    @keyframes unroll { 0% { width: 0 } ${unrollEnd}%, ${fadeAt}% { width: ${run}px } 97%, 100% { width: 0 } }
    @keyframes hook { 0% { transform: translateX(0) } ${unrollEnd}%, ${fadeAt}% { transform: translateX(${run}px) } 97%, 100% { transform: translateX(0) } }
    .reveal { animation: unroll ${CYCLE}s cubic-bezier(.45,.05,.4,1) infinite both; }
    .tip { animation: hook ${CYCLE}s cubic-bezier(.45,.05,.4,1) infinite both; }
${css}    @media (prefers-reduced-motion: reduce) {
      .reveal, .tip, ${rows.map((_, i) => `.m${i}`).join(", ")} { animation: none; }
      .reveal { width: ${run}px; }
      .tip { transform: translateX(${run}px); }
    }
  </style>
  <defs>
    <clipPath id="unrolled"><rect class="reveal" x="${tapeX0}" y="${tapeTop - 4}" width="${run}" height="${tapeH + 8}"/></clipPath>
    <pattern id="stripes" width="14" height="14" patternUnits="userSpaceOnUse"><rect width="14" height="14" fill="${GREEN_DEEP}"/><rect width="2" height="14" fill="#ffffff" fill-opacity=".05"/></pattern>
  </defs>
  <rect width="${W}" height="${H}" rx="18" fill="url(#stripes)"/>
  <rect x=".75" y=".75" width="${W - 1.5}" height="${H - 1.5}" rx="17.25" fill="none" stroke="${GOLD}" stroke-opacity=".35" stroke-width="1.5"/>
  <g clip-path="url(#unrolled)">
    <rect x="${tapeX0}" y="${tapeTop}" width="${run + 6}" height="${tapeH}" fill="${GOLD}"/>
    <rect x="${tapeX0}" y="${tapeTop}" width="${run + 6}" height="3" fill="#fff" fill-opacity=".45"/>
    <path d="${ticks}" stroke="${GREEN_DEEP}" stroke-width="1.6" fill="none"/>
    <g class="nums">${nums}</g>
  </g>
  <rect class="tip" x="${tapeX0 - 2}" y="${tapeTop - 5}" width="9" height="${tapeH + 10}" rx="2.5" fill="${CREAM}" stroke="${GREEN_DEEP}" stroke-opacity=".5"/>
  <rect x="26" y="${tapeTop - 40}" width="${tapeX0 - 14}" height="${tapeH + 80}" rx="26" fill="${CLOTH}" stroke="${GOLD}" stroke-width="2"/>
  <image x="${26 + (tapeX0 - 14 - 96) / 2}" y="${tapeTop + tapeH / 2 - 48}" width="96" height="96" href="data:image/png;base64,${avatar.toString("base64")}"/>
${marks}</svg>
`;
}

async function buildTape(page) {
  console.log("tape measure:");
  await assertClean(page, "#pieces", "pieces");
  const rows = await page.$$eval("#pieces .order__row", (els) =>
    els.map((el) => ({
      name: el.querySelector(".order__name").textContent.trim(),
      tool: el.querySelector(".order__tool").textContent.trim(),
      status: el.querySelector(".woven").textContent.trim(),
      files: [...el.querySelectorAll(".order__files code")].map((c) => c.textContent.trim()),
    })),
  );
  if (rows.length !== 7) fail(`expected seven pieces, found ${rows.length}`);
  const svg = await buildTapeSvg(rows);
  if (FORBIDDEN.test(svg.replace(/base64,[A-Za-z0-9+/=]+/g, ""))) fail("forbidden text in tape.svg");
  record("tape.svg", Buffer.from(svg, "utf8"));
}

/* -------------------------------------------------------------- 6. gallery */

// A contact sheet of three existing mobile crops: the same shop, three windows.
async function buildGallery(page) {
  console.log("gallery:");
  const crops = ["mobile-02-hero.png", "mobile-03-fitting.png", "mobile-04-pieces-1.png"];
  for (const c of crops) {
    if (!existsSync(path.join(siteDir, "screenshots", "sections", c))) fail(`missing crop ${c}`);
  }
  const html = `<!doctype html>
<html lang="en"><head><meta charset="utf-8">
<style>
  html, body { margin: 0; padding: 0; }
  .sheet { width: fit-content; background: ${GREEN}; padding: 40px; display: flex; gap: 32px; }
  img { box-sizing: border-box; display: block; width: 360px; height: 640px; object-fit: cover; object-position: top;
        border-radius: 14px; border: 1px solid rgba(255, 204, 51, .35); }
</style></head>
<body><div class="sheet">
${crops.map((c) => `  <img src="${pathToFileURL(path.join(siteDir, "screenshots", "sections", c)).href}" alt="">`).join("\n")}
</div></body></html>`;
  await page.setContent(html, { waitUntil: "networkidle" });
  await assertClean(page, ".sheet", "gallery");
  const png = await page.locator(".sheet").screenshot();
  record("gallery.jpg", await sharp(png).resize({ width: 1280 }).jpeg({ quality: 82, mozjpeg: true }).toBuffer());
}

/* ---------------------------------------------------------------- 7. board */

async function buildBoard() {
  console.log("board:");
  const src = path.join(siteDir, "brand", "pinstripe-board.png");
  if (!existsSync(src)) fail("missing site/brand/pinstripe-board.png");
  let width = 1600;
  let jpg;
  do {
    jpg = await sharp(readFileSync(src))
      .resize({ width, withoutEnlargement: true })
      .jpeg({ quality: 82, mozjpeg: true })
      .toBuffer();
    width -= 200;
  } while (jpg.length > 290 * 1024 && width > 600);
  record("board.jpg", jpg);
}

/* -------------------------------------------------------------------- main */

async function main() {
  await buildCast();
  buildBadges();
  await buildBoard();

  const browser = await chromium.launch();
  try {
    const { context, page } = await openPage(browser);
    await buildTape(page);
    await buildHero(page);
    await buildGallery(page);
    await context.close();
  } finally {
    await browser.close();
  }

  let over = false;
  for (const { rel, bytes } of outputs) {
    if (bytes > MAX_BYTES) {
      console.error(`FAIL: ${rel} is ${bytes} bytes (limit ${MAX_BYTES})`);
      over = true;
    }
  }
  if (over) process.exit(1);
  console.log(`done: ${outputs.length} files, all at or under ${MAX_BYTES} bytes`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
