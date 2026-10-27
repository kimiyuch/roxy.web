// Draws the candidate icons for the feedback widget, in the style of the kimiyu apps: an Australian animal
// peeking over the thing the app is about (here a browser window with a feedback pin on it), the kimiyu bear
// on that thing, on a rounded square. Writes the SVGs to artwork/explorations/<name>/ and renders PNGs with headless Chrome.
// Usage: node scripts/make-icons-explorations.mjs
import { execFileSync } from "node:child_process";
import { mkdirSync, mkdtempSync, readFileSync, writeFileSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";

const root = new URL("../", import.meta.url).pathname;
const chrome = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome";

// The kimiyu studio bear, faded into the page like a hero image.
const faded = {
  "#9d6b45": "#d3d6db",
  "#e3bb92": "#e2e4e8",
  "#ddb487": "#e0e2e6",
  "#292929": "#b8bcc3",
  "#fcfbfc": "#d3d6db",
};
const bear = readFileSync(root + "artwork/kimiyu-bear.svg", "utf8")
  .replace(/^[\s\S]*?<svg[^>]*>|<\/svg>\s*$/g, "")
  .replace(/<!--.*?-->/g, "")
  .replace(/fill="(#[0-9a-f]{6})"/gi, (m, hex) => `fill="${faded[hex.toLowerCase()] ?? "#d6d9de"}"`);

const sharedDefs = `
    <linearGradient id="sheen" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".2"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient>
    <linearGradient id="page" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#eceef1"/></linearGradient>
    <linearGradient id="bar" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#e9ebee"/><stop offset="1" stop-color="#dcdfe3"/></linearGradient>
    <linearGradient id="pin" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff7a59"/><stop offset="1" stop-color="#e5482a"/></linearGradient>
    <filter id="drop" x="-20%" y="-20%" width="140%" height="160%"><feDropShadow dx="0" dy="14" stdDeviation="18" flood-color="#06141a" flood-opacity=".35"/></filter>
    <filter id="soft" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="10" stdDeviation="12" flood-color="#06141a" flood-opacity=".28"/></filter>
    <filter id="icon" x="-10%" y="-10%" width="120%" height="125%"><feDropShadow dx="0" dy="10" stdDeviation="12" flood-color="#000" flood-opacity=".3"/></filter>
    <clipPath id="squircle"><rect x="100" y="100" width="824" height="824" rx="185"/></clipPath>
    <clipPath id="window"><rect x="236" y="508" width="552" height="372" rx="34"/></clipPath>`;

// The browser window: title bar with three dots, a few lines of page, the faded bear, and one bit of the page
// picked out with a dashed outline and a feedback pin on its corner.
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

const candidates = {
  // A numbat: eats bugs, around 20,000 termites a day. Rust fur, the black stripe through the eye with pale
  // stripes either side, a long pointed snout. On wandoo blue-green, the woodland it lives in.
  numbat: {
    tile: "#8ccad1",
    defs: `
    <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#d4eef0"/><stop offset=".55" stop-color="#9fd2d8"/><stop offset="1" stop-color="#5ea7b2"/></linearGradient>
    <linearGradient id="fur" gradientUnits="userSpaceOnUse" x1="0" y1="160" x2="0" y2="540"><stop offset="0" stop-color="#d07a46"/><stop offset="1" stop-color="#9a4c2a"/></linearGradient>
    <linearGradient id="muzzle" gradientUnits="userSpaceOnUse" x1="0" y1="360" x2="0" y2="540"><stop offset="0" stop-color="#e9b48a"/><stop offset="1" stop-color="#c98a5e"/></linearGradient>`,
    animal: `
  <g filter="url(#soft)">
    <g transform="rotate(-24 382 222)"><ellipse cx="382" cy="222" rx="44" ry="66" fill="url(#fur)"/><ellipse cx="384" cy="232" rx="24" ry="40" fill="#e9a98a"/></g>
    <g transform="rotate(24 642 222)"><ellipse cx="642" cy="222" rx="44" ry="66" fill="url(#fur)"/><ellipse cx="640" cy="232" rx="24" ry="40" fill="#e9a98a"/></g>
    <path d="M512 196C620 196 690 262 690 340C690 404 640 430 600 456C572 476 552 520 512 532C472 520 452 476 424 456C384 430 334 404 334 340C334 262 404 196 512 196Z" fill="url(#fur)"/>
  </g>
  <path d="M512 372C552 372 584 400 584 440C584 478 552 516 512 530C472 516 440 478 440 440C440 400 472 372 512 372Z" fill="url(#muzzle)"/>
  <g fill="none" stroke-linecap="round">
    <path d="M360 300Q416 306 446 330Q480 362 494 470M664 300Q608 306 578 330Q544 362 530 470" stroke="#f3dcc4" stroke-width="30"/>
    <path d="M362 302Q416 306 446 330Q478 362 492 464M662 302Q608 306 578 330Q546 362 532 464" stroke="#2b1913" stroke-width="11"/>
  </g>
  <g fill="#f3dcc4"><circle cx="446" cy="330" r="28"/><circle cx="578" cy="330" r="28"/></g>
  <g fill="#1b120e"><circle cx="446" cy="331" r="21"/><circle cx="578" cy="331" r="21"/></g>
  <g fill="#fff"><circle cx="453" cy="323" r="6.5"/><circle cx="585" cy="323" r="6.5"/></g>
  <ellipse cx="512" cy="510" rx="24" ry="16" fill="#241612"/>
  <ellipse cx="505" cy="504" rx="7" ry="4" fill="#fff" opacity=".35"/>
  <g filter="url(#soft)" fill="#a8542f"><ellipse cx="396" cy="514" rx="46" ry="28"/><ellipse cx="628" cy="514" rx="46" ry="28"/></g>
  <g fill="none" stroke="#f3dcc4" stroke-width="6" stroke-linecap="round" opacity=".9">
    <path d="M379 523v13M396 525v13M413 523v13M611 523v13M628 525v13M645 523v13"/>
  </g>`,
  },

  // A galah: the pink and grey cockatoo, loud, chatty and never shy about telling you what it thinks.
  // Pale pink cap and crest, rose face, grey wings, a horn-coloured beak. On a clear outback sky.
  galah: {
    tile: "#9fc0e3",
    defs: `
    <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#dcebf8"/><stop offset=".55" stop-color="#a9c8e8"/><stop offset="1" stop-color="#6b97c9"/></linearGradient>
    <linearGradient id="rose" gradientUnits="userSpaceOnUse" x1="0" y1="200" x2="0" y2="520"><stop offset="0" stop-color="#f08aa0"/><stop offset="1" stop-color="#d34f72"/></linearGradient>
    <linearGradient id="wing" gradientUnits="userSpaceOnUse" x1="0" y1="380" x2="0" y2="540"><stop offset="0" stop-color="#b3b8c0"/><stop offset="1" stop-color="#868c95"/></linearGradient>
    <linearGradient id="beak" gradientUnits="userSpaceOnUse" x1="0" y1="380" x2="0" y2="470"><stop offset="0" stop-color="#f4ede2"/><stop offset="1" stop-color="#cfc4b3"/></linearGradient>`,
    animal: `
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
  <path d="M482 420Q512 432 542 420" fill="none" stroke="#b5a893" stroke-width="5" stroke-linecap="round"/>
  <g filter="url(#soft)" fill="#8e939b"><ellipse cx="370" cy="518" rx="17" ry="24"/><ellipse cx="400" cy="522" rx="17" ry="26"/><ellipse cx="430" cy="518" rx="17" ry="24"/><ellipse cx="594" cy="518" rx="17" ry="24"/><ellipse cx="624" cy="522" rx="17" ry="26"/><ellipse cx="654" cy="518" rx="17" ry="24"/></g>
  <g fill="#3b3f45"><path d="M364 536l6 10 6-10zM394 542l6 10 6-10zM424 536l6 10 6-10zM588 536l6 10 6-10zM618 542l6 10 6-10zM648 536l6 10 6-10z"/></g>`,
  },
};

const svg = (viewBox, defs, body) =>
  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}">\n  <defs>${sharedDefs}${defs}\n  </defs>${body}\n</svg>\n`;

