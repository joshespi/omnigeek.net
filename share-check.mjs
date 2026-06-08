import { chromium } from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext({ viewport:{width:1200,height:1000} })).newPage();
// find a post url
await p.goto('http://localhost:8084/',{waitUntil:'networkidle'});
const postHref = await p.evaluate(()=>{const a=[...document.querySelectorAll('a')].find(a=>/\/post\//.test(a.getAttribute('href')||''));return a?a.href:null;});
console.log('post url:', postHref);
await p.goto(postHref,{waitUntil:'networkidle'});
await p.waitForTimeout(500);
const r = await p.evaluate(()=>{
  const hrefs={};
  ['twitter.com/intent','bsky.app/intent','reddit.com/submit','facebook.com/sharer'].forEach(k=>{
    const a=[...document.querySelectorAll('a')].find(a=>(a.href||'').includes(k));
    hrefs[k]=a?a.href:null;
  });
  return {
    copyBtn: !!document.evaluate("//*[contains(text(),'Copy link to share')]",document,null,9,null).singleNodeValue,
    shareHeading: !!document.evaluate("//*[contains(text(),'Share this post')]",document,null,9,null).singleNodeValue,
    platformLinks: hrefs,
  };
});
console.log(JSON.stringify(r,null,2));
await p.screenshot({path:'share-row.png'});
await b.close();
