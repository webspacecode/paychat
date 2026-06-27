import{g as H,b as I}from"./index-O1mB1ikW.js";import{o as mt}from"./vendor-qKbVCTru.js";const ht="/color-paychat-logo-main.svg",tt={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},gt=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},rt=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},ft=()=>gt(rt("tenant_info"),{}),J=t=>N(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),st=t=>{const e=J(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},a=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),S=t=>a(t).replace(/`/g,"&#096;"),N=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),d=t=>Number(t||0).toFixed(2),C=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},it=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},at=(t="80mm")=>tt[t]||tt["80mm"],u=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},w=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},yt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},bt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),G=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(bt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=G(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=G(r,e+1,o);if(s.length)return s}return[]},vt=(t={})=>u(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),V=(t={})=>w(t.quantity,t.qty,t.pivot?.quantity,1)||1,ct=(t={})=>{const e=V(t),o=u(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=u(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},_t=(t={})=>{const e=u(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):ct(t)*V(t)},$t=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return yt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,G(t))},xt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return u(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},et=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=u(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},kt=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},wt=t=>{if(!t)return"";const e=kt(t),o=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const r=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${S(r)}" alt="Invoice QR" />`}const n=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return n?.[1]?`<img class="qr-image" src="${S(n[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${S(e)}" alt="Invoice QR" />`:`<div class="qr-url">${a(e)}</div>`},St=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=ft(),n=H(),r=o?.tenant||{},s=o?.branding||{},i=t.location||{},c=$t(t).map(b=>({name:vt(b),qty:V(b),rate:ct(b),total:_t(b)})),l=w(t.subtotal,t.totals?.subtotal,c.reduce((b,f)=>b+f.rate*f.qty,0)),m=w(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),p=w(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),h=w(t.total,t.grand_total,t.totals?.grand_total,l+p-m);return{shopName:u(e.shopName,i.tenant?.name,t.tenant?.name,s.company_name,r.name,rt("tenant_slug"),"PayChat POS"),shopPhone:u(e.shopPhone,i.phone,s.phone,r.phone),shopAddress:u(e.shopAddress,i.address,s.address,r.address),shopLogoUrl:u(e.shopLogoUrl,i.logo,i.tenant?.logo,t.tenant?.logo,s.logo,r.logo),locationName:u(i.name,t.location_name),paychatLogoUrl:u(e.paychatLogoUrl,t.paychat_logo_url,ht),invoiceNo:u(e.invoiceNo,t.invoice_no,t.invoiceNo,t.invoice?.number,t.invoice?.invoice_no,t.invoice?.offline_invoice_number,t.offline_invoice_number,t.local_invoice_no),orderNo:u(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:u(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:u(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:u(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:u(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:u(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:et(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:et(t.batch_codes,t.batchCodes),items:c,subtotal:l,discount:m,tax:p,grandTotal:h,paidAmount:w(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,h),paymentMethod:xt(t),invoiceUrl:u(e.invoiceUrl,t.invoice_url,t.invoiceUrl,t.invoice?.url),invoiceQr:u(e.invoiceQr,t.invoice_qr,t.invoiceQr,t.qr),reviewQr:u(e.reviewQr,t.review_qr,t.reviewQr),notes:u(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},Nt=(t,e={})=>{const o=e.paperSize||"80mm",n=at(o),r=o==="58mm",s=e.agentPdf===!0,i=e.customPrintInvoice===!0,c=e.hideInvoiceQr===!0,l=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],p=Array.isArray(t.batchCodes)?t.batchCodes:[],h=H(),f=!(t.simpleBilling??h.simpleBilling),Q=t.billingLabel||h.billingLabel||"Order",A=i?J(t.shopName):t.shopName,E=i?st(t.shopAddress):t.shopAddress,B=i?r?"48px":"64px":n.paychatLogoWidth,v=c?"":wt(t.invoiceQr||t.reviewQr),Z=t.invoiceUrl&&(c||!v)?`<div class="qr-url">${a(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
      line-height: ${i?"1.16":"1.28"};
    }
    .receipt {
      width: ${n.width};
      padding: ${i?"4px 6px":n.padding};
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { font-size: 0.88em; }
    .title {
      font-size: ${n.titleSize};
      font-weight: 800;
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
      max-width: ${B};
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
      margin: ${i?"3px 0":"6px 0"};
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    td, th {
      padding: ${i?"1px 0":"2px 0"};
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
      padding: ${i?"2px 0":"3px 0"};
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
      font-weight: 900;
      font-size: 1.12em;
    }
    .top-token {
      border-bottom: 1px dashed #000;
      font-size: ${r?"1.55em":"1.75em"};
      font-weight: 900;
      margin-bottom: ${i?"3px":"6px"};
      padding-bottom: ${i?"3px":"6px"};
      text-align: center;
      word-break: break-word;
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
    ${i&&t.tokenNo?`<div class="top-token">TOKEN ${a(t.tokenNo)}</div>`:""}
    <div class="center">
      ${!i&&t.shopLogoUrl?`<img class="shop-logo" src="${S(t.shopLogoUrl)}" alt="${S(A)}" />`:""}
      <div class="title">${a(A)}</div>
      ${t.locationName?`<div class="muted">${a(t.locationName)}</div>`:""}
      ${E?`<div class="muted">${a(E)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${a(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${t.invoiceNo?`<div class="bill-no">BILL NO: ${a(t.invoiceNo)}</div>`:""}
    <table>
	      ${t.orderNo?`<tr><td>${a(Q)}</td><td class="right">${a(t.orderNo)}</td></tr>`:""}
      <tr><td>Date</td><td class="right">${a(it(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${a(t.orderType)}</td></tr>`:""}
	      ${f&&t.tableName?`<tr><td>Table</td><td class="right">${a(t.tableName)}</td></tr>`:""}
	      ${f&&t.guestCount?`<tr><td>Guests</td><td class="right">${a(t.guestCount)}</td></tr>`:""}
	      ${f&&t.tokenNo&&!i?`<tr><td>Token</td><td class="right">${a(t.tokenNo)}</td></tr>`:""}
	      ${f&&m.length?`<tr><td>KOT</td><td class="right">${a(m.join(", "))}</td></tr>`:""}
	      ${f&&p.length?`<tr><td>Batch</td><td class="right">${a(p.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${r?`
      <div>
        ${l.length?l.map(_=>`
          <div class="item-block">
            <div class="item-name">${a(_.name)}</div>
            <div class="item-meta">
              <span>${a(C(_.qty))} x ${a(d(_.rate))}</span>
              <strong>${a(d(_.total))}</strong>
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
          ${l.length?l.map(_=>`
            <tr>
              <td class="item-name">${a(_.name)}</td>
              <td class="right">${a(C(_.qty))}</td>
              <td class="right">${a(d(_.rate))}</td>
              <td class="right">${a(d(_.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${s?`
      <table class="pdf-totals">
        <tbody>
          <tr><td>Subtotal</td><td class="pdf-total-value">${a(d(t.subtotal))}</td></tr>
          ${t.discount?`<tr><td>Discount</td><td class="pdf-total-value">-${a(d(t.discount))}</td></tr>`:""}
          ${t.tax?`<tr><td>Tax/GST</td><td class="pdf-total-value">${a(d(t.tax))}</td></tr>`:""}
          <tr class="grand"><td>TOTAL</td><td class="pdf-total-value">${a(d(t.grandTotal))}</td></tr>
          ${t.paidAmount?`<tr><td>Paid</td><td class="pdf-total-value">${a(d(t.paidAmount))}</td></tr>`:""}
          ${t.paymentMethod?`<tr><td>Payment</td><td class="pdf-total-value">${a(t.paymentMethod)}</td></tr>`:""}
        </tbody>
      </table>
    `:`
      <div class="total-row"><span>Subtotal</span><span>${a(d(t.subtotal))}</span></div>
      ${t.discount?`<div class="total-row"><span>Discount</span><span>-${a(d(t.discount))}</span></div>`:""}
      ${t.tax?`<div class="total-row"><span>Tax/GST</span><span>${a(d(t.tax))}</span></div>`:""}
      <div class="total-row grand"><span>TOTAL</span><span>${a(d(t.grandTotal))}</span></div>
      ${t.paidAmount?`<div class="total-row"><span>Paid</span><span>${a(d(t.paidAmount))}</span></div>`:""}
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${a(t.paymentMethod)}</span></div>`:""}
    `}
    ${v||Z?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!c&&v?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${v||Z}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted">
      ${t.paychatLogoUrl&&!i?`<img class="paychat-logo" src="${S(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},$=(t,e="-")=>`${e.repeat(t)}
`,y=(t,e)=>{const o=N(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},g=(t,e,o)=>{const n=N(e),r=Math.max(1,o-n.length-1),s=N(t).slice(0,r),i=Math.max(1,o-s.length-n.length);return`${s}${" ".repeat(i)}${n}
`},Y=(t,e)=>{const o=N(t).split(/\s+/).filter(Boolean).flatMap(s=>s.length<=e?[s]:s.match(new RegExp(`.{1,${e}}`,"g"))||[s]),n=[];let r="";return o.forEach(s=>{if(!r){r=s;return}(r+" "+s).length<=e?r+=` ${s}`:(n.push(r),r=s.slice(0,e))}),r&&n.push(r),n.length?n:[""]},Tt=(t,e)=>{const o=Y(t.name,e),n=`${C(t.qty)} x ${d(t.rate)}`;return[...o.map(r=>`${r}
`),g(n,d(t.total),e)].join("")},At=(t,e)=>{const s=e-5-9-10,i=Y(t.name,s),c=`${i[0].padEnd(s)}${C(t.qty).padStart(5)}${d(t.rate).padStart(9)}${d(t.total).padStart(10)}
`,l=i.slice(1).map(m=>`${m}
`).join("");return c+l},lt=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=at(o),r=o==="58mm",s=e.customPrintInvoice===!0,i=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],l=Array.isArray(t.kotCodes)?t.kotCodes:[],m=Array.isArray(t.batchCodes)?t.batchCodes:[],p=H(),h=t.simpleBilling??p.simpleBilling,b=t.billingLabel||p.billingLabel||"Order",f=r?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,Q=s?J(t.shopName):t.shopName,A=s?st(t.shopAddress):t.shopAddress,E=s&&t.tokenNo?`${$(n)}${y(`TOKEN ${t.tokenNo}`,n)}${$(n)}`:"",B=t.invoiceUrl?`${$(n)}${y(i?"Invoice link":"Invoice/review link",n)}${Y(t.invoiceUrl,n).map(v=>`${N(v)}
`).join("")}`:"";return[E,y(Q,n),t.locationName?y(t.locationName,n):"",t.shopPhone?y(`Phone: ${t.shopPhone}`,n):"",A?y(A,n):"",$(n),t.invoiceNo?y(`BILL NO: ${t.invoiceNo}`,n):"",t.orderNo?g(b,t.orderNo,n):"",g("Date",it(t.dateTime),n),t.orderType?g("Type",t.orderType,n):"",!h&&t.tableName?g("Table",t.tableName,n):"",!h&&t.guestCount?g("Guests",t.guestCount,n):"",!h&&t.tokenNo&&!s?g("Token",t.tokenNo,n):"",!h&&l.length?g("KOT",l.join(","),n):"",!h&&m.length?g("Batch",m.join(","),n):"",$(n),f,f?$(n):"",c.length?c.map(v=>r?Tt(v,n):At(v,n)).join(""):y("No items",n),$(n),g("Subtotal",d(t.subtotal),n),t.discount?g("Discount",`-${d(t.discount)}`,n):"",t.tax?g("Tax/GST",d(t.tax),n):"",$(n),g("TOTAL",d(t.grandTotal),n),t.paidAmount?g("Paid",d(t.paidAmount),n):"",t.paymentMethod?g("Payment",t.paymentMethod,n):"",B,$(n),y("Thank you",n),y("Powered by PayChat",n)].join("")},Zt=lt,pt="paychat_print_agent_settings",F={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},It=8e3,nt=12e3,Pt=["invoice_url","invoiceUrl","review_url","reviewUrl"],Lt=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},X=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),Et=t=>t==="80mm"?"80mm":"58mm",qt=t=>t==="pdf"?"pdf":"escpos",x=(t={})=>({...F,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||F.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:Et(t?.paperSize),printMode:qt(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),k=()=>typeof localStorage>"u"?{...F}:x(Lt(localStorage.getItem(pt),{})),Ct=(t={})=>{const e=x({...k(),...t});try{localStorage.setItem(pt,JSON.stringify(e))}catch{}return e},W=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},R=(t,e={},o={})=>{const n=x(e),r=new URL(t,`${n.agentUrl}/`),s={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(s).forEach(([i,c])=>{c!=null&&c!==""&&r.searchParams.set(i,String(c))}),r.toString()},M=async(t,e={},o=It)=>{const n=new AbortController,r=setTimeout(()=>n.abort(),o);try{const s=await fetch(t,{...e,signal:n.signal}),c=(s.headers.get("content-type")||"").includes("application/json")?await s.json().catch(()=>null):await s.text().catch(()=>"");if(!s.ok)throw W(c?.message||c?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return c}catch(s){throw s?.name==="AbortError"?W("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",s):s?.code?s:W("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",s)}finally{clearTimeout(r)}},zt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Ot=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),K=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Ot)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=K(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=K(r,e+1,o);if(s.length)return s}return[]},P=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},T=(...t)=>{for(const e of t){const o=X(e).trim();if(o)return o}return""},j=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return zt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,K(t))},z=(t={})=>T(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),L=(t={})=>P(t.quantity,t.qty,t.pivot?.quantity,1)||1,O=(t={})=>{const e=L(t),o=T(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=T(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},ut=(t={})=>{const e=T(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):O(t)*L(t)},Ut=(t=[])=>t.map(e=>({...e,product_name:z(e),name:z(e),quantity:L(e),qty:L(e),rate:O(e),price:O(e),total:ut(e)})),Rt=(t,e)=>{const o=X(t);if(o.length<=e)return[o];const n=[];for(let r=0;r<o.length;r+=e)n.push(o.slice(r,r+e));return n},Mt=(t,e)=>{const o=e==="80mm"?48:32;return X(t).split(/\r?\n/).flatMap(n=>Rt(n,o)).join(`
`)},jt=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=j(t);return n.length?n.map(r=>{const s=z(r),i=L(r),c=O(r),m=ut(r).toFixed(2),p=`${i} x ${c.toFixed(2)}`,h=Math.max(1,o-p.length-m.length);return`${s}
${p}${" ".repeat(h)}${m}`}).join(`
`):""},Qt=(t,e,o)=>{const n=j(e);return!n.length||n.some(s=>{const i=z(s);return i&&t.includes(i.slice(0,Math.min(i.length,12)))})?t:`${t}
${jt(e,o)}`},Bt=(t,e)=>{if(/total/i.test(t))return t;const o=P(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,j(e).reduce((n,r)=>{const s=P(r.quantity,r.qty,1)||1,i=P(r.rate,r.price,r.unit_price);return n+P(r.total,r.line_total,r.amount,s*i)},0));return`${t}
TOTAL ${o.toFixed(2)}`},Wt=(t={},e={})=>{for(const o of Pt){const n=T(t[o],e[o]);if(n)return n}return T(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},Dt=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},dt=(t={},e={},o=k())=>{const n=x(o),r=n.paperSize,s={...t||{},items:Ut(j(t||{}))},i=St(s,e||{}),c={paperSize:r,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr};let l=lt(i,c);const m=Nt(i,{...c,agentPdf:n.printMode==="pdf"});typeof l!="string"&&(l=String(l??"")),l=Qt(l,s,r),l=Bt(l,s),l=Mt(l,r),l.length>nt&&(l=`${l.slice(0,nt)}
--- Receipt truncated ---`),l=l.replace(/\n*$/,`


`);const p=Wt(t,i),h={text:l,html:m,print_mode:n.printMode};return!n.hideInvoiceQr&&p&&Dt(p)&&(h.qr={data:p,size:6,error_correction:"M"}),h},Gt=async(t=k())=>{const e=x(t);return M(R("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Ft=async(t=k())=>{const e=x(t),o=await M(R("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},Kt=async(t=k())=>{const e=x(t);return M(R("/test-print",e),{method:"POST"})},Ht=async(t={},e={})=>{const o=x(e.settings||k()),n=dt(t,e.context||{},o);return M(R("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},te={getSettings:k,saveSettings:Ct,checkHealth:Gt,getPrinters:Ft,testPrint:Kt,printReceipt:Ht,buildSafeAgentReceiptPayload:dt},ee={list(t={}){return I.get("/upi-profiles",{params:t})},create(t){return I.post("/upi-profiles",t)},update(t,e){return I.patch(`/upi-profiles/${t}`,e)},deactivate(t){return I.delete(`/upi-profiles/${t}`)},setDefault(t){return I.patch(`/upi-profiles/${t}/default`)}},U="paychat_pos_wake_lock_enabled",ot=()=>{try{return localStorage.getItem(U)==="true"}catch{return!1}},ne=t=>{try{return t?(localStorage.setItem(U,"true"),!0):(localStorage.removeItem(U),!1)}catch{return!1}},Jt=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},oe=()=>{let t=null,e=!1,o=!1,n=0;const r=async()=>{try{await t?.release?.()}catch(p){console.warn("POS wake lock release failed:",p)}finally{t=null}},s=()=>{const p=Jt();return p.supported?!0:(o||(console.warn(`POS wake lock unavailable: ${p.reason}`),o=!0),!1)},i=async()=>{const p=Date.now();if(!(e||t||!ot()||!s()||document.visibilityState!=="visible")&&!(p-n<750)){n=p;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(h){console.warn("POS wake lock failed:",h)}}},c=()=>{i()},l=()=>{document.visibilityState==="visible"?i():r()},m=p=>{p.key===U&&(ot()?i():r())};return document.addEventListener("visibilitychange",l),document.addEventListener("pointerdown",c,{passive:!0}),document.addEventListener("touchstart",c,{passive:!0}),document.addEventListener("click",c,{passive:!0}),window.addEventListener("storage",m),i(),()=>{e=!0,document.removeEventListener("visibilitychange",l),document.removeEventListener("pointerdown",c),document.removeEventListener("touchstart",c),document.removeEventListener("click",c),window.removeEventListener("storage",m),r()}},Vt="paychat-pos",q="cache",D=mt(Vt,1,{upgrade(t){t.createObjectStore(q)}}),re={async set(t,e){await(await D).put(q,e,t)},async get(t){return await(await D).get(q,t)},async clear(){await(await D).clear(q)}};export{Nt as a,Zt as b,re as c,ne as d,Jt as e,ot as g,St as n,te as p,oe as s,ee as u};
