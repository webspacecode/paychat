const c=(e="")=>/^(https?:|data:image\/|blob:)/i.test(String(e||"")),n=`data:image/svg+xml;base64,${btoa(`
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="240" viewBox="0 0 320 240">
  <rect width="320" height="240" rx="28" fill="#F3F4F6"/>
  <circle cx="160" cy="102" r="34" fill="#D1D5DB"/>
  <rect x="86" y="154" width="148" height="18" rx="9" fill="#D1D5DB"/>
  <rect x="118" y="184" width="84" height="12" rx="6" fill="#E5E7EB"/>
</svg>
`)}`,m=(e="")=>String(e||"").replace(/\\/g,"").replace(/"/g,"").replace(/^\/+/,"").trim(),h=(e="")=>{const a=m(e);return a?`${window.location.origin}/storage/${a}`:""},f=(e={},a={})=>{const r=a.fallback||n,o=a.localPathResolver||h,l=e.images?.[0]||{},s=[e.resolved_image_url,e.resolvedImageUrl,e.image_url,e.imageUrl,l.url,l.image_url,l.imageUrl,l.image_path,e.image_path,e.image].filter(Boolean);for(const g of s){const t=String(g||"").trim();if(!t)continue;if(c(t)||t.startsWith("/"))return t;const i=o(t);if(i)return i}return r};export{f as r};
