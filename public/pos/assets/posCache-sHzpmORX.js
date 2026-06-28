import{g as H,b as I}from"./index-DUjwccDd.js";import{o as ht}from"./vendor-qKbVCTru.js";const gt="/color-paychat-logo-main.svg",Z={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},ft=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},ot=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},yt=()=>ft(ot("tenant_info"),{}),rt=t=>S(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),st=t=>rt(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),it=t=>{const e=rt(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},a=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),k=t=>a(t).replace(/`/g,"&#096;"),S=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),u=t=>Number(t||0).toFixed(2),q=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},at=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},ct=(t="80mm")=>Z[t]||Z["80mm"],d=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},N=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},bt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},vt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),G=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(vt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const r=G(t[s],e+1,o);if(r.length)return r}for(const s of Object.values(t)){const r=G(s,e+1,o);if(r.length)return r}return[]},_t=(t={})=>d(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),V=(t={})=>N(t.quantity,t.qty,t.pivot?.quantity,1)||1,lt=(t={})=>{const e=V(t),o=d(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=d(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},$t=(t={})=>{const e=d(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):lt(t)*V(t)},xt=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return bt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,G(t))},wt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return d(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},tt=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=d(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},Nt=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},kt=t=>{if(!t)return"";const e=Nt(t),o=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const s=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${k(s)}" alt="Invoice QR" />`}const n=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return n?.[1]?`<img class="qr-image" src="${k(n[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${k(e)}" alt="Invoice QR" />`:`<div class="qr-url">${a(e)}</div>`},St=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=yt(),n=H(),s=o?.tenant||{},r=o?.branding||{},i=t.location||{},c=xt(t).map(b=>({name:_t(b),qty:V(b),rate:lt(b),total:$t(b)})),l=N(t.subtotal,t.totals?.subtotal,c.reduce((b,f)=>b+f.rate*f.qty,0)),m=N(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),p=N(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),h=N(t.total,t.grand_total,t.totals?.grand_total,l+p-m);return{shopName:d(e.shopName,i.tenant?.name,t.tenant?.name,r.company_name,s.name,ot("tenant_slug"),"PayChat POS"),shopPhone:d(e.shopPhone,i.phone,r.phone,s.phone),shopAddress:d(e.shopAddress,i.address,r.address,s.address),shopLogoUrl:d(e.shopLogoUrl,i.logo,i.tenant?.logo,t.tenant?.logo,r.logo,s.logo),locationName:d(i.name,t.location_name),paychatLogoUrl:d(e.paychatLogoUrl,t.paychat_logo_url,gt),invoiceNo:d(e.invoiceNo,t.invoice_no,t.invoiceNo,t.invoice?.number,t.invoice?.invoice_no,t.invoice?.offline_invoice_number,t.offline_invoice_number,t.local_invoice_no),orderNo:d(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:d(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:d(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:d(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:d(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:d(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:tt(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:tt(t.batch_codes,t.batchCodes),items:c,subtotal:l,discount:m,tax:p,grandTotal:h,paidAmount:N(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,h),paymentMethod:wt(t),invoiceUrl:d(e.invoiceUrl,t.invoice_url,t.invoiceUrl,t.invoice?.url),invoiceQr:d(e.invoiceQr,t.invoice_qr,t.invoiceQr,t.qr),reviewQr:d(e.reviewQr,t.review_qr,t.reviewQr),notes:d(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},Tt=(t,e={})=>{const o=e.paperSize||"80mm",n=ct(o),s=o==="58mm",r=e.agentPdf===!0,i=e.customPrintInvoice===!0,c=e.hideInvoiceQr===!0,l=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],p=Array.isArray(t.batchCodes)?t.batchCodes:[],h=H(),f=!(t.simpleBilling??h.simpleBilling),Q=t.billingLabel||h.billingLabel||"Order",A=i?st(t.shopName):t.shopName,E=i?it(t.shopAddress):t.shopAddress,B=i?s?"48px":"64px":n.paychatLogoWidth,v=c?"":kt(t.invoiceQr||t.reviewQr),X=t.invoiceUrl&&(c||!v)?`<div class="qr-url">${a(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
    .powered { font-size: ${i?"0.72em":"0.88em"}; }
    .title {
      font-size: ${n.titleSize};
      font-weight: ${i?"900":"800"};
      text-transform: ${i?"none":"uppercase"};
      word-break: break-word;
    }
    .shop-logo {
      display: block;
      max-width: ${n.logoMaxWidth};
      max-height: ${s?"54px":"74px"};
      object-fit: contain;
      margin: 0 auto 4px;
    }
    .paychat-logo {
      display: block;
      max-width: ${B};
      max-height: ${s?"20px":"26px"};
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
      font-size: ${s?"1.55em":"1.75em"};
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
      ${!i&&t.shopLogoUrl?`<img class="shop-logo" src="${k(t.shopLogoUrl)}" alt="${k(A)}" />`:""}
      <div class="title">${a(A)}</div>
      ${!i&&t.locationName?`<div class="muted">${a(t.locationName)}</div>`:""}
      ${E?`<div class="muted">${a(E)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${a(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${t.invoiceNo?`<div class="bill-no">${i?"INVOICE NO":"BILL NO"}: ${a(t.invoiceNo)}</div>`:""}
    <table>
	      ${!i&&t.orderNo?`<tr><td>${a(Q)}</td><td class="right">${a(t.orderNo)}</td></tr>`:""}
      <tr><td>Date</td><td class="right">${a(at(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${a(t.orderType)}</td></tr>`:""}
	      ${f&&t.tableName?`<tr><td>Table</td><td class="right">${a(t.tableName)}</td></tr>`:""}
	      ${f&&t.guestCount&&!i?`<tr><td>Guests</td><td class="right">${a(t.guestCount)}</td></tr>`:""}
	      ${f&&t.tokenNo&&!i?`<tr><td>Token</td><td class="right">${a(t.tokenNo)}</td></tr>`:""}
	      ${f&&m.length?`<tr><td>KOT</td><td class="right">${a(m.join(", "))}</td></tr>`:""}
	      ${f&&p.length?`<tr><td>Batch</td><td class="right">${a(p.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${s?`
      <div>
        ${l.length?l.map(_=>`
          <div class="item-block">
            <div class="item-name">${a(_.name)}</div>
            <div class="item-meta">
              <span>${a(q(_.qty))} x ${a(u(_.rate))}</span>
              <strong>${a(u(_.total))}</strong>
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
              <td class="right">${a(q(_.qty))}</td>
              <td class="right">${a(u(_.rate))}</td>
              <td class="right">${a(u(_.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${i?`
      <div class="total-row grand"><span>TOTAL</span><span>${a(u(t.grandTotal))}</span></div>
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${a(t.paymentMethod)}</span></div>`:""}
    `:r?`
      <table class="pdf-totals">
        <tbody>
          <tr><td>Subtotal</td><td class="pdf-total-value">${a(u(t.subtotal))}</td></tr>
          ${t.discount?`<tr><td>Discount</td><td class="pdf-total-value">-${a(u(t.discount))}</td></tr>`:""}
          ${t.tax?`<tr><td>Tax/GST</td><td class="pdf-total-value">${a(u(t.tax))}</td></tr>`:""}
          <tr class="grand"><td>TOTAL</td><td class="pdf-total-value">${a(u(t.grandTotal))}</td></tr>
          ${t.paidAmount?`<tr><td>Paid</td><td class="pdf-total-value">${a(u(t.paidAmount))}</td></tr>`:""}
          ${t.paymentMethod?`<tr><td>Payment</td><td class="pdf-total-value">${a(t.paymentMethod)}</td></tr>`:""}
        </tbody>
      </table>
    `:`
      <div class="total-row"><span>Subtotal</span><span>${a(u(t.subtotal))}</span></div>
      ${t.discount?`<div class="total-row"><span>Discount</span><span>-${a(u(t.discount))}</span></div>`:""}
      ${t.tax?`<div class="total-row"><span>Tax/GST</span><span>${a(u(t.tax))}</span></div>`:""}
      <div class="total-row grand"><span>TOTAL</span><span>${a(u(t.grandTotal))}</span></div>
      ${t.paidAmount?`<div class="total-row"><span>Paid</span><span>${a(u(t.paidAmount))}</span></div>`:""}
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${a(t.paymentMethod)}</span></div>`:""}
    `}
    ${v||X?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!c&&v?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${v||X}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!i?`<img class="paychat-logo" src="${k(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},$=(t,e="-")=>`${e.repeat(t)}
`,y=(t,e)=>{const o=S(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},g=(t,e,o)=>{const n=S(e),s=Math.max(1,o-n.length-1),r=S(t).slice(0,s),i=Math.max(1,o-r.length-n.length);return`${r}${" ".repeat(i)}${n}
`},J=(t,e)=>{const o=S(t).split(/\s+/).filter(Boolean).flatMap(r=>r.length<=e?[r]:r.match(new RegExp(`.{1,${e}}`,"g"))||[r]),n=[];let s="";return o.forEach(r=>{if(!s){s=r;return}(s+" "+r).length<=e?s+=` ${r}`:(n.push(s),s=r.slice(0,e))}),s&&n.push(s),n.length?n:[""]},At=(t,e)=>{const o=J(t.name,e),n=`${q(t.qty)} x ${u(t.rate)}`;return[...o.map(s=>`${s}
`),g(n,u(t.total),e)].join("")},It=(t,e)=>{const r=e-5-9-10,i=J(t.name,r),c=`${i[0].padEnd(r)}${q(t.qty).padStart(5)}${u(t.rate).padStart(9)}${u(t.total).padStart(10)}
`,l=i.slice(1).map(m=>`${m}
`).join("");return c+l},pt=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=ct(o),s=o==="58mm",r=e.customPrintInvoice===!0,i=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],l=Array.isArray(t.kotCodes)?t.kotCodes:[],m=Array.isArray(t.batchCodes)?t.batchCodes:[],p=H(),h=t.simpleBilling??p.simpleBilling,b=t.billingLabel||p.billingLabel||"Order",f=s?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,Q=r?st(t.shopName):t.shopName,A=r?it(t.shopAddress):t.shopAddress,E=r&&t.tokenNo?`${$(n)}${y(`TOKEN ${t.tokenNo}`,n)}${$(n)}`:"",B=t.invoiceUrl?`${$(n)}${y(i?"Invoice link":"Invoice/review link",n)}${J(t.invoiceUrl,n).map(v=>`${S(v)}
`).join("")}`:"";return[E,y(Q,n),!r&&t.locationName?y(t.locationName,n):"",t.shopPhone?y(`Phone: ${t.shopPhone}`,n):"",A?y(A,n):"",$(n),t.invoiceNo?y(`${r?"INVOICE NO":"BILL NO"}: ${t.invoiceNo}`,n):"",!r&&t.orderNo?g(b,t.orderNo,n):"",g("Date",at(t.dateTime),n),t.orderType?g("Type",t.orderType,n):"",!h&&t.tableName?g("Table",t.tableName,n):"",!h&&t.guestCount&&!r?g("Guests",t.guestCount,n):"",!h&&t.tokenNo&&!r?g("Token",t.tokenNo,n):"",!h&&l.length?g("KOT",l.join(","),n):"",!h&&m.length?g("Batch",m.join(","),n):"",$(n),f,f?$(n):"",c.length?c.map(v=>s?At(v,n):It(v,n)).join(""):y("No items",n),$(n),r?"":g("Subtotal",u(t.subtotal),n),!r&&t.discount?g("Discount",`-${u(t.discount)}`,n):"",!r&&t.tax?g("Tax/GST",u(t.tax),n):"",r?"":$(n),g("TOTAL",u(t.grandTotal),n),t.paidAmount&&!r?g("Paid",u(t.paidAmount),n):"",t.paymentMethod?g("Payment",t.paymentMethod,n):"",B,$(n),y("Thank you",n),y("Powered by PayChat",n)].join("")},te=pt,ut="paychat_print_agent_settings",F={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},Pt=8e3,et=12e3,Lt=["invoice_url","invoiceUrl","review_url","reviewUrl"],Et=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},Y=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),Ct=t=>t==="80mm"?"80mm":"58mm",qt=t=>t==="pdf"?"pdf":"escpos",x=(t={})=>({...F,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||F.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:Ct(t?.paperSize),printMode:qt(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),w=()=>typeof localStorage>"u"?{...F}:x(Et(localStorage.getItem(ut),{})),zt=(t={})=>{const e=x({...w(),...t});try{localStorage.setItem(ut,JSON.stringify(e))}catch{}return e},W=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},R=(t,e={},o={})=>{const n=x(e),s=new URL(t,`${n.agentUrl}/`),r={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(r).forEach(([i,c])=>{c!=null&&c!==""&&s.searchParams.set(i,String(c))}),s.toString()},M=async(t,e={},o=Pt)=>{const n=new AbortController,s=setTimeout(()=>n.abort(),o);try{const r=await fetch(t,{...e,signal:n.signal}),c=(r.headers.get("content-type")||"").includes("application/json")?await r.json().catch(()=>null):await r.text().catch(()=>"");if(!r.ok)throw W(c?.message||c?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return c}catch(r){throw r?.name==="AbortError"?W("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",r):r?.code?r:W("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",r)}finally{clearTimeout(s)}},Ot=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Ut=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),K=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Ut)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const r=K(t[s],e+1,o);if(r.length)return r}for(const s of Object.values(t)){const r=K(s,e+1,o);if(r.length)return r}return[]},P=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},T=(...t)=>{for(const e of t){const o=Y(e).trim();if(o)return o}return""},j=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Ot(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,K(t))},z=(t={})=>T(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),L=(t={})=>P(t.quantity,t.qty,t.pivot?.quantity,1)||1,O=(t={})=>{const e=L(t),o=T(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=T(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},dt=(t={})=>{const e=T(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):O(t)*L(t)},Rt=(t=[])=>t.map(e=>({...e,product_name:z(e),name:z(e),quantity:L(e),qty:L(e),rate:O(e),price:O(e),total:dt(e)})),Mt=(t,e)=>{const o=Y(t);if(o.length<=e)return[o];const n=[];for(let s=0;s<o.length;s+=e)n.push(o.slice(s,s+e));return n},jt=(t,e)=>{const o=e==="80mm"?48:32;return Y(t).split(/\r?\n/).flatMap(n=>Mt(n,o)).join(`
`)},Qt=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=j(t);return n.length?n.map(s=>{const r=z(s),i=L(s),c=O(s),m=dt(s).toFixed(2),p=`${i} x ${c.toFixed(2)}`,h=Math.max(1,o-p.length-m.length);return`${r}
${p}${" ".repeat(h)}${m}`}).join(`
`):""},Bt=(t,e,o)=>{const n=j(e);return!n.length||n.some(r=>{const i=z(r);return i&&t.includes(i.slice(0,Math.min(i.length,12)))})?t:`${t}
${Qt(e,o)}`},Wt=(t,e)=>{if(/total/i.test(t))return t;const o=P(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,j(e).reduce((n,s)=>{const r=P(s.quantity,s.qty,1)||1,i=P(s.rate,s.price,s.unit_price);return n+P(s.total,s.line_total,s.amount,r*i)},0));return`${t}
TOTAL ${o.toFixed(2)}`},Dt=(t={},e={})=>{for(const o of Lt){const n=T(t[o],e[o]);if(n)return n}return T(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},Gt=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},mt=(t={},e={},o=w())=>{const n=x(o),s=n.paperSize,r={...t||{},items:Rt(j(t||{}))},i=St(r,e||{}),c={paperSize:s,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr};let l=pt(i,c);const m=Tt(i,{...c,agentPdf:n.printMode==="pdf"});typeof l!="string"&&(l=String(l??"")),l=Bt(l,r,s),l=Wt(l,r),l=jt(l,s),l.length>et&&(l=`${l.slice(0,et)}
--- Receipt truncated ---`),l=l.replace(/\n*$/,`


`);const p=Dt(t,i),h={text:l,html:m,print_mode:n.printMode};return!n.hideInvoiceQr&&p&&Gt(p)&&(h.qr={data:p,size:6,error_correction:"M"}),h},Ft=async(t=w())=>{const e=x(t);return M(R("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Kt=async(t=w())=>{const e=x(t),o=await M(R("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},Ht=async(t=w())=>{const e=x(t);return M(R("/test-print",e),{method:"POST"})},Vt=async(t={},e={})=>{const o=x(e.settings||w()),n=mt(t,e.context||{},o);return M(R("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},ee={getSettings:w,saveSettings:zt,checkHealth:Ft,getPrinters:Kt,testPrint:Ht,printReceipt:Vt,buildSafeAgentReceiptPayload:mt},ne={list(t={}){return I.get("/upi-profiles",{params:t})},create(t){return I.post("/upi-profiles",t)},update(t,e){return I.patch(`/upi-profiles/${t}`,e)},deactivate(t){return I.delete(`/upi-profiles/${t}`)},setDefault(t){return I.patch(`/upi-profiles/${t}/default`)}},U="paychat_pos_wake_lock_enabled",nt=()=>{try{return localStorage.getItem(U)==="true"}catch{return!1}},oe=t=>{try{return t?(localStorage.setItem(U,"true"),!0):(localStorage.removeItem(U),!1)}catch{return!1}},Jt=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},re=()=>{let t=null,e=!1,o=!1,n=0;const s=async()=>{try{await t?.release?.()}catch(p){console.warn("POS wake lock release failed:",p)}finally{t=null}},r=()=>{const p=Jt();return p.supported?!0:(o||(console.warn(`POS wake lock unavailable: ${p.reason}`),o=!0),!1)},i=async()=>{const p=Date.now();if(!(e||t||!nt()||!r()||document.visibilityState!=="visible")&&!(p-n<750)){n=p;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(h){console.warn("POS wake lock failed:",h)}}},c=()=>{i()},l=()=>{document.visibilityState==="visible"?i():s()},m=p=>{p.key===U&&(nt()?i():s())};return document.addEventListener("visibilitychange",l),document.addEventListener("pointerdown",c,{passive:!0}),document.addEventListener("touchstart",c,{passive:!0}),document.addEventListener("click",c,{passive:!0}),window.addEventListener("storage",m),i(),()=>{e=!0,document.removeEventListener("visibilitychange",l),document.removeEventListener("pointerdown",c),document.removeEventListener("touchstart",c),document.removeEventListener("click",c),window.removeEventListener("storage",m),s()}},Yt="paychat-pos",C="cache",D=ht(Yt,1,{upgrade(t){t.createObjectStore(C)}}),se={async set(t,e){await(await D).put(C,e,t)},async get(t){return await(await D).get(C,t)},async clear(){await(await D).clear(C)}};export{Tt as a,te as b,se as c,oe as d,Jt as e,nt as g,St as n,ee as p,re as s,ne as u};
