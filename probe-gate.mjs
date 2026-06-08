import { chromium } from 'playwright';
const BASE = 'http://localhost:8084';
const b = await chromium.launch();
const p = await (await b.newContext({ viewport: { width: 1200, height: 900 } })).newPage();
await p.goto(`${BASE}/memes`, { waitUntil: 'networkidle' });
await p.evaluate(() => localStorage.removeItem('memes-nsfw-ok'));
await p.reload({ waitUntil: 'networkidle' });
await p.waitForTimeout(400);

const info = await p.evaluate(() => {
  // The gate is the fixed overlay containing "Content warning"
  const h2 = [...document.querySelectorAll('h2')].find(e => e.textContent.includes('Content warning'));
  if (!h2) return { found: false };
  const gate = h2.closest('.fixed');
  const r = gate.getBoundingClientRect();
  // Walk ancestors for transform/filter/overflow that breaks position:fixed
  let breakers = [];
  let el = gate.parentElement;
  while (el && el !== document.body) {
    const cs = getComputedStyle(el);
    if (cs.transform !== 'none' || cs.filter !== 'none' || cs.perspective !== 'none' || cs.willChange === 'transform') {
      breakers.push({ tag: el.tagName, cls: el.className.slice(0,60), transform: cs.transform, filter: cs.filter });
    }
    el = el.parentElement;
  }
  return {
    found: true,
    gateRect: { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) },
    viewport: { w: window.innerWidth, h: window.innerHeight },
    coversFullViewport: r.x <= 0 && r.y <= 0 && r.width >= window.innerWidth - 1 && r.height >= window.innerHeight - 1,
    fixedBreakingAncestors: breakers,
  };
});
console.log(JSON.stringify(info, null, 2));
await b.close();
