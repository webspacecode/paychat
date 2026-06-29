import{g as Y,b as q}from"./index-Dz61Ogpr.js";import{o as vt}from"./vendor-qKbVCTru.js";const _t="/color-paychat-logo-main.svg",$t="\x1BE",xt="\x1BE\0",wt="\x1BG",St="\x1BG\0",et={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},it=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},X=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},Nt=()=>it(X("tenant_info"),{}),kt=()=>it(X("selected_location"),{}),at=t=>C(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),ct=t=>at(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),lt=t=>{const e=at(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},c=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),E=t=>c(t).replace(/`/g,"&#096;"),C=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),u=t=>Number(t||0).toFixed(2),R=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},pt=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},dt=(t="80mm")=>et[t]||et["80mm"],d=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},Tt=t=>{const e=String(t||"").trim();if(!e)return"";try{const o=typeof window<"u"?window.location.origin:"https://paychat.local",s=new URL(e,o).pathname.split("/").map(a=>a.trim()).filter(Boolean),r=s.findIndex(a=>["invoice","invoices"].includes(a.toLowerCase())),i=r>=0?s[r+1]:s[s.length-1];return decodeURIComponent(i||"").trim()}catch{const s=e.split("?")[0].split("#")[0].split("/").map(r=>r.trim()).filter(Boolean);return s[s.length-1]||""}},L=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},It=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},At=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),H=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(At)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const r=H(t[s],e+1,o);if(r.length)return r}for(const s of Object.values(t)){const r=H(s,e+1,o);if(r.length)return r}return[]},Pt=(t={})=>d(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),Z=(t={})=>L(t.quantity,t.qty,t.pivot?.quantity,1)||1,ut=(t={})=>{const e=Z(t),o=d(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=d(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},Lt=(t={})=>{const e=d(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):ut(t)*Z(t)},Et=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return It(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,H(t))},Ct=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return d(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},nt=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=d(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},qt=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},Ot=t=>{if(!t)return"";const e=qt(t),o=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const s=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${E(s)}" alt="Invoice QR" />`}const n=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return n?.[1]?`<img class="qr-image" src="${E(n[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${E(e)}" alt="Invoice QR" />`:`<div class="qr-url">${c(e)}</div>`},zt=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=Nt(),n=Y(),s=o?.tenant||{},r=o?.branding||o?.branching||s?.branding||{},i=kt(),a={...i&&typeof i=="object"?i:{},...t.location&&typeof t.location=="object"?t.location:{}},l=t.branch||t.branching||t.branding||o?.branch||o?.branching||{},m=t.merchant||t.receipt?.merchant||{},p=t.invoice||t.invoice_data||t.receipt?.invoice||{},g=t.qr||t.receipt?.qr||{},_=d(e.invoiceUrl,t.invoice_url,t.invoiceUrl,p.url,t.meta?.invoice?.url,g.invoice_url),y=Et(t).map(f=>({name:Pt(f),qty:Z(f),rate:ut(f),total:Lt(f)})),k=L(t.subtotal,t.totals?.subtotal,y.reduce((f,S)=>f+S.rate*S.qty,0)),w=L(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),v=L(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),T=L(t.total,t.grand_total,t.totals?.grand_total,k+v-w);return{shopName:d(e.shopName,m.name,a.tenant?.name,t.tenant?.name,r.company_name,s.name,X("tenant_slug"),"PayChat POS"),shopPhone:d(e.shopPhone,m.phone,a.phone,r.phone,s.phone),shopAddress:d(e.shopAddress,m.address,a.address,l.address,t.branch_address,t.branchAddress,t.shop_address,t.shopAddress,t.tenant?.address,r.address,s.address),shopLogoUrl:d(e.shopLogoUrl,a.logo,a.tenant?.logo,t.tenant?.logo,r.logo,s.logo),locationName:d(a.name,t.location_name),paychatLogoUrl:d(e.paychatLogoUrl,t.paychat_logo_url,_t),invoiceNo:d(e.invoiceNo,t.invoice_no,t.invoiceNo,p.number,p.invoice_no,p.invoiceNo,p.invoice_number,p.offline_invoice_number,t.meta?.invoice?.number,t.meta?.invoice?.invoice_no,t.meta?.invoice?.invoiceNo,t.meta?.invoice?.invoice_number,t.offline_invoice_number,t.local_invoice_no,Tt(_)),orderNo:d(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:d(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:d(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:d(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:d(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:d(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:nt(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:nt(t.batch_codes,t.batchCodes),items:y,subtotal:k,discount:w,tax:v,grandTotal:T,paidAmount:L(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,T),paymentMethod:Ct(t),invoiceUrl:_,invoiceQr:d(e.invoiceQr,t.invoice_qr,t.invoiceQr,g.qr_svg_or_url,t.qr),reviewQr:d(e.reviewQr,t.review_qr,t.reviewQr),notes:d(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},Ut=(t,e={})=>{const o=e.paperSize||"80mm",n=dt(o),s=o==="58mm",r=e.agentPdf===!0,i=e.customPrintInvoice===!0,a=e.hideInvoiceQr===!0,l=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],p=Array.isArray(t.batchCodes)?t.batchCodes:[],g=Y(),y=!(t.simpleBilling??g.simpleBilling),k=i?ct(t.shopName):t.shopName,w=i?lt(t.shopAddress):t.shopAddress,v=d(t.invoiceNo,t.orderNo),T=i?s?"48px":"64px":n.paychatLogoWidth,f=a?"":Ot(t.invoiceQr||t.reviewQr),S=t.invoiceUrl&&(a||!f)?`<div class="qr-url">${c(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
      line-height: ${i?"1.08":"1.28"};
    }
    .receipt {
      width: ${n.width};
      padding: ${i?"2px 4px":n.padding};
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { font-size: 0.88em; }
    .powered { font-size: ${i?"0.72em":"0.88em"}; }
    .title {
      color: #000;
      font-size: ${i?s?"15px":"18px":n.titleSize};
      font-weight: ${i?"900":"800"};
      text-transform: ${i?"none":"uppercase"};
      ${i?"text-shadow: 0 0 0 #000, 0.25px 0 #000, -0.25px 0 #000; -webkit-text-stroke: 0.25px #000;":""}
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
      max-width: ${T};
      max-height: ${s?"20px":"26px"};
      object-fit: contain;
      margin: 2px auto 1px;
    }
    .bill-no {
      font-size: 1.15em;
      font-weight: ${i?"900":"700"};
      text-align: center;
      margin: ${i?"1px 0":"3px 0"};
      word-break: break-word;
    }
    .bill-no-row td {
      color: #000;
      font-weight: 900;
      padding-top: 0;
    }
    .line {
      border-top: 1px dashed #000;
      margin: ${i?"2px 0":"6px 0"};
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
      padding: ${i?"1px 0":"3px 0"};
      border-bottom: 1px dotted #999;
    }
    .item-meta,
    .total-row {
      display: flex;
      justify-content: space-between;
      gap: 6px;
    }
    .grand {
      border-top: ${i?"2px solid #000":"1px dashed #000"};
      color: #000;
      padding-top: ${i?"3px":"5px"};
      margin-top: ${i?"2px":"4px"};
      font-weight: 900;
      font-size: ${i?"1.22em":"1.12em"};
      ${i?"text-shadow: 0.25px 0 #000, -0.25px 0 #000;":""}
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
    ${i&&t.tokenNo?`<div class="top-token">TOKEN ${c(t.tokenNo)}</div>`:""}
    <div class="center">
      ${!i&&t.shopLogoUrl?`<img class="shop-logo" src="${E(t.shopLogoUrl)}" alt="${E(k)}" />`:""}
      <div class="title">${c(k)}</div>
      ${!i&&t.locationName?`<div class="muted">${c(t.locationName)}</div>`:""}
      ${w?`<div class="muted">${c(w)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${c(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${v&&!i?`<div class="bill-no">INVOICE NO: ${c(v)}</div>`:""}
    <table>
	      ${v&&i?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${c(v)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${c(pt(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${c(t.orderType)}</td></tr>`:""}
	      ${y&&t.tableName?`<tr><td>Table</td><td class="right">${c(t.tableName)}</td></tr>`:""}
	      ${y&&t.guestCount&&!i?`<tr><td>Guests</td><td class="right">${c(t.guestCount)}</td></tr>`:""}
	      ${y&&t.tokenNo&&!i?`<tr><td>Token</td><td class="right">${c(t.tokenNo)}</td></tr>`:""}
	      ${y&&m.length?`<tr><td>KOT</td><td class="right">${c(m.join(", "))}</td></tr>`:""}
	      ${y&&p.length?`<tr><td>Batch</td><td class="right">${c(p.join(", "))}</td></tr>`:""}
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
    ${i?`
      <div class="total-row grand"><span>TOTAL</span><span>${c(u(t.grandTotal))}</span></div>
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${c(t.paymentMethod)}</span></div>`:""}
    `:r?`
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
    ${f||S?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!a&&f?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${f||S}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!i?`<img class="paychat-logo" src="${E(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},$=(t,e="-")=>`${e.repeat(t)}
`,mt=(t="")=>`${$t}${wt}${t}${St}${xt}`,Rt=(t="")=>mt(t),x=(t,e)=>{const o=C(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},h=(t,e,o)=>{const n=C(e),s=Math.max(1,o-n.length-1),r=C(t).slice(0,s),i=Math.max(1,o-r.length-n.length);return`${r}${" ".repeat(i)}${n}
`},M=(t,e)=>{const o=C(t).split(/\s+/).filter(Boolean).flatMap(r=>r.length<=e?[r]:r.match(new RegExp(`.{1,${e}}`,"g"))||[r]),n=[];let s="";return o.forEach(r=>{if(!s){s=r;return}(s+" "+r).length<=e?s+=` ${r}`:(n.push(s),s=r.slice(0,e))}),s&&n.push(s),n.length?n:[""]},Mt=(t,e)=>{const o=M(t.name,e),n=`${R(t.qty)} x ${u(t.rate)}`;return[...o.map(s=>`${s}
`),h(n,u(t.total),e)].join("")},jt=(t,e)=>{const r=e-5-9-10,i=M(t.name,r),a=`${i[0].padEnd(r)}${R(t.qty).padStart(5)}${u(t.rate).padStart(9)}${u(t.total).padStart(10)}
`,l=i.slice(1).map(m=>`${m}
`).join("");return a+l},ht=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=dt(o),s=o==="58mm",r=e.customPrintInvoice===!0,i=e.hideInvoiceQr===!0,a=e.escposCommands===!0,l=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],p=Array.isArray(t.batchCodes)?t.batchCodes:[],g=Y(),_=t.simpleBilling??g.simpleBilling,y=s?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,k=r?ct(t.shopName):t.shopName,w=r?lt(t.shopAddress):t.shopAddress,v=d(t.invoiceNo,t.orderNo),T=x(k,n),f=w?M(w,n).map(P=>x(P,n)).join(""):"",S=v?h("Invoice No",v,n):"",b=r&&t.tokenNo?`${$(n)}${x(`TOKEN ${t.tokenNo}`,n)}${$(n)}`:"",bt=t.invoiceUrl?`${$(n)}${x(i?"Invoice link":"Invoice/review link",n)}${M(t.invoiceUrl,n).map(P=>`${C(P)}
`).join("")}`:"";return[b,a?Rt(T):T,!r&&t.locationName?x(t.locationName,n):"",f,t.shopPhone?x(`Phone: ${t.shopPhone}`,n):"",$(n),a?mt(S):S,h("Date",pt(t.dateTime),n),t.orderType?h("Type",t.orderType,n):"",!_&&t.tableName?h("Table",t.tableName,n):"",!_&&t.guestCount&&!r?h("Guests",t.guestCount,n):"",!_&&t.tokenNo&&!r?h("Token",t.tokenNo,n):"",!_&&m.length?h("KOT",m.join(","),n):"",!_&&p.length?h("Batch",p.join(","),n):"",$(n),y,y?$(n):"",l.length?l.map(P=>s?Mt(P,n):jt(P,n)).join(""):x("No items",n),$(n),r?"":h("Subtotal",u(t.subtotal),n),!r&&t.discount?h("Discount",`-${u(t.discount)}`,n):"",!r&&t.tax?h("Tax/GST",u(t.tax),n):"",r?"":$(n),h("TOTAL",u(t.grandTotal),n),t.paidAmount&&!r?h("Paid",u(t.paidAmount),n):"",t.paymentMethod?h("Payment",t.paymentMethod,n):"",bt,$(n),x("Thank you",n),x("Powered by PayChat",n)].join("")},ue=ht,gt="paychat_print_agent_settings",J={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},Bt=8e3,ot=12e3,Qt=["invoice_url","invoiceUrl","review_url","reviewUrl"],Wt=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},tt=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),Dt=t=>t==="80mm"?"80mm":"58mm",Gt=t=>t==="pdf"?"pdf":"escpos",N=(t={})=>({...J,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||J.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:Dt(t?.paperSize),printMode:Gt(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),A=()=>typeof localStorage>"u"?{...J}:N(Wt(localStorage.getItem(gt),{})),Ft=(t={})=>{const e=N({...A(),...t});try{localStorage.setItem(gt,JSON.stringify(e))}catch{}return e},F=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},W=(t,e={},o={})=>{const n=N(e),s=new URL(t,`${n.agentUrl}/`),r={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(r).forEach(([i,a])=>{a!=null&&a!==""&&s.searchParams.set(i,String(a))}),s.toString()},D=async(t,e={},o=Bt)=>{const n=new AbortController,s=setTimeout(()=>n.abort(),o);try{const r=await fetch(t,{...e,signal:n.signal}),a=(r.headers.get("content-type")||"").includes("application/json")?await r.json().catch(()=>null):await r.text().catch(()=>"");if(!r.ok)throw F(a?.message||a?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return a}catch(r){throw r?.name==="AbortError"?F("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",r):r?.code?r:F("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",r)}finally{clearTimeout(s)}},Kt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Ht=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),V=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Ht)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const r=V(t[s],e+1,o);if(r.length)return r}for(const s of Object.values(t)){const r=V(s,e+1,o);if(r.length)return r}return[]},O=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},I=(...t)=>{for(const e of t){const o=tt(e).trim();if(o)return o}return""},G=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Kt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,V(t))},j=(t={})=>I(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),z=(t={})=>O(t.quantity,t.qty,t.pivot?.quantity,1)||1,B=(t={})=>{const e=z(t),o=I(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=I(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},ft=(t={})=>{const e=I(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):B(t)*z(t)},Jt=(t=[])=>t.map(e=>({...e,product_name:j(e),name:j(e),quantity:z(e),qty:z(e),rate:B(e),price:B(e),total:ft(e)})),Vt=(t,e)=>{const o=tt(t);if(o.length<=e)return[o];const n=[];for(let s=0;s<o.length;s+=e)n.push(o.slice(s,s+e));return n},Yt=(t,e)=>{const o=e==="80mm"?48:32;return tt(t).split(/\r?\n/).flatMap(n=>Vt(n,o)).join(`
`)},Xt=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=G(t);return n.length?n.map(s=>{const r=j(s),i=z(s),a=B(s),m=ft(s).toFixed(2),p=`${i} x ${a.toFixed(2)}`,g=Math.max(1,o-p.length-m.length);return`${r}
${p}${" ".repeat(g)}${m}`}).join(`
`):""},Zt=(t,e,o)=>{const n=G(e);return!n.length||n.some(r=>{const i=j(r);return i&&t.includes(i.slice(0,Math.min(i.length,12)))})?t:`${t}
${Xt(e,o)}`},te=(t,e)=>{if(/total/i.test(t))return t;const o=O(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,G(e).reduce((n,s)=>{const r=O(s.quantity,s.qty,1)||1,i=O(s.rate,s.price,s.unit_price);return n+O(s.total,s.line_total,s.amount,r*i)},0));return`${t}
TOTAL ${o.toFixed(2)}`},st=t=>`\x1BE${t}\x1BE\0`,ee=(t="",e={})=>{const o=I(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(n=>{const s=n.trim();return s&&(o&&s.toLowerCase()===o.toLowerCase()||/^invoice no\b/i.test(s)||/^total\b/i.test(s))?st(n):n}).join(`
`)},ne=(t={},e={})=>{for(const o of Qt){const n=I(t[o],e[o]);if(n)return n}return I(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},oe=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},yt=(t={},e={},o=A())=>{const n=N(o),s=n.paperSize,r={...t||{},items:Jt(G(t||{}))},i=zt(r,e||{}),a={paperSize:s,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr,escposCommands:n.printMode==="escpos"};let l=ht(i,a);const m=Ut(i,{...a,agentPdf:n.printMode==="pdf"});typeof l!="string"&&(l=String(l??"")),l=Zt(l,r,s),l=te(l,r),l=Yt(l,s),n.customPrintInvoice&&(l=ee(l,i)),l.length>ot&&(l=`${l.slice(0,ot)}
--- Receipt truncated ---`),l=l.replace(/\n*$/,`


`);const p=ne(t,i),g={text:l,html:m,print_mode:n.printMode};return!n.hideInvoiceQr&&p&&oe(p)&&(g.qr={data:p,size:6,error_correction:"M"}),g},se=async(t=A())=>{const e=N(t);return D(W("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},re=async(t=A())=>{const e=N(t),o=await D(W("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},ie=async(t=A())=>{const e=N(t);return D(W("/test-print",e),{method:"POST"})},ae=async(t={},e={})=>{const o=N(e.settings||A()),n=yt(t,e.context||{},o);return D(W("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},me={getSettings:A,saveSettings:Ft,checkHealth:se,getPrinters:re,testPrint:ie,printReceipt:ae,buildSafeAgentReceiptPayload:yt},he={list(t={}){return q.get("/upi-profiles",{params:t})},create(t){return q.post("/upi-profiles",t)},update(t,e){return q.patch(`/upi-profiles/${t}`,e)},deactivate(t){return q.delete(`/upi-profiles/${t}`)},setDefault(t){return q.patch(`/upi-profiles/${t}/default`)}},Q="paychat_pos_wake_lock_enabled",rt=()=>{try{return localStorage.getItem(Q)==="true"}catch{return!1}},ge=t=>{try{return t?(localStorage.setItem(Q,"true"),!0):(localStorage.removeItem(Q),!1)}catch{return!1}},ce=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},fe=()=>{let t=null,e=!1,o=!1,n=0;const s=async()=>{try{await t?.release?.()}catch(p){console.warn("POS wake lock release failed:",p)}finally{t=null}},r=()=>{const p=ce();return p.supported?!0:(o||(console.warn(`POS wake lock unavailable: ${p.reason}`),o=!0),!1)},i=async()=>{const p=Date.now();if(!(e||t||!rt()||!r()||document.visibilityState!=="visible")&&!(p-n<750)){n=p;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(g){console.warn("POS wake lock failed:",g)}}},a=()=>{i()},l=()=>{document.visibilityState==="visible"?i():s()},m=p=>{p.key===Q&&(rt()?i():s())};return document.addEventListener("visibilitychange",l),document.addEventListener("pointerdown",a,{passive:!0}),document.addEventListener("touchstart",a,{passive:!0}),document.addEventListener("click",a,{passive:!0}),window.addEventListener("storage",m),i(),()=>{e=!0,document.removeEventListener("visibilitychange",l),document.removeEventListener("pointerdown",a),document.removeEventListener("touchstart",a),document.removeEventListener("click",a),window.removeEventListener("storage",m),s()}},le="paychat-pos",U="cache",K=vt(le,1,{upgrade(t){t.createObjectStore(U)}}),ye={async set(t,e){await(await K).put(U,e,t)},async get(t){return await(await K).get(U,t)},async clear(){await(await K).clear(U)}};export{Ut as a,ue as b,ye as c,ge as d,ce as e,rt as g,zt as n,me as p,fe as s,he as u};
