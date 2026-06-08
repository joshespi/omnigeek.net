import { chromium } from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({ viewport:{width:1200,height:1400} })).newPage();
await p.goto('http://localhost:8084/memes',{waitUntil:'networkidle'});
await p.evaluate(()=>localStorage.setItem('memes-nsfw-ok','1'));
await p.reload({waitUntil:'networkidle'});
await p.waitForTimeout(800);
const r = await p.evaluate(()=>{
  // For each blur candidate (the grid inner div with x-bind:class), report computed filter + classes
  const out=[];
  document.querySelectorAll('div').forEach(d=>{
    if(d.hasAttribute('x-bind:class') || d.className.includes('blur-2xl')){
      const cs=getComputedStyle(d);
      out.push({cls:d.className.slice(0,80), filter:cs.filter, hasBind:d.hasAttribute('x-bind:class')});
    }
  });
  return out;
});
console.log(JSON.stringify(r,null,2));
await b.close();
