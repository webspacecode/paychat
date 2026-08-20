const m=(e="")=>/^(https?:|data:image\/|blob:)/i.test(String(e||"")),o=`data:image/svg+xml;base64,${btoa(`
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="240" viewBox="0 0 320 240">
  <rect width="320" height="240" rx="28" fill="#F3F4F6"/>
  <circle cx="160" cy="102" r="34" fill="#D1D5DB"/>
  <rect x="86" y="154" width="148" height="18" rx="9" fill="#D1D5DB"/>
  <rect x="118" y="184" width="84" height="12" rx="6" fill="#E5E7EB"/>
</svg>
`)}`,h=(e="")=>String(e||"").replace(/\\/g,"").replace(/"/g,"").replace(/^\/+/,"").trim(),u=(e="")=>{const a=h(e),t=sessionStorage.getItem("paychat_kiosk_tenant_id")||localStorage.getItem("tenant_id"),r=t&&a&&!a.startsWith("tenants/")?`tenants/${t}/${a}`:a;return r?`${window.location.origin}/storage/${r}`:""},f=(e,a)=>{const t=String(e||"").trim();return t?m(t)||t.startsWith("/")?t:a(t):""},d=(e={},a={})=>{const t=a.fallback||o,r=a.localPathResolver||u,i=Array.isArray(e.images)?e.images:[],c=[e.resolved_image_url,e.resolvedImageUrl,e.image_url,e.imageUrl,e.image_path,e.image,...i.flatMap((n={})=>[n.url,n.image_url,n.imageUrl,n.image_path])],s=[];for(const n of c){const l=f(n,r);l&&!s.includes(l)&&s.push(l)}return t&&!s.includes(t)&&s.push(t),s},x=(e={},a={})=>d(e,a)[0]||a.fallback||o,I=(e,a={},t={})=>{const r=e?.target;if(!r)return;const i=d(a,t),c=r.currentSrc||r.src||"",s=i.findIndex(g=>g===c||c.endsWith(g)),n=Number(r.dataset.fallbackIndex||s||0),l=i[n+1]||t.fallback||o;!l||l===c||(r.dataset.fallbackIndex=String(n+1),r.src=l)};export{I as a,x as r};
