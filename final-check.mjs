import { chromium } from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({ viewport:{width:1200,height:1000} })).newPage();
await p.goto('http://localhost:8084/memes',{waitUntil:'networkidle'});
await p.evaluate(()=>localStorage.setItem('memes-nsfw-ok','1'));
await p.reload({waitUntil:'networkidle'});
await p.waitForTimeout(700);
await p.screenshot({path:'nsfw-blurred-final.png'});
// click first reveal
await p.locator('button:has-text("Click to reveal")').first().click();
await p.waitForTimeout(400);
const filtersAfter = await p.evaluate(()=>[...document.querySelectorAll('div')].filter(d=>d.className.includes('grid-cols')&&getComputedStyle(d).filter!=='none').length);
console.log('grids still blurred after revealing one (expect 1) =', filtersAfter);
await p.screenshot({path:'nsfw-one-revealed-final.png'});
await b.close();
