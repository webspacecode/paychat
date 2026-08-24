import{g as nt}from"./index-BHtnIrz4.js";const Dt="/color-paychat-logo-main.svg",qt="\x1BE",zt="\x1BE\0",Mt="\x1BG",Rt="\x1BG\0",Ut=1,Bt=3,rt={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},mt=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},ot=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},jt=()=>mt(ot("tenant_info"),{}),Kt=()=>mt(ot("selected_location"),{}),ut=t=>q(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),ht=t=>ut(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),gt=t=>{const e=ut(t);if(!e)return"";const n=e.split(",").map(o=>o.trim()).filter(Boolean);return(n.length?n.slice(0,2).join(", "):e).slice(0,80)},l=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),L=t=>l(t).replace(/`/g,"&#096;"),q=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),d=t=>Number(t||0).toFixed(2),G=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},_t=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},ft=(t="80mm")=>rt[t]||rt["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},Qt=t=>{const e=String(t||"").trim();if(!e)return"";try{const n=typeof window<"u"?window.location.origin:"https://paychat.local",s=new URL(e,n).pathname.split("/").map(r=>r.trim()).filter(Boolean),i=s.findIndex(r=>["invoice","invoices"].includes(r.toLowerCase())),a=i>=0?s[i+1]:s[s.length-1];return decodeURIComponent(a||"").trim()}catch{const s=e.split("?")[0].split("#")[0].split("/").map(i=>i.trim()).filter(Boolean);return s[s.length-1]||""}},C=(...t)=>{for(const e of t){if(e==null||e==="")continue;const n=Number(e);if(Number.isFinite(n))return n}return 0},Ft=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Gt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),Z=(t,e=0,n=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Gt)?t:[];if(typeof t!="object"||n.has(t))return[];n.add(t);const o=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const s of o){const i=Z(t[s],e+1,n);if(i.length)return i}for(const s of Object.values(t)){const i=Z(s,e+1,n);if(i.length)return i}return[]},Wt=(t={})=>p(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),st=(t={})=>C(t.quantity,t.qty,t.pivot?.quantity,1)||1,bt=(t={})=>{const e=st(t),n=p(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(n!=="")return Number(n||0);const o=p(t.total,t.line_total,t.amount,t.subtotal);return Number(o||0)/e},Ht=(t={})=>{const e=p(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):bt(t)*st(t)},Jt=(t={})=>{const e=t.invoice||t.invoice_data||{},n=t.data||t.order||{};return Ft(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,n.items,n.order_items,n.line_items,n.cart_items,n.invoice_items,n.details,Z(t))},Vt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},ct=(...t)=>{const e=[];return t.flat().forEach(n=>{if(!n)return;if(typeof n=="string"||typeof n=="number"){e.push(String(n));return}const o=p(n.code,n.kot_code,n.batch_code,n.token_code,n.id);o&&e.push(String(o))}),[...new Set(e)]},Yt=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const n=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(n)&&(e=n)}catch{}return e.trim()},Xt=t=>{if(!t)return"";const e=Yt(t),n=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(n){const s=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(n[0])}`;return`<img class="qr-image" src="${L(s)}" alt="Invoice QR" />`}const o=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return o?.[1]?`<img class="qr-image" src="${L(o[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${L(e)}" alt="Invoice QR" />`:`<div class="qr-url">${l(e)}</div>`},Zt=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const n=jt(),o=nt(),s=n?.tenant||{},i=n?.branding||s?.branding||n?.branching||{},a=Kt(),r={...a&&typeof a=="object"?a:{},...t.location&&typeof t.location=="object"?t.location:{}};t.branch||t.branching||t.branding||n?.branch||n?.branching;const c=t.merchant||t.receipt?.merchant||{},m=t.invoice||t.invoice_data||t.receipt?.invoice||{},u=t.qr||t.receipt?.qr||{},f=p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,m.url,t.meta?.invoice?.url,u.invoice_url),v=Jt(t).map(y=>({name:Wt(y),qty:st(y),rate:bt(y),total:Ht(y)})),b=C(t.subtotal,t.totals?.subtotal,v.reduce((y,N)=>y+N.rate*N.qty,0)),A=C(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),E=C(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),x=C(t.total,t.grand_total,t.totals?.grand_total,b+E-A);return{shopName:p(e.shopName,c.name,r.tenant?.name,t.tenant?.name,i.company_name,s.name,ot("tenant_slug"),"PayChat POS"),shopPhone:p(e.shopPhone,c.phone,r.phone,i.phone,s.phone),shopAddress:p(e.shopAddress,i.address,s.branding?.address,t.tenant?.branding?.address,r.tenant?.branding?.address),shopLogoUrl:p(e.shopLogoUrl,r.logo,r.tenant?.logo,t.tenant?.logo,i.logo,s.logo),locationName:p(r.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,Dt),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,m.number,m.invoice_no,m.invoiceNo,m.invoice_number,m.offline_invoice_number,t.meta?.invoice?.number,t.meta?.invoice?.invoice_no,t.meta?.invoice?.invoiceNo,t.meta?.invoice?.invoice_number,t.offline_invoice_number,t.local_invoice_no,Qt(f)),orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table_display,t.tableDisplay,t.table_session?.table_display,t.tableSession?.tableDisplay,t.table_session?.table?.name,t.tableSession?.table?.name,t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:ct(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:ct(t.batch_codes,t.batchCodes),items:v,subtotal:b,discount:A,tax:E,grandTotal:x,paidAmount:C(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,x),paymentMethod:Vt(t),invoiceUrl:f,invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,u.qr_svg_or_url,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:o.simpleBilling,billingLabel:o.billingLabel}},te=(t,e={})=>{const n=e.paperSize||"80mm",o=ft(n),s=n==="58mm",i=e.agentPdf===!0,a=e.customPrintInvoice===!0,r=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],u=Array.isArray(t.batchCodes)?t.batchCodes:[],f=nt(),b=!(t.simpleBilling??f.simpleBilling),A=a?ht(t.shopName):t.shopName,E=a?gt(t.shopAddress):t.shopAddress,x=p(t.invoiceNo),y=a?s?"48px":"64px":o.paychatLogoWidth,N=r?"":Xt(t.invoiceQr||t.reviewQr),z=t.invoiceUrl&&(r||!N)?`<div class="qr-url">${l(t.invoiceUrl)}</div>`:"";return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Thermal Bill</title>
  <style>
    @page { size: ${o.width} auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0 0 ${a?"18px":"0"};
      background: #fff;
      color: #000;
      font-family: "Courier New", monospace;
      font-size: ${o.fontSize};
      line-height: ${a?"1.08":"1.28"};
    }
    .receipt {
      width: ${o.width};
      padding: ${a?"2px 4px 14px":o.padding};
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { font-size: 0.88em; }
    .powered { font-size: ${a?"0.72em":"0.88em"}; }
    .title {
      color: #000;
      font-size: ${a?s?"15px":"18px":o.titleSize};
      font-weight: ${a?"900":"800"};
      text-transform: ${a?"none":"uppercase"};
      ${a?"text-shadow: 0 0 0 #000, 0.25px 0 #000, -0.25px 0 #000; -webkit-text-stroke: 0.25px #000;":""}
      word-break: break-word;
    }
    .shop-logo {
      display: block;
      max-width: ${o.logoMaxWidth};
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
      width: ${o.qrSize};
      height: ${o.qrSize};
      max-width: ${o.qrSize};
      max-height: ${o.qrSize};
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
      ${!a&&t.shopLogoUrl?`<img class="shop-logo" src="${L(t.shopLogoUrl)}" alt="${L(A)}" />`:""}
      <div class="title">${l(A)}</div>
      ${!a&&t.locationName?`<div class="muted">${l(t.locationName)}</div>`:""}
      ${E?`<div class="muted">${l(E)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${l(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${x&&!a?`<div class="bill-no">INVOICE NO: ${l(x)}</div>`:""}
    <table>
	      ${x&&a?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${l(x)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${l(_t(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${l(t.orderType)}</td></tr>`:""}
	      ${(b||a)&&t.tableName?`<tr><td>Table</td><td class="right">${l(t.tableName)}</td></tr>`:""}
	      ${b&&t.guestCount&&!a?`<tr><td>Guests</td><td class="right">${l(t.guestCount)}</td></tr>`:""}
	      ${b&&t.tokenNo&&!a?`<tr><td>Token</td><td class="right">${l(t.tokenNo)}</td></tr>`:""}
	      ${b&&m.length?`<tr><td>KOT</td><td class="right">${l(m.join(", "))}</td></tr>`:""}
	      ${b&&u.length?`<tr><td>Batch</td><td class="right">${l(u.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${s?`
      <div>
        ${c.length?c.map($=>`
          <div class="item-block">
            <div class="item-name">${l($.name)}</div>
            <div class="item-meta">
              <span>${l(G($.qty))} x ${l(d($.rate))}</span>
              <strong>${l(d($.total))}</strong>
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
              <td class="right">${l(G($.qty))}</td>
              <td class="right">${l(d($.rate))}</td>
              <td class="right">${l(d($.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${a?`
      <div class="total-row grand"><span>TOTAL</span><span>${l(d(t.grandTotal))}</span></div>
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${l(t.paymentMethod)}</span></div>`:""}
    `:i?`
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
    ${N||z?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!r&&N?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${N||z}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!a?`<img class="paychat-logo" src="${L(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},I=(t,e="-")=>`${e.repeat(t)}
`,yt=(t="")=>`${qt}${Mt}${t}${Rt}${zt}`,ee=(t="")=>yt(t),S=(t,e)=>{const n=q(t).slice(0,e),o=Math.max(0,Math.floor((e-n.length)/2));return`${" ".repeat(o)}${n}
`},h=(t,e,n)=>{const o=q(e),s=Math.max(1,n-o.length-1),i=q(t).slice(0,s),a=Math.max(1,n-i.length-o.length);return`${i}${" ".repeat(a)}${o}
`},W=(t,e)=>{const n=q(t).split(/\s+/).filter(Boolean).flatMap(i=>i.length<=e?[i]:i.match(new RegExp(`.{1,${e}}`,"g"))||[i]),o=[];let s="";return n.forEach(i=>{if(!s){s=i;return}(s+" "+i).length<=e?s+=` ${i}`:(o.push(s),s=i.slice(0,e))}),s&&o.push(s),o.length?o:[""]},ne=(t,e)=>{const n=W(t.name,e),o=`${G(t.qty)} x ${d(t.rate)}`;return[...n.map(s=>`${s}
`),h(o,d(t.total),e)].join("")},oe=(t,e)=>{const i=e-5-9-10,a=W(t.name,i),r=`${a[0].padEnd(i)}${G(t.qty).padStart(5)}${d(t.rate).padStart(9)}${d(t.total).padStart(10)}
`,c=a.slice(1).map(m=>`${m}
`).join("");return r+c},$t=(t,e={})=>{const n=e.paperSize||"80mm",{columns:o}=ft(n),s=n==="58mm",i=e.customPrintInvoice===!0,a=e.hideInvoiceQr===!0,r=e.escposCommands===!0,c=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],u=Array.isArray(t.batchCodes)?t.batchCodes:[],f=nt(),v=t.simpleBilling??f.simpleBilling,b=s?"":`${"Item".padEnd(o-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,A=i?ht(t.shopName):t.shopName,E=i?gt(t.shopAddress):t.shopAddress,x=p(t.invoiceNo),y=S(A,o),N=E?W(E,o).map(O=>S(O,o)).join(""):"",z=x?h("Invoice No",x,o):"",$=i&&t.tokenNo?`${I(o)}${S(`TOKEN ${t.tokenNo}`,o)}${I(o)}`:"",Lt=t.invoiceUrl?`${I(o)}${S(a?"Invoice link":"Invoice/review link",o)}${W(t.invoiceUrl,o).map(O=>`${q(O)}
`).join("")}`:"";return[$,r?ee(y):y,!i&&t.locationName?S(t.locationName,o):"",N,t.shopPhone?S(`Phone: ${t.shopPhone}`,o):"",I(o),r?yt(z):z,h("Date",_t(t.dateTime),o),t.orderType?h("Type",t.orderType,o):"",(!v||i)&&t.tableName?h("Table",t.tableName,o):"",!v&&t.guestCount&&!i?h("Guests",t.guestCount,o):"",!v&&t.tokenNo&&!i?h("Token",t.tokenNo,o):"",!v&&m.length?h("KOT",m.join(","),o):"",!v&&u.length?h("Batch",u.join(","),o):"",I(o),b,b?I(o):"",c.length?c.map(O=>s?ne(O,o):oe(O,o)).join(""):S("No items",o),I(o),i?"":h("Subtotal",d(t.subtotal),o),!i&&t.discount?h("Discount",`-${d(t.discount)}`,o):"",!i&&t.tax?h("Tax/GST",d(t.tax),o):"",i?"":I(o),h("TOTAL",d(t.grandTotal),o),t.paidAmount&&!i?h("Paid",d(t.paidAmount),o):"",t.paymentMethod?h("Payment",t.paymentMethod,o):"",Lt,I(o),S("Thank you",o),S("Powered by PayChat",o),...Array(i?Bt:Ut).fill(`
`)].join("")},Ge=$t,xt="\x1BE",vt="\x1BE\0",se="\x1Ba\0",ie="\x1Ba",ae="!",re="!\0",ce=1,le=3,g=(t="")=>String(t??"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,"").replace(/\s+/g," ").trim(),_=(...t)=>{for(const e of t){const n=g(e);if(n)return n}return""},pe=(t="58mm")=>t==="80mm"?48:32,M=(t,e="-")=>e.repeat(t),B=(t,e=!0)=>e?`${xt}${t}${vt}`:t,de=(t,e=!0)=>e?`${ae}${xt}${t}${vt}${re}`:t,me=(t,e)=>{const n=g(t),o=Math.max(0,Math.floor((e-n.length)/2));return`${" ".repeat(o)}${n}`},Y=(t,e,n=!0)=>n?`${ie}${t}${se}`:me(t,e),R=(t,e,n)=>{const o=g(t),s=g(e),i=Math.max(1,n-o.length-s.length);return`${o}${" ".repeat(i)}${s}`},it=(t,e,n="")=>{const o=g(t);if(!o)return[];const s=Math.max(8,e-n.length),i=[],a=o.split(" ");let r="";return a.forEach(c=>{if(!r){r=c;return}if(`${r} ${c}`.length<=s){r=`${r} ${c}`;return}i.push(r),r=c}),r&&i.push(r),i.flatMap(c=>{if(c.length<=s)return[`${n}${c}`];const m=[];for(let u=0;u<c.length;u+=s)m.push(`${n}${c.slice(u,u+s)}`);return m})},F=(t={})=>t&&typeof t=="object"?_(t.table_display,t.tableDisplay,t.name,t.code,t.table_name,t.tableName):"",ue=(t={})=>{const e=[t,t.order,t.table_session,t.tableSession,t.order?.table_session,t.order?.tableSession,t.table,t.order?.table].filter(Boolean);for(const n of e){const o=_(n.table_display,n.tableDisplay,n.table_group_label,n.tableGroupLabel);if(o)return o}for(const n of e){const s=(Array.isArray(n.tables)?n.tables:[]).map(F).filter(Boolean);if(s.length)return s.join(" + ")}for(const n of e){const o=Array.isArray(n.linked_tables)?n.linked_tables:Array.isArray(n.linkedTables)?n.linkedTables:[],s=[F(n.primary_table||n.primaryTable),F(n.table),...o.map(F)].filter(Boolean);if(s.length)return[...new Set(s)].join(" + ")}for(const n of e){const o=_(n.table_name,n.tableName,n.name,n.code);if(o)return o}return""},Nt=(t={})=>_(t.product_name,t.name,t.product?.name,t.item_name,"Item"),It=(t={})=>{const e=Number(t.quantity??t.qty??1);return Number.isFinite(e)&&e>0?e:1},St=t=>Number.isInteger(t)?String(t):String(t).replace(/\.0+$/,""),he=(t={},e)=>[_(t.variant,t.variant_name),...Array.isArray(t.modifiers)?t.modifiers.map(o=>_(o.name,o.label,o)):[],_(t.notes,t.note,t.kitchen_note,t.instructions)].filter(Boolean).flatMap(o=>it(o,e,"  - ")),Tt=(t={})=>{const e=t.print_data||t.printData||t.batch||t,n=_(e.batch_code,e.batchCode,e.code,`KOT-${e.id||e.batch_id||""}`);return{outlet:_(e.outlet,e.store_name,e.location?.name,e.location_name),code:n,tokenNo:_(e.token_no,e.tokenNo,e.token_number,e.tokenNumber,e.token?.token_code,e.token?.token_no,e.order?.token?.token_code,e.order?.token_no,n),orderNo:_(e.order?.order_no,e.order_no,e.orderNo,e.order?.id,e.order_id),table:ue(e),status:_(e.status,"waiting"),time:_(e.sent_at,e.created_at,new Date().toISOString()),orderNotes:_(e.order?.notes,e.notes,e.table_notes),items:Array.isArray(e.items)?e.items:[]}},ge=(t={},e,n=!0)=>{const s=`${St(It(t))} x`,i=" ".repeat(Math.min(7,s.length+2)),a=it(Nt(t),e-i.length);return a.length?[`${B(s.padEnd(i.length-1),n)} ${a[0].trim()}`,...a.slice(1).map(r=>`${i}${r.trim()}`)]:[B(s,n)]},_e=(t={},e={})=>{const n=e.paperSize||"58mm",o=pe(n),s=e.escposCommands===!0,i=Tt(t),a=[];return i.outlet&&a.push(Y(B(i.outlet.toUpperCase(),s),o,s)),a.push(Y(B("KITCHEN ORDER TOKEN",s),o,s)),a.push(M(o)),a.push(Y(de(`TOKEN ${i.tokenNo||i.code}`,s),o,s)),a.push(M(o)),a.push(R("KOT",i.code,o)),i.orderNo&&a.push(R("Order",i.orderNo,o)),i.table&&a.push(R("Table",i.table,o)),a.push(R("Status",i.status,o)),a.push(R("Time",i.time.replace("T"," ").slice(0,16),o)),a.push(M(o)),i.items.forEach(r=>{a.push(...ge(r,o,s)),a.push(...he(r,o)),a.push(...Array(ce).fill(""))}),i.orderNotes&&(a.push(M(o)),a.push(B("Notes",s)),a.push(...it(i.orderNotes,o))),a.push(M(o)),a.push(...Array(le).fill("")),a.join(`
`)},fe=(t={})=>{const e=Tt(t),n=e.items.map(o=>`
    <div class="item">
      <div class="qty">${g(St(It(o)))} x</div>
      <div class="name">${g(Nt(o))}</div>
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
  <div class="items">${n}</div>
  ${e.orderNotes?`<div class="line"></div><p class="notes"><strong>Notes:</strong> ${g(e.orderNotes)}</p>`:""}
  <div class="line"></div>
</body>
</html>`},be=(t={},e={})=>({text:_e(t,e),html:fe(t),print_mode:e.printMode||"escpos"}),Et="paychat_print_agent_settings",tt={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},ye=8e3,lt=12e3,$e=1,xe=3,ve=["invoice_url","invoiceUrl","review_url","reviewUrl"],Ne=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},at=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),Ie=t=>t==="80mm"?"80mm":"58mm",Se=t=>t==="pdf"?"pdf":"escpos",T=(t={})=>({...tt,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||tt.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:Ie(t?.paperSize),printMode:Se(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),w=()=>typeof localStorage>"u"?{...tt}:T(Ne(localStorage.getItem(Et),{})),Te=(t={})=>{const e=T({...w(),...t});try{localStorage.setItem(Et,JSON.stringify(e))}catch{}return e},X=(t,e="PRINT_AGENT_ERROR",n=null)=>{const o=new Error(t);return o.code=e,n&&(o.cause=n),o},K=(t,e={},n={})=>{const o=T(e),s=new URL(t,`${o.agentUrl}/`),i={token:o.token,size:o.paperSize,printer_name:o.printerName,copies:1,print_mode:o.printMode,...n};return Object.entries(i).forEach(([a,r])=>{r!=null&&r!==""&&s.searchParams.set(a,String(r))}),s.toString()},Q=async(t,e={},n=ye)=>{const o=new AbortController,s=setTimeout(()=>o.abort(),n);try{const i=await fetch(t,{...e,signal:o.signal}),r=(i.headers.get("content-type")||"").includes("application/json")?await i.json().catch(()=>null):await i.text().catch(()=>"");if(!i.ok)throw X(r?.message||r?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return r}catch(i){throw i?.name==="AbortError"?X("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",i):i?.code?i:X("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",i)}finally{clearTimeout(s)}},Ee=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},we=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),et=(t,e=0,n=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(we)?t:[];if(typeof t!="object"||n.has(t))return[];n.add(t);const o=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const s of o){const i=et(t[s],e+1,n);if(i.length)return i}for(const s of Object.values(t)){const i=et(s,e+1,n);if(i.length)return i}return[]},U=(...t)=>{for(const e of t){const n=Number(e);if(Number.isFinite(n))return n}return 0},k=(...t)=>{for(const e of t){const n=at(e).trim();if(n)return n}return""},V=(t={})=>{const e=t.invoice||t.invoice_data||{},n=t.data||t.order||{};return Ee(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,n.items,n.order_items,n.line_items,n.cart_items,n.invoice_items,n.details,et(t))},H=(t={})=>k(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),j=(t={})=>U(t.quantity,t.qty,t.pivot?.quantity,1)||1,J=(t={})=>{const e=j(t),n=k(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(n!=="")return Number(n||0);const o=k(t.total,t.line_total,t.amount,t.subtotal);return Number(o||0)/e},wt=(t={})=>{const e=k(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):J(t)*j(t)},Ae=(t=[])=>t.map(e=>({...e,product_name:H(e),name:H(e),quantity:j(e),qty:j(e),rate:J(e),price:J(e),total:wt(e)})),ke=(t,e)=>{const n=at(t);if(n.length<=e)return[n];const o=[];for(let s=0;s<n.length;s+=e)o.push(n.slice(s,s+e));return o},Pe=(t,e)=>{const n=e==="80mm"?48:32;return at(t).split(/\r?\n/).flatMap(o=>ke(o,n)).join(`
`)},Oe=(t={},e="58mm")=>{const n=e==="80mm"?48:32,o=V(t);return o.length?o.map(s=>{const i=H(s),a=j(s),r=J(s),m=wt(s).toFixed(2),u=`${a} x ${r.toFixed(2)}`,f=Math.max(1,n-u.length-m.length);return`${i}
${u}${" ".repeat(f)}${m}`}).join(`
`):""},Ce=(t,e,n)=>{const o=V(e);return!o.length||o.some(i=>{const a=H(i);return a&&t.includes(a.slice(0,Math.min(a.length,12)))})?t:`${t}
${Oe(e,n)}`},Le=(t,e)=>{if(/total/i.test(t))return t;const n=U(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,V(e).reduce((o,s)=>{const i=U(s.quantity,s.qty,1)||1,a=U(s.rate,s.price,s.unit_price);return o+U(s.total,s.line_total,s.amount,i*a)},0));return`${t}
TOTAL ${n.toFixed(2)}`},pt=t=>`\x1BE${t}\x1BE\0`,De=(t="",e={})=>{const n=k(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(o=>{const s=o.trim();return s&&(n&&s.toLowerCase()===n.toLowerCase()||/^invoice no\b/i.test(s)||/^total\b/i.test(s))?pt(o):o}).join(`
`)},qe=(t={},e={})=>{for(const n of ve){const o=k(t[n],e[n]);if(o)return o}return k(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},ze=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},At=(t={},e={},n=w())=>{const o=T(n),s=o.paperSize,i={...t||{},items:Ae(V(t||{}))},a=Zt(i,e||{}),r={paperSize:s,customPrintInvoice:o.customPrintInvoice,hideInvoiceQr:o.hideInvoiceQr,escposCommands:o.printMode==="escpos"};let c=$t(a,r);const m=te(a,{...r,agentPdf:o.printMode==="pdf"});typeof c!="string"&&(c=String(c??"")),c=Ce(c,i,s),c=Le(c,i),c=Pe(c,s),o.customPrintInvoice&&(c=De(c,a)),c.length>lt&&(c=`${c.slice(0,lt)}
--- Receipt truncated ---`),c=c.replace(/\n*$/,`
`.repeat(o.customPrintInvoice?xe:$e));const u=qe(t,a),f={text:c,html:m,print_mode:o.printMode};return!o.hideInvoiceQr&&u&&ze(u)&&(f.qr={data:u,size:6,error_correction:"M"}),f},Me=async(t=w())=>{const e=T(t);return Q(K("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Re=async(t=w())=>{const e=T(t),n=await Q(K("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(n)?n:Array.isArray(n?.printers)?n.printers:Array.isArray(n?.data)?n.data:[]},Ue=async(t=w())=>{const e=T(t);return Q(K("/test-print",e),{method:"POST"})},Be=async(t={},e={})=>{const n=T(e.settings||w()),o=At(t,e.context||{},n);return Q(K("/print",n),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(o)})},je=async(t={},e={})=>{const n=T(e.settings||w()),o=be(t,{paperSize:n.paperSize,printMode:n.printMode,escposCommands:n.printMode==="escpos"});return Q(K("/print",n),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(o)})},We={getSettings:w,saveSettings:Te,checkHealth:Me,getPrinters:Re,testPrint:Ue,printReceipt:Be,printKot:je,buildSafeAgentReceiptPayload:At},kt="paychat_kitchen_operation_mode",Pt="paychat_generate_inline_kitchen_token",Ot="paychat_inline_kitchen_without_status_management",D={DEDICATED_KDS:"dedicated_kds",INLINE:"inline"},Ct=Object.values(D),P=()=>typeof window>"u"?null:window.localStorage||null,dt=t=>{try{const e=P()?.getItem(t);return e?JSON.parse(e):null}catch{return null}},Ke=()=>{const t=dt("tenant_settings")||{},e=dt("tenant_info")||{},n=t?.kitchen?.operation_mode||t?.raw?.kitchen_operation_mode||e?.settings?.kitchen?.operation_mode||e?.settings?.raw?.kitchen_operation_mode||e?.tenant?.settings?.kitchen?.operation_mode||e?.tenant?.settings?.raw?.kitchen_operation_mode;return Ct.includes(n)?n:null},Qe=()=>{const e=P()?.getItem(kt);return Ct.includes(e)?e:Ke()||D.DEDICATED_KDS},He=t=>{const e=t===D.INLINE?D.INLINE:D.DEDICATED_KDS;return P()?.setItem(kt,e),e},Je=()=>Qe()===D.INLINE,Ve=()=>P()?.getItem(Pt)==="true",Ye=t=>{const e=!!t;return P()?.setItem(Pt,e?"true":"false"),e},Xe=()=>P()?.getItem(Ot)==="true",Ze=t=>{const e=!!t;return P()?.setItem(Ot,e?"true":"false"),e};export{D as K,te as a,Ge as b,Xe as c,Ve as d,Ze as e,Ye as f,Qe as g,Je as i,Zt as n,We as p,He as s};
