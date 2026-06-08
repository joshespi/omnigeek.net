import { chromium } from 'playwright';

const BASE = 'http://localhost:8084';
const shot = (p, name) => p.screenshot({ path: `nsfw-${name}.png`, fullPage: false });

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1200, height: 900 } });
const page = await ctx.newPage();
const log = (...a) => console.log('[verify]', ...a);

try {
  // --- Log in ---
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[type="email"]', 'verify@omnigeek.test');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');
  log('logged in, url =', page.url());

  // --- (1) Visit /memes: gate should be visible ---
  await page.goto(`${BASE}/memes`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(400); // let Alpine init
  const warnVisible = await page.locator('text=Content warning').isVisible();
  const enterVisible = await page.locator('button:has-text("Enter")').isVisible();
  log('gate "Content warning" visible =', warnVisible, '| Enter btn visible =', enterVisible);
  await shot(page, '1-gate');

  // Is the feed content behind the gate (overlay covering)?
  const enterBtn = page.locator('button:has-text("Enter")');

  // --- Click Enter: gate dismisses ---
  await enterBtn.click();
  await page.waitForTimeout(300);
  const warnAfter = await page.locator('text=Content warning').isVisible();
  log('after Enter, gate visible =', warnAfter, '(expect false)');
  await shot(page, '2-after-enter');

  // localStorage remembered?
  const ls = await page.evaluate(() => localStorage.getItem('memes-nsfw-ok'));
  log('localStorage memes-nsfw-ok =', ls, '(expect "1")');

  // --- Reload: gate should NOT reappear ---
  await page.reload({ waitUntil: 'networkidle' });
  await page.waitForTimeout(400);
  const warnReload = await page.locator('text=Content warning').isVisible();
  log('after reload, gate visible =', warnReload, '(expect false — remembered)');
  await shot(page, '3-after-reload');

  // --- (2) NSFW meme blur + click-to-reveal ---
  // The NSFW overlay button says "Click to reveal"
  const revealBtn = page.locator('button:has-text("Click to reveal")').first();
  const revealVisible = await revealBtn.isVisible().catch(() => false);
  log('NSFW "Click to reveal" overlay visible =', revealVisible, '(expect true)');

  // Is the image blurred? check the grid inner div class binding
  const blurred = await page.evaluate(() => {
    const el = [...document.querySelectorAll('div')].find(d => d.className.includes('blur-2xl'));
    return !!el;
  });
  log('blurred element present (blur-2xl) =', blurred, '(expect true before reveal)');
  await shot(page, '4-meme-blurred');

  if (revealVisible) {
    await revealBtn.click();
    await page.waitForTimeout(400);
    const stillBlurred = await page.evaluate(() => {
      const el = [...document.querySelectorAll('div')].find(d => d.className.includes('blur-2xl'));
      return !!el;
    });
    const revealGone = !(await revealBtn.isVisible().catch(() => false));
    log('after reveal click: blur gone =', !stillBlurred, '| overlay gone =', revealGone, '(expect both true)');
    await shot(page, '5-meme-revealed');
  }

  log('DONE');
} catch (e) {
  console.error('[verify] ERROR', e.message);
  await shot(page, 'error');
  process.exitCode = 1;
} finally {
  await browser.close();
}
