import { chromium } from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({ viewport:{width:1200,height:900} })).newPage();
await p.goto('http://localhost:8084/memes',{waitUntil:'networkidle'});
await p.evaluate(()=>localStorage.removeItem('memes-nsfw-ok'));
await p.reload({waitUntil:'networkidle'});
await p.waitForTimeout(500);
// Can any meme text be read through the gate?
const feedVisible = await p.evaluate(()=>{
  const fw = [...document.querySelectorAll('[x-show]')].find(e=>e.getAttribute('x-show')==='entered');
  if(!fw) return 'no-feed-wrapper';
  return getComputedStyle(fw).display; // 'none' = hidden = good
});
const gateBg = await p.evaluate(()=>{const h=[...document.querySelectorAll('h2')].find(e=>e.textContent.includes('Content warning'));const g=h.closest('.fixed');const cs=getComputedStyle(g);return cs.backgroundColor+' opacity='+cs.opacity;});
console.log('feed wrapper display (expect none) =', feedVisible);
console.log('gate bg (expect opaque, no alpha) =', gateBg);
await p.screenshot({path:'nsfw-gate-fixed.png'});
// Enter -> feed shows
await p.locator('button:has-text("Enter")').click();
await p.waitForTimeout(400);
const afterEnter = await p.evaluate(()=>{const fw=[...document.querySelectorAll('[x-show]')].find(e=>e.getAttribute('x-show')==='entered');return getComputedStyle(fw).display;});
console.log('feed display after Enter (expect block) =', afterEnter);
await p.screenshot({path:'nsfw-after-enter-fixed.png'});
await b.close();
