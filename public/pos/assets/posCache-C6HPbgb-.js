import{g as D,b as k}from"./index-5X1HHRfR.js";import{o as it}from"./vendor-qKbVCTru.js";const at="/color-paychat-logo-main.svg",K={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},ct=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},X=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},lt=()=>ct(X("tenant_info"),{}),c=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),w=t=>c(t).replace(/`/g,"&#096;"),A=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),u=t=>Number(t||0).toFixed(2),I=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},Z=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},tt=(t="80mm")=>K[t]||K["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},S=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},pt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},ut=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),j=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(ut)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=j(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=j(r,e+1,o);if(s.length)return s}return[]},dt=(t={})=>p(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),Q=(t={})=>S(t.quantity,t.qty,t.pivot?.quantity,1)||1,et=(t={})=>{const e=Q(t),o=p(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=p(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},mt=(t={})=>{const e=p(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):et(t)*Q(t)},gt=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return pt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,j(t))},ht=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},J=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=p(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},ft=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},yt=t=>{if(!t)return"";const e=ft(t),o=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const r=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${w(r)}" alt="Invoice QR" />`}const n=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return n?.[1]?`<img class="qr-image" src="${w(n[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${w(e)}" alt="Invoice QR" />`:`<div class="qr-url">${c(e)}</div>`},bt=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=lt(),n=D(),r=o?.tenant||{},s=o?.branding||{},a=t.location||{},i=gt(t).map(f=>({name:dt(f),qty:Q(f),rate:et(f),total:mt(f)})),m=S(t.subtotal,t.totals?.subtotal,i.reduce((f,x)=>f+x.rate*x.qty,0)),l=S(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),d=S(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),g=S(t.total,t.grand_total,t.totals?.grand_total,m+d-l);return{shopName:p(e.shopName,a.tenant?.name,t.tenant?.name,s.company_name,r.name,X("tenant_slug"),"PayChat POS"),shopPhone:p(e.shopPhone,a.phone,s.phone,r.phone),shopAddress:p(e.shopAddress,a.address,s.address,r.address),shopLogoUrl:p(e.shopLogoUrl,a.logo,a.tenant?.logo,t.tenant?.logo,s.logo,r.logo),locationName:p(a.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,at),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,t.invoice?.number,t.invoice?.invoice_no,t.invoice?.offline_invoice_number,t.offline_invoice_number,t.local_invoice_no),orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:J(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:J(t.batch_codes,t.batchCodes),items:i,subtotal:m,discount:l,tax:d,grandTotal:g,paidAmount:S(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,g),paymentMethod:ht(t),invoiceUrl:p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,t.invoice?.url),invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},_t=(t,e={})=>{const o=e.paperSize||"80mm",n=tt(o),r=o==="58mm",s=e.agentPdf===!0,a=Array.isArray(t.items)?t.items:[],i=Array.isArray(t.kotCodes)?t.kotCodes:[],m=Array.isArray(t.batchCodes)?t.batchCodes:[],l=D(),g=!(t.simpleBilling??l.simpleBilling),f=t.billingLabel||l.billingLabel||"Order",x=yt(t.invoiceQr||t.reviewQr),H=!x&&t.invoiceUrl?`<div class="qr-url">${c(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
      max-height: ${r?"54px":"74px"};
      object-fit: contain;
      margin: 0 auto 4px;
    }
    .paychat-logo {
      display: block;
      max-width: ${n.paychatLogoWidth};
      max-height: ${r?"20px":"26px"};
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
    .pdf-totals {
      width: 100%;
      table-layout: fixed;
      border-collapse: collapse;
    }
    .pdf-totals td:first-child {
      width: 58%;
      padding-right: 4px;
    }
    .pdf-totals .pdf-total-value {
      width: 42%;
      text-align: right;
      overflow-wrap: anywhere;
    }
    .pdf-totals .grand td {
      border-top: 1px dashed #000;
      padding-top: 5px;
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
      ${t.shopLogoUrl?`<img class="shop-logo" src="${w(t.shopLogoUrl)}" alt="${w(t.shopName)}" />`:""}
      <div class="title">${c(t.shopName)}</div>
      ${t.locationName?`<div class="muted">${c(t.locationName)}</div>`:""}
      ${t.shopAddress?`<div class="muted">${c(t.shopAddress)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${c(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${t.invoiceNo?`<div class="bill-no">BILL NO: ${c(t.invoiceNo)}</div>`:""}
    <table>
	      ${t.orderNo?`<tr><td>${c(f)}</td><td class="right">${c(t.orderNo)}</td></tr>`:""}
      <tr><td>Date</td><td class="right">${c(Z(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${c(t.orderType)}</td></tr>`:""}
	      ${g&&t.tableName?`<tr><td>Table</td><td class="right">${c(t.tableName)}</td></tr>`:""}
	      ${g&&t.guestCount?`<tr><td>Guests</td><td class="right">${c(t.guestCount)}</td></tr>`:""}
	      ${g&&t.tokenNo?`<tr><td>Token</td><td class="right">${c(t.tokenNo)}</td></tr>`:""}
	      ${g&&i.length?`<tr><td>KOT</td><td class="right">${c(i.join(", "))}</td></tr>`:""}
	      ${g&&m.length?`<tr><td>Batch</td><td class="right">${c(m.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${r?`
      <div>
        ${a.length?a.map(y=>`
          <div class="item-block">
            <div class="item-name">${c(y.name)}</div>
            <div class="item-meta">
              <span>${c(I(y.qty))} x ${c(u(y.rate))}</span>
              <strong>${c(u(y.total))}</strong>
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
          ${a.length?a.map(y=>`
            <tr>
              <td class="item-name">${c(y.name)}</td>
              <td class="right">${c(I(y.qty))}</td>
              <td class="right">${c(u(y.rate))}</td>
              <td class="right">${c(u(y.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${s?`
      <table class="pdf-totals">
        <tbody>
          <tr><td>Subtotal</td><td class="pdf-total-value">${c(u(t.subtotal))}</td></tr>
          ${t.discount?`<tr><td>Discount</td><td class="pdf-total-value">-${c(u(t.discount))}</td></tr>`:""}
          ${t.tax?`<tr><td>Tax/GST</td><td class="pdf-total-value">${c(u(t.tax))}</td></tr>`:""}
          <tr class="grand"><td>TOTAL</td><td class="pdf-total-value">${c(u(t.grandTotal))}</td></tr>
          ${t.paidAmount?`<tr><td>Paid</td><td class="pdf-total-value">${c(u(t.paidAmount))}</td></tr>`:""}
          ${t.paymentMethod?`<tr><td>Payment</td><td class="pdf-total-value">${c(t.paymentMethod)}</td></tr>`:""}
        </tbody>
      </table>
    `:`
      <div class="total-row"><span>Subtotal</span><span>${c(u(t.subtotal))}</span></div>
      ${t.discount?`<div class="total-row"><span>Discount</span><span>-${c(u(t.discount))}</span></div>`:""}
      ${t.tax?`<div class="total-row"><span>Tax/GST</span><span>${c(u(t.tax))}</span></div>`:""}
      <div class="total-row grand"><span>TOTAL</span><span>${c(u(t.grandTotal))}</span></div>
      ${t.paidAmount?`<div class="total-row"><span>Paid</span><span>${c(u(t.paidAmount))}</span></div>`:""}
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${c(t.paymentMethod)}</span></div>`:""}
    `}
    ${x||H?`
      <div class="line"></div>
      <div class="qr-wrap">
        <div class="muted">Scan QR for invoice/review</div>
        ${x||H}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted">
      ${t.paychatLogoUrl?`<img class="paychat-logo" src="${w(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},v=(t,e="-")=>`${e.repeat(t)}
`,b=(t,e)=>{const o=A(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},h=(t,e,o)=>{const n=A(e),r=Math.max(1,o-n.length-1),s=A(t).slice(0,r),a=Math.max(1,o-s.length-n.length);return`${s}${" ".repeat(a)}${n}
`},G=(t,e)=>{const o=A(t).split(/\s+/).filter(Boolean).flatMap(s=>s.length<=e?[s]:s.match(new RegExp(`.{1,${e}}`,"g"))||[s]),n=[];let r="";return o.forEach(s=>{if(!r){r=s;return}(r+" "+s).length<=e?r+=` ${s}`:(n.push(r),r=s.slice(0,e))}),r&&n.push(r),n.length?n:[""]},vt=(t,e)=>{const o=G(t.name,e),n=`${I(t.qty)} x ${u(t.rate)}`;return[...o.map(r=>`${r}
`),h(n,u(t.total),e)].join("")},$t=(t,e)=>{const s=e-5-9-10,a=G(t.name,s),i=`${a[0].padEnd(s)}${I(t.qty).padStart(5)}${u(t.rate).padStart(9)}${u(t.total).padStart(10)}
`,m=a.slice(1).map(l=>`${l}
`).join("");return i+m},nt=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=tt(o),r=o==="58mm",s=Array.isArray(t.items)?t.items:[],a=Array.isArray(t.kotCodes)?t.kotCodes:[],i=Array.isArray(t.batchCodes)?t.batchCodes:[],m=D(),l=t.simpleBilling??m.simpleBilling,d=t.billingLabel||m.billingLabel||"Order",g=r?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`;return[b(t.shopName,n),t.locationName?b(t.locationName,n):"",t.shopPhone?b(`Phone: ${t.shopPhone}`,n):"",t.shopAddress?b(t.shopAddress,n):"",v(n),t.invoiceNo?b(`BILL NO: ${t.invoiceNo}`,n):"",t.orderNo?h(d,t.orderNo,n):"",h("Date",Z(t.dateTime),n),t.orderType?h("Type",t.orderType,n):"",!l&&t.tableName?h("Table",t.tableName,n):"",!l&&t.guestCount?h("Guests",t.guestCount,n):"",!l&&t.tokenNo?h("Token",t.tokenNo,n):"",!l&&a.length?h("KOT",a.join(","),n):"",!l&&i.length?h("Batch",i.join(","),n):"",v(n),g,g?v(n):"",s.length?s.map(f=>r?vt(f,n):$t(f,n)).join(""):b("No items",n),v(n),h("Subtotal",u(t.subtotal),n),t.discount?h("Discount",`-${u(t.discount)}`,n):"",t.tax?h("Tax/GST",u(t.tax),n):"",v(n),h("TOTAL",u(t.grandTotal),n),t.paidAmount?h("Paid",u(t.paidAmount),n):"",t.paymentMethod?h("Payment",t.paymentMethod,n):"",t.invoiceUrl?`${v(n)}${b("Invoice/review link",n)}${G(t.invoiceUrl,n).map(f=>`${A(f)}
`).join("")}`:"",v(n),b("Thank you",n),b("Powered by PayChat",n)].join("")},Ft=nt,ot="paychat_print_agent_settings",B={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1},xt=8e3,V=12e3,St=["invoice_url","invoiceUrl","review_url","reviewUrl"],wt=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},F=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),Tt=t=>t==="80mm"?"80mm":"58mm",kt=t=>t==="pdf"?"pdf":"escpos",_=(t={})=>({...B,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||B.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:Tt(t?.paperSize),printMode:kt(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout}),$=()=>typeof localStorage>"u"?{...B}:_(wt(localStorage.getItem(ot),{})),Nt=(t={})=>{const e=_({...$(),...t});try{localStorage.setItem(ot,JSON.stringify(e))}catch{}return e},R=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},z=(t,e={},o={})=>{const n=_(e),r=new URL(t,`${n.agentUrl}/`),s={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(s).forEach(([a,i])=>{i!=null&&i!==""&&r.searchParams.set(a,String(i))}),r.toString()},U=async(t,e={},o=xt)=>{const n=new AbortController,r=setTimeout(()=>n.abort(),o);try{const s=await fetch(t,{...e,signal:n.signal}),i=(s.headers.get("content-type")||"").includes("application/json")?await s.json().catch(()=>null):await s.text().catch(()=>"");if(!s.ok)throw R(i?.message||i?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return i}catch(s){throw s?.name==="AbortError"?R("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",s):s?.code?s:R("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",s)}finally{clearTimeout(r)}},At=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Lt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),W=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Lt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=W(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=W(r,e+1,o);if(s.length)return s}return[]},N=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},T=(...t)=>{for(const e of t){const o=F(e).trim();if(o)return o}return""},O=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return At(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,W(t))},q=(t={})=>T(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),L=(t={})=>N(t.quantity,t.qty,t.pivot?.quantity,1)||1,E=(t={})=>{const e=L(t),o=T(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=T(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},rt=(t={})=>{const e=T(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):E(t)*L(t)},Pt=(t=[])=>t.map(e=>({...e,product_name:q(e),name:q(e),quantity:L(e),qty:L(e),rate:E(e),price:E(e),total:rt(e)})),It=(t,e)=>{const o=F(t);if(o.length<=e)return[o];const n=[];for(let r=0;r<o.length;r+=e)n.push(o.slice(r,r+e));return n},qt=(t,e)=>{const o=e==="80mm"?48:32;return F(t).split(/\r?\n/).flatMap(n=>It(n,o)).join(`
`)},Et=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=O(t);return n.length?n.map(r=>{const s=q(r),a=L(r),i=E(r),l=rt(r).toFixed(2),d=`${a} x ${i.toFixed(2)}`,g=Math.max(1,o-d.length-l.length);return`${s}
${d}${" ".repeat(g)}${l}`}).join(`
`):""},Ct=(t,e,o)=>{const n=O(e);return!n.length||n.some(s=>{const a=q(s);return a&&t.includes(a.slice(0,Math.min(a.length,12)))})?t:`${t}
${Et(e,o)}`},zt=(t,e)=>{if(/total/i.test(t))return t;const o=N(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,O(e).reduce((n,r)=>{const s=N(r.quantity,r.qty,1)||1,a=N(r.rate,r.price,r.unit_price);return n+N(r.total,r.line_total,r.amount,s*a)},0));return`${t}
TOTAL ${o.toFixed(2)}`},Ut=(t={},e={})=>{for(const o of St){const n=T(t[o],e[o]);if(n)return n}return T(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},Ot=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},st=(t={},e={},o=$())=>{const n=_(o),r=n.paperSize,s={...t||{},items:Pt(O(t||{}))},a=bt(s,e||{});let i=nt(a,{paperSize:r});const m=_t(a,{paperSize:r,agentPdf:n.printMode==="pdf"});typeof i!="string"&&(i=String(i??"")),i=Ct(i,s,r),i=zt(i,s),i=qt(i,r),i.length>V&&(i=`${i.slice(0,V)}
--- Receipt truncated ---`),i=i.replace(/\n*$/,`


`);const l=Ut(t,a),d={text:i,html:m,print_mode:n.printMode};return l&&Ot(l)&&(d.qr={data:l,size:6,error_correction:"M"}),d},Rt=async(t=$())=>{const e=_(t);return U(z("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Mt=async(t=$())=>{const e=_(t),o=await U(z("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},jt=async(t=$())=>{const e=_(t);return U(z("/test-print",e),{method:"POST"})},Bt=async(t={},e={})=>{const o=_(e.settings||$()),n=st(t,e.context||{},o);return U(z("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},Ht={getSettings:$,saveSettings:Nt,checkHealth:Rt,getPrinters:Mt,testPrint:jt,printReceipt:Bt,buildSafeAgentReceiptPayload:st},Kt={list(t={}){return k.get("/upi-profiles",{params:t})},create(t){return k.post("/upi-profiles",t)},update(t,e){return k.patch(`/upi-profiles/${t}`,e)},deactivate(t){return k.delete(`/upi-profiles/${t}`)},setDefault(t){return k.patch(`/upi-profiles/${t}/default`)}},C="paychat_pos_wake_lock_enabled",Y=()=>{try{return localStorage.getItem(C)==="true"}catch{return!1}},Jt=t=>{try{return t?(localStorage.setItem(C,"true"),!0):(localStorage.removeItem(C),!1)}catch{return!1}},Wt=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},Vt=()=>{let t=null,e=!1,o=!1,n=0;const r=async()=>{try{await t?.release?.()}catch(d){console.warn("POS wake lock release failed:",d)}finally{t=null}},s=()=>{const d=Wt();return d.supported?!0:(o||(console.warn(`POS wake lock unavailable: ${d.reason}`),o=!0),!1)},a=async()=>{const d=Date.now();if(!(e||t||!Y()||!s()||document.visibilityState!=="visible")&&!(d-n<750)){n=d;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(g){console.warn("POS wake lock failed:",g)}}},i=()=>{a()},m=()=>{document.visibilityState==="visible"?a():r()},l=d=>{d.key===C&&(Y()?a():r())};return document.addEventListener("visibilitychange",m),document.addEventListener("pointerdown",i,{passive:!0}),document.addEventListener("touchstart",i,{passive:!0}),document.addEventListener("click",i,{passive:!0}),window.addEventListener("storage",l),a(),()=>{e=!0,document.removeEventListener("visibilitychange",m),document.removeEventListener("pointerdown",i),document.removeEventListener("touchstart",i),document.removeEventListener("click",i),window.removeEventListener("storage",l),r()}},Dt="paychat-pos",P="cache",M=it(Dt,1,{upgrade(t){t.createObjectStore(P)}}),Yt={async set(t,e){await(await M).put(P,e,t)},async get(t){return await(await M).get(P,t)},async clear(){await(await M).clear(P)}};export{_t as a,Ft as b,Yt as c,Jt as d,Wt as e,Y as g,bt as n,Ht as p,Vt as s,Kt as u};
