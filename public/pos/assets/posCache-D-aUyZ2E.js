import{g as Y,b as q}from"./index-DJ2FbZOy.js";import{o as vt}from"./vendor-qKbVCTru.js";const _t="/color-paychat-logo-main.svg",$t="\x1BE",xt="\x1BE\0",wt="\x1BG",St="\x1BG\0",et={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},rt=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},X=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},Nt=()=>rt(X("tenant_info"),{}),kt=()=>rt(X("selected_location"),{}),at=t=>E(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),ct=t=>at(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),lt=t=>{const e=at(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},c=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),L=t=>c(t).replace(/`/g,"&#096;"),E=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),u=t=>Number(t||0).toFixed(2),R=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},pt=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},dt=(t="80mm")=>et[t]||et["80mm"],d=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},Tt=t=>{const e=String(t||"").trim();if(!e)return"";try{const o=typeof window<"u"?window.location.origin:"https://paychat.local",s=new URL(e,o).pathname.split("/").map(a=>a.trim()).filter(Boolean),i=s.findIndex(a=>["invoice","invoices"].includes(a.toLowerCase())),r=i>=0?s[i+1]:s[s.length-1];return decodeURIComponent(r||"").trim()}catch{const s=e.split("?")[0].split("#")[0].split("/").map(i=>i.trim()).filter(Boolean);return s[s.length-1]||""}},P=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},It=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},At=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),H=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(At)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const i=H(t[s],e+1,o);if(i.length)return i}for(const s of Object.values(t)){const i=H(s,e+1,o);if(i.length)return i}return[]},Pt=(t={})=>d(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),Z=(t={})=>P(t.quantity,t.qty,t.pivot?.quantity,1)||1,ut=(t={})=>{const e=Z(t),o=d(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=d(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},Lt=(t={})=>{const e=d(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):ut(t)*Z(t)},Et=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return It(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,H(t))},Ct=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return d(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},nt=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=d(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},qt=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},Ot=t=>{if(!t)return"";const e=qt(t),o=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const s=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${L(s)}" alt="Invoice QR" />`}const n=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return n?.[1]?`<img class="qr-image" src="${L(n[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${L(e)}" alt="Invoice QR" />`:`<div class="qr-url">${c(e)}</div>`},zt=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=Nt(),n=Y(),s=o?.tenant||{},i=o?.branding||s?.branding||o?.branching||{},r=kt(),a={...r&&typeof r=="object"?r:{},...t.location&&typeof t.location=="object"?t.location:{}};t.branch||t.branching||t.branding||o?.branch||o?.branching;const l=t.merchant||t.receipt?.merchant||{},m=t.invoice||t.invoice_data||t.receipt?.invoice||{},p=t.qr||t.receipt?.qr||{},g=d(e.invoiceUrl,t.invoice_url,t.invoiceUrl,m.url,t.meta?.invoice?.url,p.invoice_url),_=Et(t).map(y=>({name:Pt(y),qty:Z(y),rate:ut(y),total:Lt(y)})),f=P(t.subtotal,t.totals?.subtotal,_.reduce((y,$)=>y+$.rate*$.qty,0)),k=P(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),S=P(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),v=P(t.total,t.grand_total,t.totals?.grand_total,f+S-k);return{shopName:d(e.shopName,l.name,a.tenant?.name,t.tenant?.name,i.company_name,s.name,X("tenant_slug"),"PayChat POS"),shopPhone:d(e.shopPhone,l.phone,a.phone,i.phone,s.phone),shopAddress:d(e.shopAddress,i.address,s.branding?.address,t.tenant?.branding?.address,a.tenant?.branding?.address),shopLogoUrl:d(e.shopLogoUrl,a.logo,a.tenant?.logo,t.tenant?.logo,i.logo,s.logo),locationName:d(a.name,t.location_name),paychatLogoUrl:d(e.paychatLogoUrl,t.paychat_logo_url,_t),invoiceNo:d(e.invoiceNo,t.invoice_no,t.invoiceNo,m.number,m.invoice_no,m.invoiceNo,m.invoice_number,m.offline_invoice_number,t.meta?.invoice?.number,t.meta?.invoice?.invoice_no,t.meta?.invoice?.invoiceNo,t.meta?.invoice?.invoice_number,t.offline_invoice_number,t.local_invoice_no,Tt(g)),orderNo:d(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:d(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:d(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:d(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:d(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:d(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:nt(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:nt(t.batch_codes,t.batchCodes),items:_,subtotal:f,discount:k,tax:S,grandTotal:v,paidAmount:P(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,v),paymentMethod:Ct(t),invoiceUrl:g,invoiceQr:d(e.invoiceQr,t.invoice_qr,t.invoiceQr,p.qr_svg_or_url,t.qr),reviewQr:d(e.reviewQr,t.review_qr,t.reviewQr),notes:d(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},Ut=(t,e={})=>{const o=e.paperSize||"80mm",n=dt(o),s=o==="58mm",i=e.agentPdf===!0,r=e.customPrintInvoice===!0,a=e.hideInvoiceQr===!0,l=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],p=Array.isArray(t.batchCodes)?t.batchCodes:[],g=Y(),f=!(t.simpleBilling??g.simpleBilling),k=r?ct(t.shopName):t.shopName,S=r?lt(t.shopAddress):t.shopAddress,v=d(t.invoiceNo),y=r?s?"48px":"64px":n.paychatLogoWidth,$=a?"":Ot(t.invoiceQr||t.reviewQr),C=t.invoiceUrl&&(a||!$)?`<div class="qr-url">${c(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
      line-height: ${r?"1.08":"1.28"};
    }
    .receipt {
      width: ${n.width};
      padding: ${r?"2px 4px":n.padding};
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { font-size: 0.88em; }
    .powered { font-size: ${r?"0.72em":"0.88em"}; }
    .title {
      color: #000;
      font-size: ${r?s?"15px":"18px":n.titleSize};
      font-weight: ${r?"900":"800"};
      text-transform: ${r?"none":"uppercase"};
      ${r?"text-shadow: 0 0 0 #000, 0.25px 0 #000, -0.25px 0 #000; -webkit-text-stroke: 0.25px #000;":""}
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
      max-width: ${y};
      max-height: ${s?"20px":"26px"};
      object-fit: contain;
      margin: 2px auto 1px;
    }
    .bill-no {
      font-size: 1.15em;
      font-weight: ${r?"900":"700"};
      text-align: center;
      margin: ${r?"1px 0":"3px 0"};
      word-break: break-word;
    }
    .bill-no-row td {
      color: #000;
      font-weight: 900;
      padding-top: 0;
    }
    .line {
      border-top: 1px dashed #000;
      margin: ${r?"2px 0":"6px 0"};
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    td, th {
      padding: ${r?"1px 0":"2px 0"};
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
      padding: ${r?"1px 0":"3px 0"};
      border-bottom: 1px dotted #999;
    }
    .item-meta,
    .total-row {
      display: flex;
      justify-content: space-between;
      gap: 6px;
    }
    .grand {
      border-top: ${r?"2px solid #000":"1px dashed #000"};
      color: #000;
      padding-top: ${r?"3px":"5px"};
      margin-top: ${r?"2px":"4px"};
      font-weight: 900;
      font-size: ${r?"1.22em":"1.12em"};
      ${r?"text-shadow: 0.25px 0 #000, -0.25px 0 #000;":""}
    }
    .top-token {
      border-bottom: 1px dashed #000;
      font-size: ${s?"1.55em":"1.75em"};
      font-weight: 900;
      margin-bottom: ${r?"3px":"6px"};
      padding-bottom: ${r?"3px":"6px"};
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
    ${r&&t.tokenNo?`<div class="top-token">TOKEN ${c(t.tokenNo)}</div>`:""}
    <div class="center">
      ${!r&&t.shopLogoUrl?`<img class="shop-logo" src="${L(t.shopLogoUrl)}" alt="${L(k)}" />`:""}
      <div class="title">${c(k)}</div>
      ${!r&&t.locationName?`<div class="muted">${c(t.locationName)}</div>`:""}
      ${S?`<div class="muted">${c(S)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${c(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${v&&!r?`<div class="bill-no">INVOICE NO: ${c(v)}</div>`:""}
    <table>
	      ${v&&r?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${c(v)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${c(pt(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${c(t.orderType)}</td></tr>`:""}
	      ${f&&t.tableName?`<tr><td>Table</td><td class="right">${c(t.tableName)}</td></tr>`:""}
	      ${f&&t.guestCount&&!r?`<tr><td>Guests</td><td class="right">${c(t.guestCount)}</td></tr>`:""}
	      ${f&&t.tokenNo&&!r?`<tr><td>Token</td><td class="right">${c(t.tokenNo)}</td></tr>`:""}
	      ${f&&m.length?`<tr><td>KOT</td><td class="right">${c(m.join(", "))}</td></tr>`:""}
	      ${f&&p.length?`<tr><td>Batch</td><td class="right">${c(p.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${s?`
      <div>
        ${l.length?l.map(b=>`
          <div class="item-block">
            <div class="item-name">${c(b.name)}</div>
            <div class="item-meta">
              <span>${c(R(b.qty))} x ${c(u(b.rate))}</span>
              <strong>${c(u(b.total))}</strong>
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
          ${l.length?l.map(b=>`
            <tr>
              <td class="item-name">${c(b.name)}</td>
              <td class="right">${c(R(b.qty))}</td>
              <td class="right">${c(u(b.rate))}</td>
              <td class="right">${c(u(b.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${r?`
      <div class="total-row grand"><span>TOTAL</span><span>${c(u(t.grandTotal))}</span></div>
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${c(t.paymentMethod)}</span></div>`:""}
    `:i?`
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
    ${$||C?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!a&&$?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${$||C}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!r?`<img class="paychat-logo" src="${L(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},x=(t,e="-")=>`${e.repeat(t)}
`,mt=(t="")=>`${$t}${wt}${t}${St}${xt}`,Rt=(t="")=>mt(t),w=(t,e)=>{const o=E(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},h=(t,e,o)=>{const n=E(e),s=Math.max(1,o-n.length-1),i=E(t).slice(0,s),r=Math.max(1,o-i.length-n.length);return`${i}${" ".repeat(r)}${n}
`},M=(t,e)=>{const o=E(t).split(/\s+/).filter(Boolean).flatMap(i=>i.length<=e?[i]:i.match(new RegExp(`.{1,${e}}`,"g"))||[i]),n=[];let s="";return o.forEach(i=>{if(!s){s=i;return}(s+" "+i).length<=e?s+=` ${i}`:(n.push(s),s=i.slice(0,e))}),s&&n.push(s),n.length?n:[""]},Mt=(t,e)=>{const o=M(t.name,e),n=`${R(t.qty)} x ${u(t.rate)}`;return[...o.map(s=>`${s}
`),h(n,u(t.total),e)].join("")},jt=(t,e)=>{const i=e-5-9-10,r=M(t.name,i),a=`${r[0].padEnd(i)}${R(t.qty).padStart(5)}${u(t.rate).padStart(9)}${u(t.total).padStart(10)}
`,l=r.slice(1).map(m=>`${m}
`).join("");return a+l},ht=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=dt(o),s=o==="58mm",i=e.customPrintInvoice===!0,r=e.hideInvoiceQr===!0,a=e.escposCommands===!0,l=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],p=Array.isArray(t.batchCodes)?t.batchCodes:[],g=Y(),_=t.simpleBilling??g.simpleBilling,f=s?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,k=i?ct(t.shopName):t.shopName,S=i?lt(t.shopAddress):t.shopAddress,v=d(t.invoiceNo),y=w(k,n),$=S?M(S,n).map(A=>w(A,n)).join(""):"",C=v?h("Invoice No",v,n):"",b=i&&t.tokenNo?`${x(n)}${w(`TOKEN ${t.tokenNo}`,n)}${x(n)}`:"",bt=t.invoiceUrl?`${x(n)}${w(r?"Invoice link":"Invoice/review link",n)}${M(t.invoiceUrl,n).map(A=>`${E(A)}
`).join("")}`:"";return[b,a?Rt(y):y,!i&&t.locationName?w(t.locationName,n):"",$,t.shopPhone?w(`Phone: ${t.shopPhone}`,n):"",x(n),a?mt(C):C,h("Date",pt(t.dateTime),n),t.orderType?h("Type",t.orderType,n):"",!_&&t.tableName?h("Table",t.tableName,n):"",!_&&t.guestCount&&!i?h("Guests",t.guestCount,n):"",!_&&t.tokenNo&&!i?h("Token",t.tokenNo,n):"",!_&&m.length?h("KOT",m.join(","),n):"",!_&&p.length?h("Batch",p.join(","),n):"",x(n),f,f?x(n):"",l.length?l.map(A=>s?Mt(A,n):jt(A,n)).join(""):w("No items",n),x(n),i?"":h("Subtotal",u(t.subtotal),n),!i&&t.discount?h("Discount",`-${u(t.discount)}`,n):"",!i&&t.tax?h("Tax/GST",u(t.tax),n):"",i?"":x(n),h("TOTAL",u(t.grandTotal),n),t.paidAmount&&!i?h("Paid",u(t.paidAmount),n):"",t.paymentMethod?h("Payment",t.paymentMethod,n):"",bt,x(n),w("Thank you",n),w("Powered by PayChat",n)].join("")},ue=ht,gt="paychat_print_agent_settings",J={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},Bt=8e3,ot=12e3,Qt=["invoice_url","invoiceUrl","review_url","reviewUrl"],Wt=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},tt=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),Dt=t=>t==="80mm"?"80mm":"58mm",Gt=t=>t==="pdf"?"pdf":"escpos",N=(t={})=>({...J,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||J.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:Dt(t?.paperSize),printMode:Gt(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),I=()=>typeof localStorage>"u"?{...J}:N(Wt(localStorage.getItem(gt),{})),Ft=(t={})=>{const e=N({...I(),...t});try{localStorage.setItem(gt,JSON.stringify(e))}catch{}return e},F=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},W=(t,e={},o={})=>{const n=N(e),s=new URL(t,`${n.agentUrl}/`),i={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(i).forEach(([r,a])=>{a!=null&&a!==""&&s.searchParams.set(r,String(a))}),s.toString()},D=async(t,e={},o=Bt)=>{const n=new AbortController,s=setTimeout(()=>n.abort(),o);try{const i=await fetch(t,{...e,signal:n.signal}),a=(i.headers.get("content-type")||"").includes("application/json")?await i.json().catch(()=>null):await i.text().catch(()=>"");if(!i.ok)throw F(a?.message||a?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return a}catch(i){throw i?.name==="AbortError"?F("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",i):i?.code?i:F("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",i)}finally{clearTimeout(s)}},Kt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Ht=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),V=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Ht)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const i=V(t[s],e+1,o);if(i.length)return i}for(const s of Object.values(t)){const i=V(s,e+1,o);if(i.length)return i}return[]},O=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},T=(...t)=>{for(const e of t){const o=tt(e).trim();if(o)return o}return""},G=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Kt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,V(t))},j=(t={})=>T(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),z=(t={})=>O(t.quantity,t.qty,t.pivot?.quantity,1)||1,B=(t={})=>{const e=z(t),o=T(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=T(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},ft=(t={})=>{const e=T(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):B(t)*z(t)},Jt=(t=[])=>t.map(e=>({...e,product_name:j(e),name:j(e),quantity:z(e),qty:z(e),rate:B(e),price:B(e),total:ft(e)})),Vt=(t,e)=>{const o=tt(t);if(o.length<=e)return[o];const n=[];for(let s=0;s<o.length;s+=e)n.push(o.slice(s,s+e));return n},Yt=(t,e)=>{const o=e==="80mm"?48:32;return tt(t).split(/\r?\n/).flatMap(n=>Vt(n,o)).join(`
`)},Xt=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=G(t);return n.length?n.map(s=>{const i=j(s),r=z(s),a=B(s),m=ft(s).toFixed(2),p=`${r} x ${a.toFixed(2)}`,g=Math.max(1,o-p.length-m.length);return`${i}
${p}${" ".repeat(g)}${m}`}).join(`
`):""},Zt=(t,e,o)=>{const n=G(e);return!n.length||n.some(i=>{const r=j(i);return r&&t.includes(r.slice(0,Math.min(r.length,12)))})?t:`${t}
${Xt(e,o)}`},te=(t,e)=>{if(/total/i.test(t))return t;const o=O(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,G(e).reduce((n,s)=>{const i=O(s.quantity,s.qty,1)||1,r=O(s.rate,s.price,s.unit_price);return n+O(s.total,s.line_total,s.amount,i*r)},0));return`${t}
TOTAL ${o.toFixed(2)}`},st=t=>`\x1BE${t}\x1BE\0`,ee=(t="",e={})=>{const o=T(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(n=>{const s=n.trim();return s&&(o&&s.toLowerCase()===o.toLowerCase()||/^invoice no\b/i.test(s)||/^total\b/i.test(s))?st(n):n}).join(`
`)},ne=(t={},e={})=>{for(const o of Qt){const n=T(t[o],e[o]);if(n)return n}return T(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},oe=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},yt=(t={},e={},o=I())=>{const n=N(o),s=n.paperSize,i={...t||{},items:Jt(G(t||{}))},r=zt(i,e||{}),a={paperSize:s,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr,escposCommands:n.printMode==="escpos"};let l=ht(r,a);const m=Ut(r,{...a,agentPdf:n.printMode==="pdf"});typeof l!="string"&&(l=String(l??"")),l=Zt(l,i,s),l=te(l,i),l=Yt(l,s),n.customPrintInvoice&&(l=ee(l,r)),l.length>ot&&(l=`${l.slice(0,ot)}
--- Receipt truncated ---`),l=l.replace(/\n*$/,`


`);const p=ne(t,r),g={text:l,html:m,print_mode:n.printMode};return!n.hideInvoiceQr&&p&&oe(p)&&(g.qr={data:p,size:6,error_correction:"M"}),g},se=async(t=I())=>{const e=N(t);return D(W("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},ie=async(t=I())=>{const e=N(t),o=await D(W("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},re=async(t=I())=>{const e=N(t);return D(W("/test-print",e),{method:"POST"})},ae=async(t={},e={})=>{const o=N(e.settings||I()),n=yt(t,e.context||{},o);return D(W("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},me={getSettings:I,saveSettings:Ft,checkHealth:se,getPrinters:ie,testPrint:re,printReceipt:ae,buildSafeAgentReceiptPayload:yt},he={list(t={}){return q.get("/upi-profiles",{params:t})},create(t){return q.post("/upi-profiles",t)},update(t,e){return q.patch(`/upi-profiles/${t}`,e)},deactivate(t){return q.delete(`/upi-profiles/${t}`)},setDefault(t){return q.patch(`/upi-profiles/${t}/default`)}},Q="paychat_pos_wake_lock_enabled",it=()=>{try{return localStorage.getItem(Q)==="true"}catch{return!1}},ge=t=>{try{return t?(localStorage.setItem(Q,"true"),!0):(localStorage.removeItem(Q),!1)}catch{return!1}},ce=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},fe=()=>{let t=null,e=!1,o=!1,n=0;const s=async()=>{try{await t?.release?.()}catch(p){console.warn("POS wake lock release failed:",p)}finally{t=null}},i=()=>{const p=ce();return p.supported?!0:(o||(console.warn(`POS wake lock unavailable: ${p.reason}`),o=!0),!1)},r=async()=>{const p=Date.now();if(!(e||t||!it()||!i()||document.visibilityState!=="visible")&&!(p-n<750)){n=p;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(g){console.warn("POS wake lock failed:",g)}}},a=()=>{r()},l=()=>{document.visibilityState==="visible"?r():s()},m=p=>{p.key===Q&&(it()?r():s())};return document.addEventListener("visibilitychange",l),document.addEventListener("pointerdown",a,{passive:!0}),document.addEventListener("touchstart",a,{passive:!0}),document.addEventListener("click",a,{passive:!0}),window.addEventListener("storage",m),r(),()=>{e=!0,document.removeEventListener("visibilitychange",l),document.removeEventListener("pointerdown",a),document.removeEventListener("touchstart",a),document.removeEventListener("click",a),window.removeEventListener("storage",m),s()}},le="paychat-pos",U="cache",K=vt(le,1,{upgrade(t){t.createObjectStore(U)}}),ye={async set(t,e){await(await K).put(U,e,t)},async get(t){return await(await K).get(U,t)},async clear(){await(await K).clear(U)}};export{Ut as a,ue as b,ye as c,ge as d,ce as e,it as g,zt as n,me as p,fe as s,he as u};
