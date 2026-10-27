// Draws the Roxy icon: a galah peeking over a browser window, where the kimiyu bear on the page is picked out
// with a dashed outline and a feedback pin, on an outback-sky square. Writes the SVGs to artwork/ and renders
// the PNGs with headless Chrome (no npm packages needed).
// Usage: node scripts/make-icon.mjs
import { execFileSync } from "node:child_process";
import { mkdirSync, mkdtempSync, readFileSync, writeFileSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";

const root = new URL("../", import.meta.url).pathname;
const chrome = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome";

// The kimiyu studio bear (copied from kimiyu.web), faded into the page like a hero image.
const faded = {
  "#9d6b45": "#d3d6db", // ears, head
  "#e3bb92": "#e2e4e8", // inside the ears
  "#ddb487": "#e0e2e6", // snout
  "#292929": "#b8bcc3", // eyes, nose, mouth
  "#fcfbfc": "#d3d6db", // eye highlights blend into the head
};
const bear = readFileSync(root + "artwork/kimiyu-bear.svg", "utf8")
  .replace(/^[\s\S]*?<svg[^>]*>|<\/svg>\s*$/g, "")
  .replace(/<!--.*?-->/g, "")
  .replace(/fill="(#[0-9a-f]{6})"/gi, (match, hex) => `fill="${faded[hex.toLowerCase()] ?? "#d6d9de"}"`);

const defs = `
    <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#dcebf8"/><stop offset=".55" stop-color="#a9c8e8"/><stop offset="1" stop-color="#6b97c9"/></linearGradient>
    <linearGradient id="sheen" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".2"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient>
    <linearGradient id="rose" gradientUnits="userSpaceOnUse" x1="0" y1="200" x2="0" y2="520"><stop offset="0" stop-color="#f08aa0"/><stop offset="1" stop-color="#d34f72"/></linearGradient>
    <linearGradient id="wing" gradientUnits="userSpaceOnUse" x1="0" y1="380" x2="0" y2="540"><stop offset="0" stop-color="#b3b8c0"/><stop offset="1" stop-color="#868c95"/></linearGradient>
    <linearGradient id="beak" gradientUnits="userSpaceOnUse" x1="0" y1="380" x2="0" y2="470"><stop offset="0" stop-color="#f4ede2"/><stop offset="1" stop-color="#cfc4b3"/></linearGradient>
    <linearGradient id="page" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#eceef1"/></linearGradient>
    <linearGradient id="bar" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#e9ebee"/><stop offset="1" stop-color="#dcdfe3"/></linearGradient>
    <linearGradient id="pin" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff7a59"/><stop offset="1" stop-color="#e5482a"/></linearGradient>
    <filter id="drop" x="-20%" y="-20%" width="140%" height="160%"><feDropShadow dx="0" dy="14" stdDeviation="18" flood-color="#06141a" flood-opacity=".35"/></filter>
    <filter id="soft" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="10" stdDeviation="12" flood-color="#06141a" flood-opacity=".28"/></filter>
    <filter id="icon" x="-10%" y="-10%" width="120%" height="125%"><feDropShadow dx="0" dy="10" stdDeviation="12" flood-color="#000" flood-opacity=".3"/></filter>
    <clipPath id="squircle"><rect x="100" y="100" width="824" height="824" rx="185"/></clipPath>
    <clipPath id="window"><rect x="236" y="508" width="552" height="372" rx="34"/></clipPath>`;

const background = `
  <rect width="1024" height="1024" fill="url(#bg)"/>
  <rect width="1024" height="560" fill="url(#sheen)"/>`;

// The galah behind the window: grey wings, crest, rose head with its pale cap, eyes and beak.
const galah = `
  <g filter="url(#soft)">
    <ellipse cx="512" cy="500" rx="200" ry="80" fill="url(#wing)"/>
    <path d="M462 204Q456 156 474 126Q486 160 500 180Q500 130 522 104Q528 150 530 182Q546 140 574 124Q570 168 560 204Z" fill="#f7cdd7"/>
    <ellipse cx="512" cy="356" rx="160" ry="164" fill="url(#rose)"/>
  </g>
  <path d="M354 356C356 254 424 192 512 192S668 254 670 356Q650 326 622 330Q598 306 566 318Q540 298 512 312Q484 298 458 318Q426 306 402 330Q374 326 354 356Z" fill="#f9d9e0"/>
  <g fill="#f9e3e8"><circle cx="446" cy="350" r="30"/><circle cx="578" cy="350" r="30"/></g>
  <g fill="#2a1418"><circle cx="446" cy="351" r="18"/><circle cx="578" cy="351" r="18"/></g>
  <g fill="#fff"><circle cx="452" cy="345" r="6"/><circle cx="584" cy="345" r="6"/></g>
  <path d="M474 398Q512 380 550 398Q556 440 520 470Q512 476 504 470Q468 440 474 398Z" fill="url(#beak)"/>
  <path d="M482 420Q512 432 542 420" fill="none" stroke="#b5a893" stroke-width="5" stroke-linecap="round"/>`;

// The browser window: title bar with three dots, a few lines of page, the faded bear picked out with a dashed
// outline and a feedback pin on its corner.
const browser = `
  <g filter="url(#drop)"><rect x="236" y="508" width="552" height="372" rx="34" fill="url(#page)"/></g>
  <g clip-path="url(#window)">
    <rect x="236" y="508" width="552" height="62" fill="url(#bar)"/>
    <g><circle cx="282" cy="539" r="11" fill="#ff6159"/><circle cx="318" cy="539" r="11" fill="#ffbd2e"/><circle cx="354" cy="539" r="11" fill="#29c840"/></g>
    <g transform="translate(372 720) scale(0.15) translate(-627 -632)">${bear}</g>
    <g fill="#d7dade"><rect x="490" y="632" width="240" height="20" rx="10"/><rect x="490" y="672" width="190" height="16" rx="8"/><rect x="490" y="704" width="216" height="16" rx="8"/></g>
    <rect x="490" y="764" width="130" height="44" rx="22" fill="#c5c9cf"/>
    <g fill="#d7dade"><rect x="288" y="830" width="440" height="14" rx="7"/></g>
  </g>
  <rect x="278" y="622" width="188" height="186" rx="26" fill="none" stroke="#e5482a" stroke-width="5" stroke-dasharray="14 10"/>
  <path d="M466 622C442 598 422 586 422 564A44 44 0 1 1 510 564C510 586 490 598 466 622Z" fill="url(#pin)" filter="url(#soft)"/>
  <circle cx="466" cy="564" r="16" fill="#fff"/>`;

// The feet gripping the window's top edge, toes and claws.
const feet = `
  <g filter="url(#soft)" fill="#8e939b"><ellipse cx="370" cy="518" rx="17" ry="24"/><ellipse cx="400" cy="522" rx="17" ry="26"/><ellipse cx="430" cy="518" rx="17" ry="24"/><ellipse cx="594" cy="518" rx="17" ry="24"/><ellipse cx="624" cy="522" rx="17" ry="26"/><ellipse cx="654" cy="518" rx="17" ry="24"/></g>
  <g fill="#3b3f45"><path d="M364 536l6 10 6-10zM394 542l6 10 6-10zM424 536l6 10 6-10zM588 536l6 10 6-10zM618 542l6 10 6-10zM648 536l6 10 6-10z"/></g>`;

const art = `\n  <g transform="translate(0 -10)">${galah}${browser}${feet}\n  </g>`;

const svg = (viewBox, body) =>
  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}">\n  <defs>${defs}\n  </defs>${body}\n</svg>\n`;