const work = mkdtempSync(join(tmpdir(), "feedback-icon-"));
const render = (source, out, size) => {
  const page = join(work, "page.html");
  writeFileSync(page, `<style>html,body{margin:0;background:transparent}svg{display:block;width:${size}px;height:${size}px}</style>${source}`);
  execFileSync(chrome, [
    "--headless", "--disable-gpu", "--hide-scrollbars", "--force-device-scale-factor=1",
    `--window-size=${size},${size}`, "--default-background-color=00000000",
    `--user-data-dir=${join(work, "profile")}`, `--screenshot=${root + out}`, `file://${page}`,
  ], { stdio: "ignore" });
  console.log(out);
};

for (const [name, c] of Object.entries(candidates)) {
  const background = `
  <rect width="1024" height="1024" fill="url(#bg)"/>
  <rect width="1024" height="560" fill="url(#sheen)"/>`;
  // The animal behind the window, then the window, then the paws on its top edge (the last <g> of the animal).
  const [head, paws] = c.animal.split(/(?=\n  <g filter="url\(#soft\)" fill)/);
  const art = `\n  <g transform="translate(0 -10)">${head}${browser}${paws}\n  </g>`;
  const rounded = svg("0 0 1024 1024", c.defs, `
  <rect x="100" y="100" width="824" height="824" rx="185" fill="${c.tile}" filter="url(#icon)"/>
  <g clip-path="url(#squircle)"><g transform="translate(100 100) scale(0.8047)">${background}${art}</g></g>`);
  const mascot = svg("150 120 724 800", c.defs, art);

  mkdirSync(root + `artwork/explorations/${name}`, { recursive: true });
  writeFileSync(root + `artwork/explorations/${name}/icon.svg`, rounded);
  writeFileSync(root + `artwork/explorations/${name}/mascot.svg`, mascot);
  render(rounded, `artwork/explorations/${name}/icon.png`, 1024);
}
