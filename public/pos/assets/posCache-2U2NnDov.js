import{g as R,b as k}from"./index-jJyWKqXN.js";import{o as Z}from"./vendor-qKbVCTru.js";const tt="/color-paychat-logo-main.svg",j={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},et=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},F=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},nt=()=>et(F("tenant_info"),{}),c=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),N=t=>c(t).replace(/`/g,"&#096;"),T=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),d=t=>Number(t||0).toFixed(2),L=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},H=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},J=(t="80mm")=>j[t]||j["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},x=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},ot=(t={})=>Array.isArray(t.items)?t.items:[],it=(t={})=>p(t.product?.name,t.product_name,t.name,t.title,"Item"),O=(t={})=>x(t.quantity,t.qty,1)||1,K=(t={})=>{const e=O(t),o=p(t.rate,t.price,t.unit_price);if(o!=="")return Number(o||0);const n=p(t.total,t.line_total,t.amount);return Number(n||0)/e},rt=(t={})=>{const e=p(t.total,t.line_total,t.amount);return e!==""?Number(e||0):K(t)*O(t)},st=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},D=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=p(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},at=(t,e)=>{if(!t)return"";const o=String(t);return o.trim().startsWith("<svg")||o.trim().startsWith("<img")?`<div class="qr-embed">${o}</div>`:/^(data:image\/|https?:\/\/|\/)/i.test(o)?`<img class="qr-image" src="${N(o)}" alt="Invoice QR" />`:`<div class="qr-url">${c(o)}</div>`},ct=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=nt(),n=R(),i=o?.tenant||{},r=o?.branding||{},s=t.location||{},a=ot(t).map(h=>({name:it(h),qty:O(h),rate:K(h),total:rt(h)})),m=x(t.subtotal,t.totals?.subtotal,a.reduce((h,w)=>h+w.rate*w.qty,0)),u=x(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),l=x(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),f=x(t.total,t.grand_total,t.totals?.grand_total,m+l-u);return{shopName:p(e.shopName,s.tenant?.name,t.tenant?.name,r.company_name,i.name,F("tenant_slug"),"PayChat POS"),shopPhone:p(e.shopPhone,s.phone,r.phone,i.phone),shopAddress:p(e.shopAddress,s.address,r.address,i.address),shopLogoUrl:p(e.shopLogoUrl,s.logo,s.tenant?.logo,t.tenant?.logo,r.logo,i.logo),locationName:p(s.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,tt),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,t.invoice?.number,t.invoice?.invoice_no,t.invoice?.offline_invoice_number,t.offline_invoice_number,t.local_invoice_no),orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:D(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:D(t.batch_codes,t.batchCodes),items:a,subtotal:m,discount:u,tax:l,grandTotal:f,paidAmount:x(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,f),paymentMethod:st(t),invoiceUrl:p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,t.invoice?.url),invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},lt=(t,e={})=>{const o=e.paperSize||"80mm",n=J(o),i=o==="58mm",r=Array.isArray(t.items)?t.items:[],s=Array.isArray(t.kotCodes)?t.kotCodes:[],a=Array.isArray(t.batchCodes)?t.batchCodes:[],m=R(),l=!(t.simpleBilling??m.simpleBilling),f=t.billingLabel||m.billingLabel||"Order",h=at(t.invoiceQr||t.reviewQr,n.qrSize),w=!h&&t.invoiceUrl?`<div class="qr-url">${c(t.invoiceUrl)}</div>`:"";return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Thermal Bill</title>
  <style>
    @page { size: ${n.width} auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      background: #fff;
      color: #000;
      font-family: "Courier New", monospace;
      font-size: ${n.fontSize};
      line-height: 1.28;
    }
    .receipt {
      width: ${n.width};
      padding: ${n.padding};
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { font-size: 0.88em; }
    .title {
      font-size: ${n.titleSize};
      font-weight: 700;
      text-transform: uppercase;
      word-break: break-word;
    }
    .shop-logo {
      display: block;
      max-width: ${n.logoMaxWidth};
      max-height: ${i?"54px":"74px"};
      object-fit: contain;
      margin: 0 auto 4px;
    }
    .paychat-logo {
      display: block;
      max-width: ${n.paychatLogoWidth};
      max-height: ${i?"20px":"26px"};
      object-fit: contain;
      margin: 2px auto 1px;
    }
    .bill-no {
      font-size: 1.15em;
      font-weight: 700;
      text-align: center;
      margin: 3px 0;
      word-break: break-word;
    }
    .line {
      border-top: 1px dashed #000;
      margin: 6px 0;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    td, th {
      padding: 2px 0;
      vertical-align: top;
    }
    th {
      border-bottom: 1px dashed #000;
      font-weight: 700;
      text-align: left;
    }
    .item-name {
      word-break: break-word;
    }
    .item-block {
      padding: 3px 0;
      border-bottom: 1px dotted #999;
    }
    .item-meta,
    .total-row {
      display: flex;
      justify-content: space-between;
      gap: 6px;
    }
    .grand {
      border-top: 1px dashed #000;
      padding-top: 5px;
      margin-top: 4px;
      font-weight: 700;
      font-size: 1.12em;
    }
    .qr-wrap {
      text-align: center;
      margin-top: 6px;
    }
    .qr-image,
    .qr-embed svg,
    .qr-embed img {
      width: ${n.qrSize};
      height: ${n.qrSize};
      max-width: ${n.qrSize};
      max-height: ${n.qrSize};
      object-fit: contain;
    }
    .qr-url {
      font-size: 0.86em;
      word-break: break-all;
      margin-top: 3px;
    }
  </style>
</head>
<body>
  <div class="receipt">
    <div class="center">
      ${t.shopLogoUrl?`<img class="shop-logo" src="${N(t.shopLogoUrl)}" alt="${N(t.shopName)}" />`:""}
      <div class="title">${c(t.shopName)}</div>
      ${t.locationName?`<div class="muted">${c(t.locationName)}</div>`:""}
      ${t.shopAddress?`<div class="muted">${c(t.shopAddress)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${c(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${t.invoiceNo?`<div class="bill-no">BILL NO: ${c(t.invoiceNo)}</div>`:""}
    <table>
	      ${t.orderNo?`<tr><td>${c(f)}</td><td class="right">${c(t.orderNo)}</td></tr>`:""}
      <tr><td>Date</td><td class="right">${c(H(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${c(t.orderType)}</td></tr>`:""}
	      ${l&&t.tableName?`<tr><td>Table</td><td class="right">${c(t.tableName)}</td></tr>`:""}
	      ${l&&t.guestCount?`<tr><td>Guests</td><td class="right">${c(t.guestCount)}</td></tr>`:""}
	      ${l&&t.tokenNo?`<tr><td>Token</td><td class="right">${c(t.tokenNo)}</td></tr>`:""}
	      ${l&&s.length?`<tr><td>KOT</td><td class="right">${c(s.join(", "))}</td></tr>`:""}
	      ${l&&a.length?`<tr><td>Batch</td><td class="right">${c(a.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${i?`
      <div>
        ${r.length?r.map(y=>`
          <div class="item-block">
            <div class="item-name">${c(y.name)}</div>
            <div class="item-meta">
              <span>${c(L(y.qty))} x ${c(d(y.rate))}</span>
              <strong>${c(d(y.total))}</strong>
            </div>
          </div>
        `).join(""):'<div class="center">No items</div>'}
      </div>
    `:`
      <table>
        <thead>
          <tr>
            <th>Item</th>
            <th class="right">Qty</th>
            <th class="right">Rate</th>
            <th class="right">Amt</th>
          </tr>
        </thead>
        <tbody>
          ${r.length?r.map(y=>`
            <tr>
              <td class="item-name">${c(y.name)}</td>
              <td class="right">${c(L(y.qty))}</td>
              <td class="right">${c(d(y.rate))}</td>
              <td class="right">${c(d(y.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    <div class="total-row"><span>Subtotal</span><span>${c(d(t.subtotal))}</span></div>
    ${t.discount?`<div class="total-row"><span>Discount</span><span>-${c(d(t.discount))}</span></div>`:""}
    ${t.tax?`<div class="total-row"><span>Tax/GST</span><span>${c(d(t.tax))}</span></div>`:""}
    <div class="total-row grand"><span>TOTAL</span><span>${c(d(t.grandTotal))}</span></div>
    ${t.paidAmount?`<div class="total-row"><span>Paid</span><span>${c(d(t.paidAmount))}</span></div>`:""}
    ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${c(t.paymentMethod)}</span></div>`:""}
    ${h||w?`
      <div class="line"></div>
      <div class="qr-wrap">
        <div class="muted">Scan QR for invoice/review</div>
        ${h||w}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted">
      ${t.paychatLogoUrl?`<img class="paychat-logo" src="${N(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},_=(t,e="-")=>`${e.repeat(t)}
`,b=(t,e)=>{const o=T(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},g=(t,e,o)=>{const n=T(e),i=Math.max(1,o-n.length-1),r=T(t).slice(0,i),s=Math.max(1,o-r.length-n.length);return`${r}${" ".repeat(s)}${n}
`},M=(t,e)=>{const o=T(t).split(/\s+/).filter(Boolean).flatMap(r=>r.length<=e?[r]:r.match(new RegExp(`.{1,${e}}`,"g"))||[r]),n=[];let i="";return o.forEach(r=>{if(!i){i=r;return}(i+" "+r).length<=e?i+=` ${r}`:(n.push(i),i=r.slice(0,e))}),i&&n.push(i),n.length?n:[""]},pt=(t,e)=>{const o=M(t.name,e),n=`${L(t.qty)} x ${d(t.rate)}`;return[...o.map(i=>`${i}
`),g(n,d(t.total),e)].join("")},ut=(t,e)=>{const r=e-5-9-10,s=M(t.name,r),a=`${s[0].padEnd(r)}${L(t.qty).padStart(5)}${d(t.rate).padStart(9)}${d(t.total).padStart(10)}
`,m=s.slice(1).map(u=>`${u}
`).join("");return a+m},V=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=J(o),i=o==="58mm",r=Array.isArray(t.items)?t.items:[],s=Array.isArray(t.kotCodes)?t.kotCodes:[],a=Array.isArray(t.batchCodes)?t.batchCodes:[],m=R(),u=t.simpleBilling??m.simpleBilling,l=t.billingLabel||m.billingLabel||"Order",f=i?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`;return[b(t.shopName,n),t.locationName?b(t.locationName,n):"",t.shopPhone?b(`Phone: ${t.shopPhone}`,n):"",t.shopAddress?b(t.shopAddress,n):"",_(n),t.invoiceNo?b(`BILL NO: ${t.invoiceNo}`,n):"",t.orderNo?g(l,t.orderNo,n):"",g("Date",H(t.dateTime),n),t.orderType?g("Type",t.orderType,n):"",!u&&t.tableName?g("Table",t.tableName,n):"",!u&&t.guestCount?g("Guests",t.guestCount,n):"",!u&&t.tokenNo?g("Token",t.tokenNo,n):"",!u&&s.length?g("KOT",s.join(","),n):"",!u&&a.length?g("Batch",a.join(","),n):"",_(n),f,f?_(n):"",r.length?r.map(h=>i?pt(h,n):ut(h,n)).join(""):b("No items",n),_(n),g("Subtotal",d(t.subtotal),n),t.discount?g("Discount",`-${d(t.discount)}`,n):"",t.tax?g("Tax/GST",d(t.tax),n):"",_(n),g("TOTAL",d(t.grandTotal),n),t.paidAmount?g("Paid",d(t.paidAmount),n):"",t.paymentMethod?g("Payment",t.paymentMethod,n):"",t.invoiceUrl?`${_(n)}${b("Invoice/review link",n)}${M(t.invoiceUrl,n).map(h=>`${T(h)}
`).join("")}`:"",_(n),b("Thank you",n),b("Powered by PayChat",n)].join("")},It=V,Y="paychat_print_agent_settings",U={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1},mt=8e3,Q=12e3,dt=["invoice_url","invoiceUrl","review_url","reviewUrl"],ht=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},W=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),gt=t=>t==="80mm"?"80mm":"58mm",ft=t=>t==="pdf"?"pdf":"escpos",v=(t={})=>({...U,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||U.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:gt(t?.paperSize),printMode:ft(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout}),S=()=>typeof localStorage>"u"?{...U}:v(ht(localStorage.getItem(Y),{})),yt=(t={})=>{const e=v({...S(),...t});try{localStorage.setItem(Y,JSON.stringify(e))}catch{}return e},q=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},C=(t,e={},o={})=>{const n=v(e),i=new URL(t,`${n.agentUrl}/`),r={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(r).forEach(([s,a])=>{a!=null&&a!==""&&i.searchParams.set(s,String(a))}),i.toString()},I=async(t,e={},o=mt)=>{const n=new AbortController,i=setTimeout(()=>n.abort(),o);try{const r=await fetch(t,{...e,signal:n.signal}),a=(r.headers.get("content-type")||"").includes("application/json")?await r.json().catch(()=>null):await r.text().catch(()=>"");if(!r.ok)throw q(a?.message||a?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return a}catch(r){throw r?.name==="AbortError"?q("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",r):r?.code?r:q("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",r)}finally{clearTimeout(i)}},B=(t={})=>Array.isArray(t.items)?t.items:Array.isArray(t.order_items)?t.order_items:Array.isArray(t.orderItems)?t.orderItems:Array.isArray(t.cart)?t.cart:[],$=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},P=(...t)=>{for(const e of t){const o=W(e).trim();if(o)return o}return""},bt=(t,e)=>{const o=W(t);if(o.length<=e)return[o];const n=[];for(let i=0;i<o.length;i+=e)n.push(o.slice(i,i+e));return n},vt=(t,e)=>{const o=e==="80mm"?48:32;return W(t).split(/\r?\n/).flatMap(n=>bt(n,o)).join(`
`)},_t=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=B(t);return n.length?n.map(i=>{const r=P(i.product?.name,i.product_name,i.name,i.title,"Item"),s=$(i.quantity,i.qty,1)||1,a=$(i.rate,i.price,i.unit_price),u=$(i.total,i.line_total,i.amount,s*a).toFixed(2),l=`${s} x ${a.toFixed(2)}`,f=Math.max(1,o-l.length-u.length);return`${r}
${l}${" ".repeat(f)}${u}`}).join(`
`):""},$t=(t,e,o)=>{const n=B(e);return!n.length||n.some(r=>{const s=P(r.product?.name,r.product_name,r.name,r.title);return s&&t.includes(s.slice(0,Math.min(s.length,12)))})?t:`${t}
${_t(e,o)}`},St=(t,e)=>{if(/total/i.test(t))return t;const o=$(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,B(e).reduce((n,i)=>{const r=$(i.quantity,i.qty,1)||1,s=$(i.rate,i.price,i.unit_price);return n+$(i.total,i.line_total,i.amount,r*s)},0));return`${t}
TOTAL ${o.toFixed(2)}`},xt=(t={},e={})=>{for(const o of dt){const n=P(t[o],e[o]);if(n)return n}return P(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},wt=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},X=(t={},e={},o=S())=>{const n=v(o),i=n.paperSize,r=ct(t||{},e||{});let s=V(r,{paperSize:i});const a=lt(r,{paperSize:i});typeof s!="string"&&(s=String(s??"")),s=$t(s,t,i),s=St(s,t),s=vt(s,i),s.length>Q&&(s=`${s.slice(0,Q)}
--- Receipt truncated ---`),s=s.replace(/\n*$/,`


`);const m=xt(t,r),u={text:s,html:a,print_mode:n.printMode};return m&&wt(m)&&(u.qr={data:m,size:6,error_correction:"M"}),u},kt=async(t=S())=>{const e=v(t);return I(C("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Tt=async(t=S())=>{const e=v(t),o=await I(C("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},Nt=async(t=S())=>{const e=v(t);return I(C("/test-print",e),{method:"POST"})},At=async(t={},e={})=>{const o=v(e.settings||S()),n=X(t,e.context||{},o);return I(C("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},qt={getSettings:S,saveSettings:yt,checkHealth:kt,getPrinters:Tt,testPrint:Nt,printReceipt:At,buildSafeAgentReceiptPayload:X},zt={list(t={}){return k.get("/upi-profiles",{params:t})},create(t){return k.post("/upi-profiles",t)},update(t,e){return k.patch(`/upi-profiles/${t}`,e)},deactivate(t){return k.delete(`/upi-profiles/${t}`)},setDefault(t){return k.patch(`/upi-profiles/${t}/default`)}},E="paychat_pos_wake_lock_enabled",G=()=>{try{return localStorage.getItem(E)==="true"}catch{return!1}},Ut=t=>{try{return t?(localStorage.setItem(E,"true"),!0):(localStorage.removeItem(E),!1)}catch{return!1}},Lt=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},Rt=()=>{let t=null,e=!1,o=!1,n=0;const i=async()=>{try{await t?.release?.()}catch(l){console.warn("POS wake lock release failed:",l)}finally{t=null}},r=()=>{const l=Lt();return l.supported?!0:(o||(console.warn(`POS wake lock unavailable: ${l.reason}`),o=!0),!1)},s=async()=>{const l=Date.now();if(!(e||t||!G()||!r()||document.visibilityState!=="visible")&&!(l-n<750)){n=l;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(f){console.warn("POS wake lock failed:",f)}}},a=()=>{s()},m=()=>{document.visibilityState==="visible"?s():i()},u=l=>{l.key===E&&(G()?s():i())};return document.addEventListener("visibilitychange",m),document.addEventListener("pointerdown",a,{passive:!0}),document.addEventListener("touchstart",a,{passive:!0}),document.addEventListener("click",a,{passive:!0}),window.addEventListener("storage",u),s(),()=>{e=!0,document.removeEventListener("visibilitychange",m),document.removeEventListener("pointerdown",a),document.removeEventListener("touchstart",a),document.removeEventListener("click",a),window.removeEventListener("storage",u),i()}},Pt="paychat-pos",A="cache",z=Z(Pt,1,{upgrade(t){t.createObjectStore(A)}}),Ot={async set(t,e){await(await z).put(A,e,t)},async get(t){return await(await z).get(A,t)},async clear(){await(await z).clear(A)}};export{lt as a,It as b,Ot as c,Ut as d,Lt as e,G as g,ct as n,qt as p,Rt as s,zt as u};
