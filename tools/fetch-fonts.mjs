// Downloads the Inria Serif / Inria Sans latin woff2 files Google Fonts would
// serve, so the theme can self-host them (no third-party request, GDPR-clean).
// Run: node tools/fetch-fonts.mjs
import { writeFile, mkdir } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const UA =
  'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 ' +
  '(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

const OUT = join(dirname(fileURLToPath(import.meta.url)), '..', 'assets', 'fonts');
const FAMILIES = [
  { css: 'Inria+Serif', slug: 'inria-serif' },
  { css: 'Inria+Sans', slug: 'inria-sans' },
];
const WEIGHTS = [300, 400, 700];

async function run() {
  await mkdir(OUT, { recursive: true });
  for (const fam of FAMILIES) {
    const cssUrl =
      `https://fonts.googleapis.com/css2?family=${fam.css}:wght@` +
      `${WEIGHTS.join(';')}&display=swap`;
    const css = await fetch(cssUrl, { headers: { 'User-Agent': UA } }).then((r) => r.text());

    // The API emits `/* latin */` immediately before each latin @font-face block.
    const blocks = [...css.matchAll(/\/\*\s*latin\s*\*\/\s*@font-face\s*{([^}]*)}/g)];
    if (blocks.length === 0) {
      console.error('No /* latin */ blocks found. Actual CSS response:');
      console.error(css);
      process.exit(1);
    }
    for (const [, body] of blocks) {
      const weight = (body.match(/font-weight:\s*(\d+)/) || [])[1];
      const url = (body.match(/url\((https:[^)]+\.woff2)\)/) || [])[1];
      if (!weight || !url || !WEIGHTS.includes(Number(weight))) continue;
      const buf = Buffer.from(await fetch(url, { headers: { 'User-Agent': UA } }).then((r) => r.arrayBuffer()));
      const file = join(OUT, `${fam.slug}-${weight}.woff2`);
      await writeFile(file, buf);
      console.log(`saved ${file} (${buf.length} bytes)`);
    }
  }
}

run().catch((e) => {
  console.error(e);
  process.exit(1);
});
