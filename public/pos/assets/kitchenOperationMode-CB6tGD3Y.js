import{g as ot}from"./index-CuY6AFxk.js";const zt="/color-paychat-logo-main.svg",Mt="\x1BE",Ut="\x1BE\0",Qt="\x1BG",Rt="\x1BG\0",Bt=1,jt=3,ct={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},ht=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},st=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},Kt=()=>ht(st("tenant_info"),{}),Ft=()=>ht(st("selected_location"),{}),gt=t=>z(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),_t=t=>gt(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),ft=t=>{const e=gt(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},l=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),E=t=>l(t).replace(/`/g,"&#096;"),z=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),m=t=>Number(t||0).toFixed(2),W=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},bt=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},yt=(t="80mm")=>ct[t]||ct["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},Gt=t=>{const e=String(t||"").trim();if(!e)return"";try{const o=typeof window<"u"?window.location.origin:"https://paychat.local",s=new URL(e,o).pathname.split("/").map(r=>r.trim()).filter(Boolean),i=s.findIndex(r=>["invoice","invoices"].includes(r.toLowerCase())),a=i>=0?s[i+1]:s[s.length-1];return decodeURIComponent(a||"").trim()}catch{const s=e.split("?")[0].split("#")[0].split("/").map(i=>i.trim()).filter(Boolean);return s[s.length-1]||""}},L=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},Wt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Ht=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),tt=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Ht)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const s of n){const i=tt(t[s],e+1,o);if(i.length)return i}for(const s of Object.values(t)){const i=tt(s,e+1,o);if(i.length)return i}return[]},Jt=(t={})=>p(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),it=(t={})=>L(t.quantity,t.qty,t.pivot?.quantity,1)||1,$t=(t={})=>{const e=it(t),o=p(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=p(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},Vt=(t={})=>{const e=p(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):$t(t)*it(t)},Yt=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Wt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,o.items,o.order_items,o.line_items,o.cart_items,o.invoice_items,o.details,tt(t))},Xt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},lt=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=p(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},Zt=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},pt=(t,e="Invoice QR")=>{if(!t)return"";const o=Zt(t),n=o.match(/<svg\b[\s\S]*?<\/svg>/i);if(n){const i=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(n[0])}`;return`<img class="qr-image" src="${E(i)}" alt="${E(e)}" />`}const s=o.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return s?.[1]?`<img class="qr-image" src="${E(s[1])}" alt="${E(e)}" />`:/^(data:image\/|https?:\/\/|\/)/i.test(o)?`<img class="qr-image" src="${E(o)}" alt="${E(e)}" />`:`<div class="qr-url">${l(o)}</div>`},te=(t={})=>{const o=(Array.isArray(t.payments)?t.payments:[]).find(n=>String(n?.payment_method||n?.method||"").toLowerCase()==="upi"&&p(n.upi_qr_url,n.upiQrUrl,n.meta?.upi_qr_url,n.qr_payload,n.qr));return p(t.upi_qr_url,t.upiQrUrl,t.payment?.upi_qr_url,t.payment?.upiQrUrl,t.payment?.meta?.upi_qr_url,t.qr?.upi_qr_url,t.receipt?.qr?.upi_qr_url,o?.upi_qr_url,o?.upiQrUrl,o?.meta?.upi_qr_url,o?.qr_payload,o?.qr)},ee=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=Kt(),n=ot(),s=o?.tenant||{},i=o?.branding||s?.branding||o?.branching||{},a=Ft(),r={...a&&typeof a=="object"?a:{},...t.location&&typeof t.location=="object"?t.location:{}};t.branch||t.branching||t.branding||o?.branch||o?.branching;const c=t.merchant||t.receipt?.merchant||{},d=t.invoice||t.invoice_data||t.receipt?.invoice||{},u=t.qr||t.receipt?.qr||{},v=p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,d.url,t.meta?.invoice?.url,u.invoice_url),b=p(e.upiQr,e.paymentQr,te(t)),_=Yt(t).map(y=>({name:Jt(y),qty:it(y),rate:$t(y),total:Vt(y)})),P=L(t.subtotal,t.totals?.subtotal,_.reduce((y,N)=>y+N.rate*N.qty,0)),A=L(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),x=L(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),q=L(t.total,t.grand_total,t.totals?.grand_total,P+x-A);return{shopName:p(e.shopName,c.name,r.tenant?.name,t.tenant?.name,i.company_name,s.name,st("tenant_slug"),"PayChat POS"),shopPhone:p(e.shopPhone,c.phone,r.phone,i.phone,s.phone),shopAddress:p(e.shopAddress,i.address,s.branding?.address,t.tenant?.branding?.address,r.tenant?.branding?.address),shopLogoUrl:p(e.shopLogoUrl,r.logo,r.tenant?.logo,t.tenant?.logo,i.logo,s.logo),locationName:p(r.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,zt),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,d.number,d.invoice_no,d.invoiceNo,d.invoice_number,d.offline_invoice_number,t.meta?.invoice?.number,t.meta?.invoice?.invoice_no,t.meta?.invoice?.invoiceNo,t.meta?.invoice?.invoice_number,t.offline_invoice_number,t.local_invoice_no,Gt(v)),orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table_display,t.tableDisplay,t.table_session?.table_display,t.tableSession?.tableDisplay,t.table_session?.table?.name,t.tableSession?.table?.name,t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:lt(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:lt(t.batch_codes,t.batchCodes),items:_,subtotal:P,discount:A,tax:x,grandTotal:q,paidAmount:L(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,q),paymentMethod:Xt(t),invoiceUrl:v,upiQr:b,invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,u.qr_svg_or_url,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},ne=(t,e={})=>{const o=e.paperSize||"80mm",n=yt(o),s=o==="58mm",i=e.agentPdf===!0,a=e.customPrintInvoice===!0,r=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],d=Array.isArray(t.kotCodes)?t.kotCodes:[],u=Array.isArray(t.batchCodes)?t.batchCodes:[],v=ot(),_=!(t.simpleBilling??v.simpleBilling),P=a?_t(t.shopName):t.shopName,A=a?ft(t.shopAddress):t.shopAddress,x=p(t.invoiceNo),q=a?s?"48px":"64px":n.paychatLogoWidth,y=!r&&t.upiQr?pt(t.upiQr,"UPI payment QR"):"",N=!r&&!y?pt(t.invoiceQr||t.reviewQr):"",F=!t.upiQr&&t.invoiceUrl&&(r||!N)?`<div class="qr-url">${l(t.invoiceUrl)}</div>`:"";return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Thermal Bill</title>
  <style>
    @page { size: ${n.width} auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0 0 ${a?"18px":"0"};
      background: #fff;
      color: #000;
      font-family: "Courier New", monospace;
      font-size: ${n.fontSize};
      line-height: ${a?"1.08":"1.28"};
    }
    .receipt {
      width: ${n.width};
      padding: ${a?"2px 4px 14px":n.padding};
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { font-size: 0.88em; }
    .powered { font-size: ${a?"0.72em":"0.88em"}; }
    .title {
      color: #000;
      font-size: ${a?s?"15px":"18px":n.titleSize};
      font-weight: ${a?"900":"800"};
      text-transform: ${a?"none":"uppercase"};
      ${a?"text-shadow: 0 0 0 #000, 0.25px 0 #000, -0.25px 0 #000; -webkit-text-stroke: 0.25px #000;":""}
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
      max-width: ${q};
      max-height: ${s?"20px":"26px"};
      object-fit: contain;
      margin: 2px auto 1px;
    }
    .bill-no {
      font-size: 1.15em;
      font-weight: ${a?"900":"700"};
      text-align: center;
      margin: ${a?"1px 0":"3px 0"};
      word-break: break-word;
    }
    .bill-no-row td {
      color: #000;
      font-weight: 900;
      padding-top: 0;
    }
    .line {
      border-top: 1px dashed #000;
      margin: ${a?"2px 0":"6px 0"};
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    td, th {
      padding: ${a?"1px 0":"2px 0"};
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
      padding: ${a?"1px 0":"3px 0"};
      border-bottom: 1px dotted #999;
    }
    .item-meta,
    .total-row {
      display: flex;
      justify-content: space-between;
      gap: 6px;
    }
    .grand {
      border-top: ${a?"2px solid #000":"1px dashed #000"};
      color: #000;
      padding-top: ${a?"3px":"5px"};
      margin-top: ${a?"2px":"4px"};
      font-weight: 900;
      font-size: ${a?"1.22em":"1.12em"};
      ${a?"text-shadow: 0.25px 0 #000, -0.25px 0 #000;":""}
    }
    .top-token {
      border-bottom: 1px dashed #000;
      font-size: ${s?"1.55em":"1.75em"};
      font-weight: 900;
      margin-bottom: ${a?"3px":"6px"};
      padding-bottom: ${a?"3px":"6px"};
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
    ${a&&t.tokenNo?`<div class="top-token">TOKEN ${l(t.tokenNo)}</div>`:""}
    <div class="center">
      ${!a&&t.shopLogoUrl?`<img class="shop-logo" src="${E(t.shopLogoUrl)}" alt="${E(P)}" />`:""}
      <div class="title">${l(P)}</div>
      ${!a&&t.locationName?`<div class="muted">${l(t.locationName)}</div>`:""}
      ${A?`<div class="muted">${l(A)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${l(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${x&&!a?`<div class="bill-no">INVOICE NO: ${l(x)}</div>`:""}
    <table>
	      ${x&&a?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${l(x)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${l(bt(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${l(t.orderType)}</td></tr>`:""}
	      ${(_||a)&&t.tableName?`<tr><td>Table</td><td class="right">${l(t.tableName)}</td></tr>`:""}
	      ${_&&t.guestCount&&!a?`<tr><td>Guests</td><td class="right">${l(t.guestCount)}</td></tr>`:""}
	      ${_&&t.tokenNo&&!a?`<tr><td>Token</td><td class="right">${l(t.tokenNo)}</td></tr>`:""}
	      ${_&&d.length?`<tr><td>KOT</td><td class="right">${l(d.join(", "))}</td></tr>`:""}
	      ${_&&u.length?`<tr><td>Batch</td><td class="right">${l(u.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${s?`
      <div>
        ${c.length?c.map($=>`
          <div class="item-block">
            <div class="item-name">${l($.name)}</div>
            <div class="item-meta">
              <span>${l(W($.qty))} x ${l(m($.rate))}</span>
              <strong>${l(m($.total))}</strong>
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
          ${c.length?c.map($=>`
            <tr>
              <td class="item-name">${l($.name)}</td>
              <td class="right">${l(W($.qty))}</td>
              <td class="right">${l(m($.rate))}</td>
              <td class="right">${l(m($.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${a?`
      <div class="total-row grand"><span>TOTAL</span><span>${l(m(t.grandTotal))}</span></div>
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${l(t.paymentMethod)}</span></div>`:""}
    `:i?`
      <table class="pdf-totals">
        <tbody>
          <tr><td>Subtotal</td><td class="pdf-total-value">${l(m(t.subtotal))}</td></tr>
          ${t.discount?`<tr><td>Discount</td><td class="pdf-total-value">-${l(m(t.discount))}</td></tr>`:""}
          ${t.tax?`<tr><td>Tax/GST</td><td class="pdf-total-value">${l(m(t.tax))}</td></tr>`:""}
          <tr class="grand"><td>TOTAL</td><td class="pdf-total-value">${l(m(t.grandTotal))}</td></tr>
          ${t.paidAmount?`<tr><td>Paid</td><td class="pdf-total-value">${l(m(t.paidAmount))}</td></tr>`:""}
          ${t.paymentMethod?`<tr><td>Payment</td><td class="pdf-total-value">${l(t.paymentMethod)}</td></tr>`:""}
        </tbody>
      </table>
    `:`
      <div class="total-row"><span>Subtotal</span><span>${l(m(t.subtotal))}</span></div>
      ${t.discount?`<div class="total-row"><span>Discount</span><span>-${l(m(t.discount))}</span></div>`:""}
      ${t.tax?`<div class="total-row"><span>Tax/GST</span><span>${l(m(t.tax))}</span></div>`:""}
      <div class="total-row grand"><span>TOTAL</span><span>${l(m(t.grandTotal))}</span></div>
      ${t.paidAmount?`<div class="total-row"><span>Paid</span><span>${l(m(t.paidAmount))}</span></div>`:""}
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${l(t.paymentMethod)}</span></div>`:""}
    `}
    ${N||F?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!r&&N?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${N||F}
      </div>
    `:""}
    ${y?`
      <div class="line"></div>
      <div class="qr-wrap">
        <div class="muted">Scan QR to pay via UPI</div>
        ${y}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!a?`<img class="paychat-logo" src="${E(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},I=(t,e="-")=>`${e.repeat(t)}
`,vt=(t="")=>`${Mt}${Qt}${t}${Rt}${Ut}`,oe=(t="")=>vt(t),S=(t,e)=>{const o=z(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},h=(t,e,o)=>{const n=z(e),s=Math.max(1,o-n.length-1),i=z(t).slice(0,s),a=Math.max(1,o-i.length-n.length);return`${i}${" ".repeat(a)}${n}
`},H=(t,e)=>{const o=z(t).split(/\s+/).filter(Boolean).flatMap(i=>i.length<=e?[i]:i.match(new RegExp(`.{1,${e}}`,"g"))||[i]),n=[];let s="";return o.forEach(i=>{if(!s){s=i;return}(s+" "+i).length<=e?s+=` ${i}`:(n.push(s),s=i.slice(0,e))}),s&&n.push(s),n.length?n:[""]},se=(t,e)=>{const o=H(t.name,e),n=`${W(t.qty)} x ${m(t.rate)}`;return[...o.map(s=>`${s}
`),h(n,m(t.total),e)].join("")},ie=(t,e)=>{const i=e-5-9-10,a=H(t.name,i),r=`${a[0].padEnd(i)}${W(t.qty).padStart(5)}${m(t.rate).padStart(9)}${m(t.total).padStart(10)}
`,c=a.slice(1).map(d=>`${d}
`).join("");return r+c},xt=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=yt(o),s=o==="58mm",i=e.customPrintInvoice===!0,a=e.hideInvoiceQr===!0,r=e.escposCommands===!0,c=Array.isArray(t.items)?t.items:[],d=Array.isArray(t.kotCodes)?t.kotCodes:[],u=Array.isArray(t.batchCodes)?t.batchCodes:[],v=ot(),b=t.simpleBilling??v.simpleBilling,_=s?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,P=i?_t(t.shopName):t.shopName,A=i?ft(t.shopAddress):t.shopAddress,x=p(t.invoiceNo),q=S(P,n),y=A?H(A,n).map(O=>S(O,n)).join(""):"",N=x?h("Invoice No",x,n):"",F=i&&t.tokenNo?`${I(n)}${S(`TOKEN ${t.tokenNo}`,n)}${I(n)}`:"",$=!t.upiQr&&t.invoiceUrl?`${I(n)}${S(a?"Invoice link":"Invoice/review link",n)}${H(t.invoiceUrl,n).map(O=>`${z(O)}
`).join("")}`:"",Dt=t.upiQr&&!a?`${I(n)}${S("Scan QR to pay via UPI",n)}`:"";return[F,r?oe(q):q,!i&&t.locationName?S(t.locationName,n):"",y,t.shopPhone?S(`Phone: ${t.shopPhone}`,n):"",I(n),r?vt(N):N,h("Date",bt(t.dateTime),n),t.orderType?h("Type",t.orderType,n):"",(!b||i)&&t.tableName?h("Table",t.tableName,n):"",!b&&t.guestCount&&!i?h("Guests",t.guestCount,n):"",!b&&t.tokenNo&&!i?h("Token",t.tokenNo,n):"",!b&&d.length?h("KOT",d.join(","),n):"",!b&&u.length?h("Batch",u.join(","),n):"",I(n),_,_?I(n):"",c.length?c.map(O=>s?se(O,n):ie(O,n)).join(""):S("No items",n),I(n),i?"":h("Subtotal",m(t.subtotal),n),!i&&t.discount?h("Discount",`-${m(t.discount)}`,n):"",!i&&t.tax?h("Tax/GST",m(t.tax),n):"",i?"":I(n),h("TOTAL",m(t.grandTotal),n),t.paidAmount&&!i?h("Paid",m(t.paidAmount),n):"",t.paymentMethod?h("Payment",t.paymentMethod,n):"",Dt,$,I(n),S("Thank you",n),S("Powered by PayChat",n),...Array(i?jt:Bt).fill(`
`)].join("")},Je=xt,Nt="\x1BE",It="\x1BE\0",ae="\x1Ba\0",re="\x1Ba",ce="!",le="!\0",pe=1,me=3,g=(t="")=>String(t??"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,"").replace(/\s+/g," ").trim(),f=(...t)=>{for(const e of t){const o=g(e);if(o)return o}return""},de=(t="58mm")=>t==="80mm"?48:32,M=(t,e="-")=>e.repeat(t),R=(t,e=!0)=>e?`${Nt}${t}${It}`:t,ue=(t,e=!0)=>e?`${ce}${Nt}${t}${It}${le}`:t,he=(t,e)=>{const o=g(t),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}`},X=(t,e,o=!0)=>o?`${re}${t}${ae}`:he(t,e),U=(t,e,o)=>{const n=g(t),s=g(e),i=Math.max(1,o-n.length-s.length);return`${n}${" ".repeat(i)}${s}`},at=(t,e,o="")=>{const n=g(t);if(!n)return[];const s=Math.max(8,e-o.length),i=[],a=n.split(" ");let r="";return a.forEach(c=>{if(!r){r=c;return}if(`${r} ${c}`.length<=s){r=`${r} ${c}`;return}i.push(r),r=c}),r&&i.push(r),i.flatMap(c=>{if(c.length<=s)return[`${o}${c}`];const d=[];for(let u=0;u<c.length;u+=s)d.push(`${o}${c.slice(u,u+s)}`);return d})},G=(t={})=>t&&typeof t=="object"?f(t.table_display,t.tableDisplay,t.name,t.code,t.table_name,t.tableName):"",ge=(t={})=>{const e=[t,t.order,t.table_session,t.tableSession,t.order?.table_session,t.order?.tableSession,t.table,t.order?.table].filter(Boolean);for(const o of e){const n=f(o.table_display,o.tableDisplay,o.table_group_label,o.tableGroupLabel);if(n)return n}for(const o of e){const s=(Array.isArray(o.tables)?o.tables:[]).map(G).filter(Boolean);if(s.length)return s.join(" + ")}for(const o of e){const n=Array.isArray(o.linked_tables)?o.linked_tables:Array.isArray(o.linkedTables)?o.linkedTables:[],s=[G(o.primary_table||o.primaryTable),G(o.table),...n.map(G)].filter(Boolean);if(s.length)return[...new Set(s)].join(" + ")}for(const o of e){const n=f(o.table_name,o.tableName,o.name,o.code);if(n)return n}return""},St=(t={})=>f(t.product_name,t.name,t.product?.name,t.item_name,"Item"),Tt=(t={})=>{const e=Number(t.quantity??t.qty??1);return Number.isFinite(e)&&e>0?e:1},Et=t=>Number.isInteger(t)?String(t):String(t).replace(/\.0+$/,""),_e=(t={},e)=>[f(t.variant,t.variant_name),...Array.isArray(t.modifiers)?t.modifiers.map(n=>f(n.name,n.label,n)):[],f(t.notes,t.note,t.kitchen_note,t.instructions)].filter(Boolean).flatMap(n=>at(n,e,"  - ")),wt=(t={})=>{const e=t.print_data||t.printData||t.batch||t,o=f(e.batch_code,e.batchCode,e.code,`KOT-${e.id||e.batch_id||""}`);return{outlet:f(e.outlet,e.store_name,e.location?.name,e.location_name),code:o,tokenNo:f(e.token_no,e.tokenNo,e.token_number,e.tokenNumber,e.token?.token_code,e.token?.token_no,e.order?.token?.token_code,e.order?.token_no,o),orderNo:f(e.order?.order_no,e.order_no,e.orderNo,e.order?.id,e.order_id),table:ge(e),status:f(e.status,"waiting"),time:f(e.sent_at,e.created_at,new Date().toISOString()),orderNotes:f(e.order?.notes,e.notes,e.table_notes),items:Array.isArray(e.items)?e.items:[]}},fe=(t={},e,o=!0)=>{const s=`${Et(Tt(t))} x`,i=" ".repeat(Math.min(7,s.length+2)),a=at(St(t),e-i.length);return a.length?[`${R(s.padEnd(i.length-1),o)} ${a[0].trim()}`,...a.slice(1).map(r=>`${i}${r.trim()}`)]:[R(s,o)]},be=(t={},e={})=>{const o=e.paperSize||"58mm",n=de(o),s=e.escposCommands===!0,i=wt(t),a=[];return i.outlet&&a.push(X(R(i.outlet.toUpperCase(),s),n,s)),a.push(X(R("KITCHEN ORDER TOKEN",s),n,s)),a.push(M(n)),a.push(X(ue(`TOKEN ${i.tokenNo||i.code}`,s),n,s)),a.push(M(n)),a.push(U("KOT",i.code,n)),i.orderNo&&a.push(U("Order",i.orderNo,n)),i.table&&a.push(U("Table",i.table,n)),a.push(U("Status",i.status,n)),a.push(U("Time",i.time.replace("T"," ").slice(0,16),n)),a.push(M(n)),i.items.forEach(r=>{a.push(...fe(r,n,s)),a.push(..._e(r,n)),a.push(...Array(pe).fill(""))}),i.orderNotes&&(a.push(M(n)),a.push(R("Notes",s)),a.push(...at(i.orderNotes,n))),a.push(M(n)),a.push(...Array(me).fill("")),a.join(`
`)},ye=(t={})=>{const e=wt(t),o=e.items.map(n=>`
    <div class="item">
      <div class="qty">${g(Et(Tt(n)))} x</div>
      <div class="name">${g(St(n))}</div>
    </div>
  `).join("");return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: monospace; width: 280px; margin: 0; padding: 0 0 18px; color: #111; }
    h1, h2, p { margin: 0; }
    h1 { font-size: 18px; text-align: center; }
    h2 { font-size: 14px; text-align: center; margin-bottom: 8px; }
    .token { margin: 8px 0; text-align: center; font-size: 26px; font-weight: 900; line-height: 1.05; }
    .line { border-top: 1px dashed #111; margin: 8px 0; }
    .meta { display: flex; justify-content: space-between; gap: 8px; font-size: 12px; }
    .meta span { text-align: right; overflow-wrap: anywhere; }
    .items { font-size: 15px; }
    .item { display: flex; align-items: flex-start; gap: 8px; padding: 6px 0 8px; margin-bottom: 2px; border-bottom: 1px dotted #ddd; }
    .qty { flex: 0 0 42px; font-size: 16px; font-weight: 900; }
    .name { flex: 1; font-weight: 800; line-height: 1.25; overflow-wrap: anywhere; word-break: break-word; }
    .notes { font-size: 12px; margin-top: 8px; }
  </style>
</head>
<body>
  ${e.outlet?`<h1>${g(e.outlet).toUpperCase()}</h1>`:""}
  <h2>KITCHEN ORDER TOKEN</h2>
  <div class="line"></div>
  <div class="token">TOKEN ${g(e.tokenNo||e.code)}</div>
  <div class="line"></div>
  <p class="meta"><strong>KOT</strong><span>${g(e.code)}</span></p>
  ${e.orderNo?`<p class="meta"><strong>Order</strong><span>${g(e.orderNo)}</span></p>`:""}
  ${e.table?`<p class="meta"><strong>Table</strong><span>${g(e.table)}</span></p>`:""}
  <p class="meta"><strong>Status</strong><span>${g(e.status)}</span></p>
  <p class="meta"><strong>Time</strong><span>${g(e.time.replace("T"," ").slice(0,16))}</span></p>
  <div class="line"></div>
  <div class="items">${o}</div>
  ${e.orderNotes?`<div class="line"></div><p class="notes"><strong>Notes:</strong> ${g(e.orderNotes)}</p>`:""}
  <div class="line"></div>
</body>
</html>`},$e=(t={},e={})=>({text:be(t,e),html:ye(t),print_mode:e.printMode||"escpos"}),At="paychat_print_agent_settings",et={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},ve=8e3,mt=12e3,xe=1,Ne=3,Ie=["invoice_url","invoiceUrl","review_url","reviewUrl"],Se=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},rt=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),Te=t=>t==="80mm"?"80mm":"58mm",Ee=t=>t==="pdf"?"pdf":"escpos",w=(t={})=>({...et,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||et.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:Te(t?.paperSize),printMode:Ee(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),k=()=>typeof localStorage>"u"?{...et}:w(Se(localStorage.getItem(At),{})),we=(t={})=>{const e=w({...k(),...t});try{localStorage.setItem(At,JSON.stringify(e))}catch{}return e},Z=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},j=(t,e={},o={})=>{const n=w(e),s=new URL(t,`${n.agentUrl}/`),i={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(i).forEach(([a,r])=>{r!=null&&r!==""&&s.searchParams.set(a,String(r))}),s.toString()},K=async(t,e={},o=ve)=>{const n=new AbortController,s=setTimeout(()=>n.abort(),o);try{const i=await fetch(t,{...e,signal:n.signal}),r=(i.headers.get("content-type")||"").includes("application/json")?await i.json().catch(()=>null):await i.text().catch(()=>"");if(!i.ok)throw Z(r?.message||r?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return r}catch(i){throw i?.name==="AbortError"?Z("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",i):i?.code?i:Z("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",i)}finally{clearTimeout(s)}},Ae=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},ke=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),nt=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(ke)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const s of n){const i=nt(t[s],e+1,o);if(i.length)return i}for(const s of Object.values(t)){const i=nt(s,e+1,o);if(i.length)return i}return[]},Q=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},T=(...t)=>{for(const e of t){const o=rt(e).trim();if(o)return o}return""},Y=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Ae(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,o.items,o.order_items,o.line_items,o.cart_items,o.invoice_items,o.details,nt(t))},J=(t={})=>T(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),B=(t={})=>Q(t.quantity,t.qty,t.pivot?.quantity,1)||1,V=(t={})=>{const e=B(t),o=T(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=T(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},kt=(t={})=>{const e=T(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):V(t)*B(t)},Pe=(t=[])=>t.map(e=>({...e,product_name:J(e),name:J(e),quantity:B(e),qty:B(e),rate:V(e),price:V(e),total:kt(e)})),qe=(t,e)=>{const o=rt(t);if(o.length<=e)return[o];const n=[];for(let s=0;s<o.length;s+=e)n.push(o.slice(s,s+e));return n},Ce=(t,e)=>{const o=e==="80mm"?48:32;return rt(t).split(/\r?\n/).flatMap(n=>qe(n,o)).join(`
`)},Oe=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=Y(t);return n.length?n.map(s=>{const i=J(s),a=B(s),r=V(s),d=kt(s).toFixed(2),u=`${a} x ${r.toFixed(2)}`,v=Math.max(1,o-u.length-d.length);return`${i}
${u}${" ".repeat(v)}${d}`}).join(`
`):""},Le=(t,e,o)=>{const n=Y(e);return!n.length||n.some(i=>{const a=J(i);return a&&t.includes(a.slice(0,Math.min(a.length,12)))})?t:`${t}
${Oe(e,o)}`},De=(t,e)=>{if(/total/i.test(t))return t;const o=Q(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,Y(e).reduce((n,s)=>{const i=Q(s.quantity,s.qty,1)||1,a=Q(s.rate,s.price,s.unit_price);return n+Q(s.total,s.line_total,s.amount,i*a)},0));return`${t}
TOTAL ${o.toFixed(2)}`},dt=t=>`\x1BE${t}\x1BE\0`,ze=(t="",e={})=>{const o=T(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(n=>{const s=n.trim();return s&&(o&&s.toLowerCase()===o.toLowerCase()||/^invoice no\b/i.test(s)||/^total\b/i.test(s))?dt(n):n}).join(`
`)},Me=(t={},e={})=>{for(const o of Ie){const n=T(t[o],e[o]);if(n)return n}return T(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},Ue=(t={},e={})=>{const n=(Array.isArray(t.payments)?t.payments:[]).find(s=>String(s?.payment_method||s?.method||"").toLowerCase()==="upi"&&T(s.upi_qr_url,s.upiQrUrl,s.meta?.upi_qr_url,s.qr_payload,s.qr));return T(e.upiQr,e.paymentQr,t.upi_qr_url,t.upiQrUrl,t.payment?.upi_qr_url,t.payment?.upiQrUrl,t.payment?.meta?.upi_qr_url,t.qr?.upi_qr_url,t.receipt?.qr?.upi_qr_url,n?.upi_qr_url,n?.upiQrUrl,n?.meta?.upi_qr_url,n?.qr_payload,n?.qr)},Qe=t=>{try{const e=new URL(t);return["http:","https:","upi:"].includes(e.protocol)}catch{return T(t)!==""}},Pt=(t={},e={},o=k())=>{const n=w(o),s=n.paperSize,i={...t||{},items:Pe(Y(t||{}))},a=ee(i,e||{}),r={paperSize:s,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr,escposCommands:n.printMode==="escpos"&&s!=="80mm"};let c=xt(a,r);const d=ne(a,{...r,agentPdf:n.printMode==="pdf"});typeof c!="string"&&(c=String(c??"")),c=Le(c,i,s),c=De(c,i),c=Ce(c,s),n.customPrintInvoice&&s!=="80mm"&&(c=ze(c,a)),c.length>mt&&(c=`${c.slice(0,mt)}
--- Receipt truncated ---`),c=c.replace(/\n*$/,`
`.repeat(n.customPrintInvoice?Ne:xe));const u=Ue(t,a),v=Me(t,a),b=u||v,_={text:c,html:d,print_mode:n.printMode};return!n.hideInvoiceQr&&b&&Qe(b)&&(_.qr={data:b,size:6,error_correction:"M"}),_},Re=async(t=k())=>{const e=w(t);return K(j("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Be=async(t=k())=>{const e=w(t),o=await K(j("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},je=async(t=k())=>{const e=w(t);return K(j("/test-print",e),{method:"POST"})},Ke=async(t={},e={})=>{const o=w(e.settings||k()),n=Pt(t,e.context||{},o);return K(j("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},Fe=async(t={},e={})=>{const o=w(e.settings||k()),n=$e(t,{paperSize:o.paperSize,printMode:o.printMode,escposCommands:o.printMode==="escpos"});return K(j("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},Ve={getSettings:k,saveSettings:we,checkHealth:Re,getPrinters:Be,testPrint:je,printReceipt:Ke,printKot:Fe,buildSafeAgentReceiptPayload:Pt},qt="paychat_kitchen_operation_mode",Ct="paychat_generate_inline_kitchen_token",Ot="paychat_inline_kitchen_without_status_management",D={DEDICATED_KDS:"dedicated_kds",INLINE:"inline"},Lt=Object.values(D),C=()=>typeof window>"u"?null:window.localStorage||null,ut=t=>{try{const e=C()?.getItem(t);return e?JSON.parse(e):null}catch{return null}},Ge=()=>{const t=ut("tenant_settings")||{},e=ut("tenant_info")||{},o=t?.kitchen?.operation_mode||t?.raw?.kitchen_operation_mode||e?.settings?.kitchen?.operation_mode||e?.settings?.raw?.kitchen_operation_mode||e?.tenant?.settings?.kitchen?.operation_mode||e?.tenant?.settings?.raw?.kitchen_operation_mode;return Lt.includes(o)?o:null},We=()=>{const e=C()?.getItem(qt);return Lt.includes(e)?e:Ge()||D.DEDICATED_KDS},Ye=t=>{const e=t===D.INLINE?D.INLINE:D.DEDICATED_KDS;return C()?.setItem(qt,e),e},Xe=()=>We()===D.INLINE,Ze=()=>C()?.getItem(Ct)==="true",tn=t=>{const e=!!t;return C()?.setItem(Ct,e?"true":"false"),e},en=()=>C()?.getItem(Ot)==="true",nn=t=>{const e=!!t;return C()?.setItem(Ot,e?"true":"false"),e};export{D as K,ne as a,Je as b,en as c,Ze as d,nn as e,tn as f,We as g,Xe as i,ee as n,Ve as p,Ye as s};