// Rounded square on transparent with a shadow, on the macOS icon grid like the siblings': app icon, favicon, README.
const rounded = svg("0 0 1024 1024", `
  <rect x="100" y="100" width="824" height="824" rx="185" fill="#9fc0e3" filter="url(#icon)"/>
  <g clip-path="url(#squircle)"><g transform="translate(100 100) scale(0.8047)">${background}${art}</g></g>`);
// Full-bleed square for the apple-touch-icon (iOS rounds the corners itself).
const square = svg("0 0 1024 1024", background + art);
// The galah and its window without a background.
const mascot = svg("130 70 764 840", art);

mkdirSync(root + "artwork", { recursive: true });
writeFileSync(root + "artwork/icon.svg", rounded);
writeFileSync(root + "artwork/icon-square.svg", square);
writeFileSync(root + "artwork/mascot.svg", mascot);

const renders = [
  [rounded, "artwork/icon.png", 1024],
  [square, "artwork/icon-square.png", 1024],
  [mascot, "artwork/mascot.png", 800],
  [rounded, "artwork/favicon.png", 96],
  [square, "artwork/apple-touch-icon.png", 180],
];
const work = mkdtempSync(join(tmpdir(), "roxy-icon-"));
for (const [source, out, size] of renders) {
  const page = join(work, "page.html");
  writeFileSync(page, `<style>html,body{margin:0;background:transparent}svg{display:block;width:${size}px;height:${size}px}</style>${source}`);
  execFileSync(chrome, [
    "--headless", "--disable-gpu", "--hide-scrollbars", "--force-device-scale-factor=1",
    `--window-size=${size},${size}`, "--default-background-color=00000000",
    `--user-data-dir=${join(work, "profile")}`, `--screenshot=${root + out}`, `file://${page}`,
  ], { stdio: "ignore" });
  console.log(out);
}
