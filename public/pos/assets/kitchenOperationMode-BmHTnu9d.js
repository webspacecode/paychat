import{g as ot,b as h}from"./index-BcmOPvyt.js";const zt="/color-paychat-logo-main.svg",Mt="\x1BE",Dt="\x1BE\0",Rt="\x1BG",Ut="\x1BG\0",Bt=4,ct={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},mt=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},st=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},Kt=()=>mt(st("tenant_info"),{}),jt=()=>mt(st("selected_location"),{}),ht=t=>M(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),gt=t=>ht(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),ft=t=>{const e=ht(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},l=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),q=t=>l(t).replace(/`/g,"&#096;"),M=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),d=t=>Number(t||0).toFixed(2),W=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},bt=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},_t=(t="80mm")=>ct[t]||ct["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},Qt=t=>{const e=String(t||"").trim();if(!e)return"";try{const o=typeof window<"u"?window.location.origin:"https://paychat.local",s=new URL(e,o).pathname.split("/").map(a=>a.trim()).filter(Boolean),r=s.findIndex(a=>["invoice","invoices"].includes(a.toLowerCase())),i=r>=0?s[r+1]:s[s.length-1];return decodeURIComponent(i||"").trim()}catch{const s=e.split("?")[0].split("#")[0].split("/").map(r=>r.trim()).filter(Boolean);return s[s.length-1]||""}},L=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},Ft=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Gt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),tt=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Gt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const r=tt(t[s],e+1,o);if(r.length)return r}for(const s of Object.values(t)){const r=tt(s,e+1,o);if(r.length)return r}return[]},Wt=(t={})=>p(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),rt=(t={})=>L(t.quantity,t.qty,t.pivot?.quantity,1)||1,yt=(t={})=>{const e=rt(t),o=p(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=p(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},Ht=(t={})=>{const e=p(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):yt(t)*rt(t)},Jt=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Ft(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,tt(t))},Vt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},lt=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=p(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},Yt=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},Xt=t=>{if(!t)return"";const e=Yt(t),o=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const s=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${q(s)}" alt="Invoice QR" />`}const n=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return n?.[1]?`<img class="qr-image" src="${q(n[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${q(e)}" alt="Invoice QR" />`:`<div class="qr-url">${l(e)}</div>`},Zt=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=Kt(),n=ot(),s=o?.tenant||{},r=o?.branding||s?.branding||o?.branching||{},i=jt(),a={...i&&typeof i=="object"?i:{},...t.location&&typeof t.location=="object"?t.location:{}};t.branch||t.branching||t.branding||o?.branch||o?.branching;const c=t.merchant||t.receipt?.merchant||{},u=t.invoice||t.invoice_data||t.receipt?.invoice||{},m=t.qr||t.receipt?.qr||{},_=p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,u.url,t.meta?.invoice?.url,m.invoice_url),N=Jt(t).map($=>({name:Wt($),qty:rt($),rate:yt($),total:Ht($)})),y=L(t.subtotal,t.totals?.subtotal,N.reduce(($,S)=>$+S.rate*S.qty,0)),A=L(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),k=L(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),v=L(t.total,t.grand_total,t.totals?.grand_total,y+k-A);return{shopName:p(e.shopName,c.name,a.tenant?.name,t.tenant?.name,r.company_name,s.name,st("tenant_slug"),"PayChat POS"),shopPhone:p(e.shopPhone,c.phone,a.phone,r.phone,s.phone),shopAddress:p(e.shopAddress,r.address,s.branding?.address,t.tenant?.branding?.address,a.tenant?.branding?.address),shopLogoUrl:p(e.shopLogoUrl,a.logo,a.tenant?.logo,t.tenant?.logo,r.logo,s.logo),locationName:p(a.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,zt),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,u.number,u.invoice_no,u.invoiceNo,u.invoice_number,u.offline_invoice_number,t.meta?.invoice?.number,t.meta?.invoice?.invoice_no,t.meta?.invoice?.invoiceNo,t.meta?.invoice?.invoice_number,t.offline_invoice_number,t.local_invoice_no,Qt(_)),orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table_display,t.tableDisplay,t.table_session?.table_display,t.tableSession?.tableDisplay,t.table_session?.table?.name,t.tableSession?.table?.name,t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:lt(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:lt(t.batch_codes,t.batchCodes),items:N,subtotal:y,discount:A,tax:k,grandTotal:v,paidAmount:L(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,v),paymentMethod:Vt(t),invoiceUrl:_,invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,m.qr_svg_or_url,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},te=(t,e={})=>{const o=e.paperSize||"80mm",n=_t(o),s=o==="58mm",r=e.agentPdf===!0,i=e.customPrintInvoice===!0,a=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],u=Array.isArray(t.kotCodes)?t.kotCodes:[],m=Array.isArray(t.batchCodes)?t.batchCodes:[],_=ot(),y=!(t.simpleBilling??_.simpleBilling),A=i?gt(t.shopName):t.shopName,k=i?ft(t.shopAddress):t.shopAddress,v=p(t.invoiceNo),$=i?s?"48px":"64px":n.paychatLogoWidth,S=a?"":Xt(t.invoiceQr||t.reviewQr),D=t.invoiceUrl&&(a||!S)?`<div class="qr-url">${l(t.invoiceUrl)}</div>`:"";return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Thermal Bill</title>
  <style>
    @page { size: ${n.width} auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0 0 ${i?"28px":"32px"};
      background: #fff;
      color: #000;
      font-family: "Courier New", monospace;
      font-size: ${n.fontSize};
      line-height: ${i?"1.08":"1.28"};
    }
    .receipt {
      width: ${n.width};
      padding: ${i?"2px 4px 24px":n.padding};
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
      max-width: ${$};
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
    ${i&&t.tokenNo?`<div class="top-token">TOKEN ${l(t.tokenNo)}</div>`:""}
    <div class="center">
      ${!i&&t.shopLogoUrl?`<img class="shop-logo" src="${q(t.shopLogoUrl)}" alt="${q(A)}" />`:""}
      <div class="title">${l(A)}</div>
      ${!i&&t.locationName?`<div class="muted">${l(t.locationName)}</div>`:""}
      ${k?`<div class="muted">${l(k)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${l(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${v&&!i?`<div class="bill-no">INVOICE NO: ${l(v)}</div>`:""}
    <table>
	      ${v&&i?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${l(v)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${l(bt(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${l(t.orderType)}</td></tr>`:""}
	      ${(y||i)&&t.tableName?`<tr><td>Table</td><td class="right">${l(t.tableName)}</td></tr>`:""}
	      ${y&&t.guestCount&&!i?`<tr><td>Guests</td><td class="right">${l(t.guestCount)}</td></tr>`:""}
	      ${y&&t.tokenNo&&!i?`<tr><td>Token</td><td class="right">${l(t.tokenNo)}</td></tr>`:""}
	      ${y&&u.length?`<tr><td>KOT</td><td class="right">${l(u.join(", "))}</td></tr>`:""}
	      ${y&&m.length?`<tr><td>Batch</td><td class="right">${l(m.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${s?`
      <div>
        ${c.length?c.map(x=>`
          <div class="item-block">
            <div class="item-name">${l(x.name)}</div>
            <div class="item-meta">
              <span>${l(W(x.qty))} x ${l(d(x.rate))}</span>
              <strong>${l(d(x.total))}</strong>
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
          ${c.length?c.map(x=>`
            <tr>
              <td class="item-name">${l(x.name)}</td>
              <td class="right">${l(W(x.qty))}</td>
              <td class="right">${l(d(x.rate))}</td>
              <td class="right">${l(d(x.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${i?`
      <div class="total-row grand"><span>TOTAL</span><span>${l(d(t.grandTotal))}</span></div>
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${l(t.paymentMethod)}</span></div>`:""}
    `:r?`
      <table class="pdf-totals">
        <tbody>
          <tr><td>Subtotal</td><td class="pdf-total-value">${l(d(t.subtotal))}</td></tr>
          ${t.discount?`<tr><td>Discount</td><td class="pdf-total-value">-${l(d(t.discount))}</td></tr>`:""}
          ${t.tax?`<tr><td>Tax/GST</td><td class="pdf-total-value">${l(d(t.tax))}</td></tr>`:""}
          <tr class="grand"><td>TOTAL</td><td class="pdf-total-value">${l(d(t.grandTotal))}</td></tr>
          ${t.paidAmount?`<tr><td>Paid</td><td class="pdf-total-value">${l(d(t.paidAmount))}</td></tr>`:""}
          ${t.paymentMethod?`<tr><td>Payment</td><td class="pdf-total-value">${l(t.paymentMethod)}</td></tr>`:""}
        </tbody>
      </table>
    `:`
      <div class="total-row"><span>Subtotal</span><span>${l(d(t.subtotal))}</span></div>
      ${t.discount?`<div class="total-row"><span>Discount</span><span>-${l(d(t.discount))}</span></div>`:""}
      ${t.tax?`<div class="total-row"><span>Tax/GST</span><span>${l(d(t.tax))}</span></div>`:""}
      <div class="total-row grand"><span>TOTAL</span><span>${l(d(t.grandTotal))}</span></div>
      ${t.paidAmount?`<div class="total-row"><span>Paid</span><span>${l(d(t.paidAmount))}</span></div>`:""}
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${l(t.paymentMethod)}</span></div>`:""}
    `}
    ${S||D?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!a&&S?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${S||D}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!i?`<img class="paychat-logo" src="${q(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},T=(t,e="-")=>`${e.repeat(t)}
`,$t=(t="")=>`${Mt}${Rt}${t}${Ut}${Dt}`,ee=(t="")=>$t(t),I=(t,e)=>{const o=M(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},g=(t,e,o)=>{const n=M(e),s=Math.max(1,o-n.length-1),r=M(t).slice(0,s),i=Math.max(1,o-r.length-n.length);return`${r}${" ".repeat(i)}${n}
`},H=(t,e)=>{const o=M(t).split(/\s+/).filter(Boolean).flatMap(r=>r.length<=e?[r]:r.match(new RegExp(`.{1,${e}}`,"g"))||[r]),n=[];let s="";return o.forEach(r=>{if(!s){s=r;return}(s+" "+r).length<=e?s+=` ${r}`:(n.push(s),s=r.slice(0,e))}),s&&n.push(s),n.length?n:[""]},ne=(t,e)=>{const o=H(t.name,e),n=`${W(t.qty)} x ${d(t.rate)}`;return[...o.map(s=>`${s}
`),g(n,d(t.total),e)].join("")},oe=(t,e)=>{const r=e-5-9-10,i=H(t.name,r),a=`${i[0].padEnd(r)}${W(t.qty).padStart(5)}${d(t.rate).padStart(9)}${d(t.total).padStart(10)}
`,c=i.slice(1).map(u=>`${u}
`).join("");return a+c},xt=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=_t(o),s=o==="58mm",r=e.customPrintInvoice===!0,i=e.hideInvoiceQr===!0,a=e.escposCommands===!0,c=Array.isArray(t.items)?t.items:[],u=Array.isArray(t.kotCodes)?t.kotCodes:[],m=Array.isArray(t.batchCodes)?t.batchCodes:[],_=ot(),N=t.simpleBilling??_.simpleBilling,y=s?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,A=r?gt(t.shopName):t.shopName,k=r?ft(t.shopAddress):t.shopAddress,v=p(t.invoiceNo),$=I(A,n),S=k?H(k,n).map(C=>I(C,n)).join(""):"",D=v?g("Invoice No",v,n):"",x=r&&t.tokenNo?`${T(n)}${I(`TOKEN ${t.tokenNo}`,n)}${T(n)}`:"",qt=t.invoiceUrl?`${T(n)}${I(i?"Invoice link":"Invoice/review link",n)}${H(t.invoiceUrl,n).map(C=>`${M(C)}
`).join("")}`:"";return[x,a?ee($):$,!r&&t.locationName?I(t.locationName,n):"",S,t.shopPhone?I(`Phone: ${t.shopPhone}`,n):"",T(n),a?$t(D):D,g("Date",bt(t.dateTime),n),t.orderType?g("Type",t.orderType,n):"",(!N||r)&&t.tableName?g("Table",t.tableName,n):"",!N&&t.guestCount&&!r?g("Guests",t.guestCount,n):"",!N&&t.tokenNo&&!r?g("Token",t.tokenNo,n):"",!N&&u.length?g("KOT",u.join(","),n):"",!N&&m.length?g("Batch",m.join(","),n):"",T(n),y,y?T(n):"",c.length?c.map(C=>s?ne(C,n):oe(C,n)).join(""):I("No items",n),T(n),r?"":g("Subtotal",d(t.subtotal),n),!r&&t.discount?g("Discount",`-${d(t.discount)}`,n):"",!r&&t.tax?g("Tax/GST",d(t.tax),n):"",r?"":T(n),g("TOTAL",d(t.grandTotal),n),t.paidAmount&&!r?g("Paid",d(t.paidAmount),n):"",t.paymentMethod?g("Payment",t.paymentMethod,n):"",qt,T(n),I("Thank you",n),I("Powered by PayChat",n),...Array(r?Bt:3).fill(`
`)].join("")},Fe=xt,vt="\x1BE",Nt="\x1BE\0",se="\x1Ba\0",re="\x1Ba",ie="!",ae="!\0",ce=2,le=8,f=(t="")=>String(t??"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,"").replace(/\s+/g," ").trim(),b=(...t)=>{for(const e of t){const o=f(e);if(o)return o}return""},pe=(t="58mm")=>t==="80mm"?48:32,R=(t,e="-")=>e.repeat(t),K=(t,e=!0)=>e?`${vt}${t}${Nt}`:t,de=(t,e=!0)=>e?`${ie}${vt}${t}${Nt}${ae}`:t,ue=(t,e)=>{const o=f(t),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}`},X=(t,e,o=!0)=>o?`${re}${t}${se}`:ue(t,e),U=(t,e,o)=>{const n=f(t),s=f(e),r=Math.max(1,o-n.length-s.length);return`${n}${" ".repeat(r)}${s}`},it=(t,e,o="")=>{const n=f(t);if(!n)return[];const s=Math.max(8,e-o.length),r=[],i=n.split(" ");let a="";return i.forEach(c=>{if(!a){a=c;return}if(`${a} ${c}`.length<=s){a=`${a} ${c}`;return}r.push(a),a=c}),a&&r.push(a),r.flatMap(c=>{if(c.length<=s)return[`${o}${c}`];const u=[];for(let m=0;m<c.length;m+=s)u.push(`${o}${c.slice(m,m+s)}`);return u})},G=(t={})=>t&&typeof t=="object"?b(t.table_display,t.tableDisplay,t.name,t.code,t.table_name,t.tableName):"",me=(t={})=>{const e=[t,t.order,t.table_session,t.tableSession,t.order?.table_session,t.order?.tableSession,t.table,t.order?.table].filter(Boolean);for(const o of e){const n=b(o.table_display,o.tableDisplay,o.table_group_label,o.tableGroupLabel);if(n)return n}for(const o of e){const s=(Array.isArray(o.tables)?o.tables:[]).map(G).filter(Boolean);if(s.length)return s.join(" + ")}for(const o of e){const n=Array.isArray(o.linked_tables)?o.linked_tables:Array.isArray(o.linkedTables)?o.linkedTables:[],s=[G(o.primary_table||o.primaryTable),G(o.table),...n.map(G)].filter(Boolean);if(s.length)return[...new Set(s)].join(" + ")}for(const o of e){const n=b(o.table_name,o.tableName,o.name,o.code);if(n)return n}return""},St=(t={})=>b(t.product_name,t.name,t.product?.name,t.item_name,"Item"),Tt=(t={})=>{const e=Number(t.quantity??t.qty??1);return Number.isFinite(e)&&e>0?e:1},It=t=>Number.isInteger(t)?String(t):String(t).replace(/\.0+$/,""),he=(t={},e)=>[b(t.variant,t.variant_name),...Array.isArray(t.modifiers)?t.modifiers.map(n=>b(n.name,n.label,n)):[],b(t.notes,t.note,t.kitchen_note,t.instructions)].filter(Boolean).flatMap(n=>it(n,e,"  - ")),Et=(t={})=>{const e=t.print_data||t.printData||t.batch||t,o=b(e.batch_code,e.batchCode,e.code,`KOT-${e.id||e.batch_id||""}`);return{outlet:b(e.outlet,e.store_name,e.location?.name,e.location_name),code:o,tokenNo:b(e.token_no,e.tokenNo,e.token_number,e.tokenNumber,e.token?.token_code,e.token?.token_no,e.order?.token?.token_code,e.order?.token_no,o),orderNo:b(e.order?.order_no,e.order_no,e.orderNo,e.order?.id,e.order_id),table:me(e),status:b(e.status,"waiting"),time:b(e.sent_at,e.created_at,new Date().toISOString()),orderNotes:b(e.order?.notes,e.notes,e.table_notes),items:Array.isArray(e.items)?e.items:[]}},ge=(t={},e,o=!0)=>{const s=`${It(Tt(t))} x`,r=" ".repeat(Math.min(7,s.length+2)),i=it(St(t),e-r.length);return i.length?[`${K(s.padEnd(r.length-1),o)} ${i[0].trim()}`,...i.slice(1).map(a=>`${r}${a.trim()}`)]:[K(s,o)]},fe=(t={},e={})=>{const o=e.paperSize||"58mm",n=pe(o),s=e.escposCommands===!0,r=Et(t),i=[];return r.outlet&&i.push(X(K(r.outlet.toUpperCase(),s),n,s)),i.push(X(K("KITCHEN ORDER TOKEN",s),n,s)),i.push(R(n)),i.push(X(de(`TOKEN ${r.tokenNo||r.code}`,s),n,s)),i.push(R(n)),i.push(U("KOT",r.code,n)),r.orderNo&&i.push(U("Order",r.orderNo,n)),r.table&&i.push(U("Table",r.table,n)),i.push(U("Status",r.status,n)),i.push(U("Time",r.time.replace("T"," ").slice(0,16),n)),i.push(R(n)),r.items.forEach(a=>{i.push(...ge(a,n,s)),i.push(...he(a,n)),i.push(...Array(ce).fill(""))}),r.orderNotes&&(i.push(R(n)),i.push(K("Notes",s)),i.push(...it(r.orderNotes,n))),i.push(R(n)),i.push(...Array(le).fill("")),i.join(`
`)},be=(t={})=>{const e=Et(t),o=e.items.map(n=>`
    <div class="item">
      <div class="qty">${f(It(Tt(n)))} x</div>
      <div class="name">${f(St(n))}</div>
    </div>
  `).join("");return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: monospace; width: 280px; margin: 0; padding: 0 0 56px; color: #111; }
    h1, h2, p { margin: 0; }
    h1 { font-size: 18px; text-align: center; }
    h2 { font-size: 14px; text-align: center; margin-bottom: 8px; }
    .token { margin: 8px 0; text-align: center; font-size: 26px; font-weight: 900; line-height: 1.05; }
    .line { border-top: 1px dashed #111; margin: 8px 0; }
    .meta { display: flex; justify-content: space-between; gap: 8px; font-size: 12px; }
    .meta span { text-align: right; overflow-wrap: anywhere; }
    .items { font-size: 15px; }
    .item { display: flex; align-items: flex-start; gap: 8px; padding: 6px 0 14px; margin-bottom: 4px; border-bottom: 1px dotted #ddd; }
    .qty { flex: 0 0 42px; font-size: 16px; font-weight: 900; }
    .name { flex: 1; font-weight: 800; line-height: 1.25; overflow-wrap: anywhere; word-break: break-word; }
    .notes { font-size: 12px; margin-top: 8px; }
  </style>
</head>
<body>
  ${e.outlet?`<h1>${f(e.outlet).toUpperCase()}</h1>`:""}
  <h2>KITCHEN ORDER TOKEN</h2>
  <div class="line"></div>
  <div class="token">TOKEN ${f(e.tokenNo||e.code)}</div>
  <div class="line"></div>
  <p class="meta"><strong>KOT</strong><span>${f(e.code)}</span></p>
  ${e.orderNo?`<p class="meta"><strong>Order</strong><span>${f(e.orderNo)}</span></p>`:""}
  ${e.table?`<p class="meta"><strong>Table</strong><span>${f(e.table)}</span></p>`:""}
  <p class="meta"><strong>Status</strong><span>${f(e.status)}</span></p>
  <p class="meta"><strong>Time</strong><span>${f(e.time.replace("T"," ").slice(0,16))}</span></p>
  <div class="line"></div>
  <div class="items">${o}</div>
  ${e.orderNotes?`<div class="line"></div><p class="notes"><strong>Notes:</strong> ${f(e.orderNotes)}</p>`:""}
  <div class="line"></div>
</body>
</html>`},_e=(t={},e={})=>({text:fe(t,e),html:be(t),print_mode:e.printMode||"escpos"}),kt="paychat_print_agent_settings",et={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},ye=8e3,pt=12e3,$e=4,xe=["invoice_url","invoiceUrl","review_url","reviewUrl"],ve=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},at=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),Ne=t=>t==="80mm"?"80mm":"58mm",Se=t=>t==="pdf"?"pdf":"escpos",E=(t={})=>({...et,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||et.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:Ne(t?.paperSize),printMode:Se(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),w=()=>typeof localStorage>"u"?{...et}:E(ve(localStorage.getItem(kt),{})),Te=(t={})=>{const e=E({...w(),...t});try{localStorage.setItem(kt,JSON.stringify(e))}catch{}return e},Z=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},Q=(t,e={},o={})=>{const n=E(e),s=new URL(t,`${n.agentUrl}/`),r={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(r).forEach(([i,a])=>{a!=null&&a!==""&&s.searchParams.set(i,String(a))}),s.toString()},F=async(t,e={},o=ye)=>{const n=new AbortController,s=setTimeout(()=>n.abort(),o);try{const r=await fetch(t,{...e,signal:n.signal}),a=(r.headers.get("content-type")||"").includes("application/json")?await r.json().catch(()=>null):await r.text().catch(()=>"");if(!r.ok)throw Z(a?.message||a?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return a}catch(r){throw r?.name==="AbortError"?Z("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",r):r?.code?r:Z("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",r)}finally{clearTimeout(s)}},Ie=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Ee=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),nt=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Ee)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const r=nt(t[s],e+1,o);if(r.length)return r}for(const s of Object.values(t)){const r=nt(s,e+1,o);if(r.length)return r}return[]},B=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},P=(...t)=>{for(const e of t){const o=at(e).trim();if(o)return o}return""},Y=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Ie(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,nt(t))},J=(t={})=>P(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),j=(t={})=>B(t.quantity,t.qty,t.pivot?.quantity,1)||1,V=(t={})=>{const e=j(t),o=P(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=P(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},wt=(t={})=>{const e=P(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):V(t)*j(t)},ke=(t=[])=>t.map(e=>({...e,product_name:J(e),name:J(e),quantity:j(e),qty:j(e),rate:V(e),price:V(e),total:wt(e)})),we=(t,e)=>{const o=at(t);if(o.length<=e)return[o];const n=[];for(let s=0;s<o.length;s+=e)n.push(o.slice(s,s+e));return n},Ae=(t,e)=>{const o=e==="80mm"?48:32;return at(t).split(/\r?\n/).flatMap(n=>we(n,o)).join(`
`)},Pe=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=Y(t);return n.length?n.map(s=>{const r=J(s),i=j(s),a=V(s),u=wt(s).toFixed(2),m=`${i} x ${a.toFixed(2)}`,_=Math.max(1,o-m.length-u.length);return`${r}
${m}${" ".repeat(_)}${u}`}).join(`
`):""},Oe=(t,e,o)=>{const n=Y(e);return!n.length||n.some(r=>{const i=J(r);return i&&t.includes(i.slice(0,Math.min(i.length,12)))})?t:`${t}
${Pe(e,o)}`},Ce=(t,e)=>{if(/total/i.test(t))return t;const o=B(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,Y(e).reduce((n,s)=>{const r=B(s.quantity,s.qty,1)||1,i=B(s.rate,s.price,s.unit_price);return n+B(s.total,s.line_total,s.amount,r*i)},0));return`${t}
TOTAL ${o.toFixed(2)}`},dt=t=>`\x1BE${t}\x1BE\0`,Le=(t="",e={})=>{const o=P(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(n=>{const s=n.trim();return s&&(o&&s.toLowerCase()===o.toLowerCase()||/^invoice no\b/i.test(s)||/^total\b/i.test(s))?dt(n):n}).join(`
`)},qe=(t={},e={})=>{for(const o of xe){const n=P(t[o],e[o]);if(n)return n}return P(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},ze=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},At=(t={},e={},o=w())=>{const n=E(o),s=n.paperSize,r={...t||{},items:ke(Y(t||{}))},i=Zt(r,e||{}),a={paperSize:s,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr,escposCommands:n.printMode==="escpos"};let c=xt(i,a);const u=te(i,{...a,agentPdf:n.printMode==="pdf"});typeof c!="string"&&(c=String(c??"")),c=Oe(c,r,s),c=Ce(c,r),c=Ae(c,s),n.customPrintInvoice&&(c=Le(c,i)),c.length>pt&&(c=`${c.slice(0,pt)}
--- Receipt truncated ---`),c=c.replace(/\n*$/,`
`.repeat(n.customPrintInvoice?$e:3));const m=qe(t,i),_={text:c,html:u,print_mode:n.printMode};return!n.hideInvoiceQr&&m&&ze(m)&&(_.qr={data:m,size:6,error_correction:"M"}),_},Me=async(t=w())=>{const e=E(t);return F(Q("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},De=async(t=w())=>{const e=E(t),o=await F(Q("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},Re=async(t=w())=>{const e=E(t);return F(Q("/test-print",e),{method:"POST"})},Ue=async(t={},e={})=>{const o=E(e.settings||w()),n=At(t,e.context||{},o);return F(Q("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},Be=async(t={},e={})=>{const o=E(e.settings||w()),n=_e(t,{paperSize:o.paperSize,printMode:o.printMode,escposCommands:o.printMode==="escpos"});return F(Q("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},Ge={getSettings:w,saveSettings:Te,checkHealth:Me,getPrinters:De,testPrint:Re,printReceipt:Ue,printKot:Be,buildSafeAgentReceiptPayload:At},We={createOrder(t){return h.post("/orders",t)},diningStructure(t={}){return h.get("/dining-structure",{params:t})},bulkSaveTables(t){return h.post("/dining-structure/tables/bulk",t)},updateTablePosition(t,e){return h.patch(`/dining-structure/tables/${t}/position`,e)},list(t={}){return h.get("/tables",{params:t})},update(t,e){return h.patch(`/tables/${t}`,e)},updateStatus(t,e){return h.patch(`/tables/${t}/status`,e)},release(t,e={}){return h.post(`/tables/${t}/release`,e)},createSession(t){return h.post("/table-sessions",t)},openSessions(t={}){return h.get("/table-sessions/open",{params:t})},closeSession(t,e={}){return h.post(`/table-sessions/${t}/close`,e)},assignOrder(t,e){return h.patch(`/orders/${t}/table`,e)},linkOrderTables(t,e){return h.post(`/orders/${t}/tables/link`,e)},sendToKitchen(t,e={}){return h.post(`/orders/${t}/send-to-kitchen`,e)},printKot(t){return h.post(`/orders/${t}/print-kot`)},reprintKitchenBatch(t){return h.post(`/kitchen-batches/${t}/reprint`)},cancelKitchenBatch(t){return h.post(`/kitchen-batches/${t}/cancel`)},generateInlineToken(t){return h.post(`/orders/${t}/inline-token`)}},Pt="paychat_kitchen_operation_mode",Ot="paychat_generate_inline_kitchen_token",Ct="paychat_inline_kitchen_without_status_management",z={DEDICATED_KDS:"dedicated_kds",INLINE:"inline"},Lt=Object.values(z),O=()=>typeof window>"u"?null:window.localStorage||null,ut=t=>{try{const e=O()?.getItem(t);return e?JSON.parse(e):null}catch{return null}},Ke=()=>{const t=ut("tenant_settings")||{},e=ut("tenant_info")||{},o=t?.kitchen?.operation_mode||t?.raw?.kitchen_operation_mode||e?.settings?.kitchen?.operation_mode||e?.settings?.raw?.kitchen_operation_mode||e?.tenant?.settings?.kitchen?.operation_mode||e?.tenant?.settings?.raw?.kitchen_operation_mode;return Lt.includes(o)?o:null},je=()=>{const e=O()?.getItem(Pt);return Lt.includes(e)?e:Ke()||z.DEDICATED_KDS},He=t=>{const e=t===z.INLINE?z.INLINE:z.DEDICATED_KDS;return O()?.setItem(Pt,e),e},Je=()=>je()===z.INLINE,Ve=()=>O()?.getItem(Ot)==="true",Ye=t=>{const e=!!t;return O()?.setItem(Ot,e?"true":"false"),e},Xe=()=>O()?.getItem(Ct)==="true",Ze=t=>{const e=!!t;return O()?.setItem(Ct,e?"true":"false"),e};export{z as K,te as a,Fe as b,Xe as c,Ve as d,Ze as e,Ye as f,je as g,Je as i,Zt as n,Ge as p,He as s,We as t};
