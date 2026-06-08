import { chromium } from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({ viewport:{width:1200,height:1400} })).newPage();
await p.goto('http://localhost:8084/memes',{waitUntil:'networkidle'});
await p.evaluate(()=>localStorage.setItem('memes-nsfw-ok','1'));
await p.reload({waitUntil:'networkidle'});
await p.waitForTimeout(700);
// Count NSFW overlays + blur elements actually rendered
const r = await p.evaluate(()=>{
  const reveals = [...document.querySelectorAll('button')].filter(b=>b.textContent.includes('Click to reveal')).length;
  const blurs = [...document.querySelectorAll('div')].filter(d=>d.className.includes('blur-2xl')).length;
  // find the "nsfw test" post card and check its gallery
  return { revealOverlays: reveals, blurDivs: blurs };
});
console.log(JSON.stringify(r));
await p.screenshot({path:'nsfw-feed-full.png', fullPage:true});
await b.close();
