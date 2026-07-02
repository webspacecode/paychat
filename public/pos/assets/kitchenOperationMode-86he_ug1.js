import{g as et,b as m}from"./index-D4ioXmBe.js";const Pt="/color-paychat-logo-main.svg",Ct="\x1BE",Ot="\x1BE\0",Lt="\x1BG",qt="\x1BG\0",rt={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},pt=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},nt=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},zt=()=>pt(nt("tenant_info"),{}),Mt=()=>pt(nt("selected_location"),{}),dt=t=>R(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),ut=t=>dt(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),mt=t=>{const e=dt(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},a=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),z=t=>a(t).replace(/`/g,"&#096;"),R=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),d=t=>Number(t||0).toFixed(2),W=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},ht=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},gt=(t="80mm")=>rt[t]||rt["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},Rt=t=>{const e=String(t||"").trim();if(!e)return"";try{const o=typeof window<"u"?window.location.origin:"https://paychat.local",r=new URL(e,o).pathname.split("/").map(c=>c.trim()).filter(Boolean),s=r.findIndex(c=>["invoice","invoices"].includes(c.toLowerCase())),i=s>=0?r[s+1]:r[r.length-1];return decodeURIComponent(i||"").trim()}catch{const r=e.split("?")[0].split("#")[0].split("/").map(s=>s.trim()).filter(Boolean);return r[r.length-1]||""}},q=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},Ut=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Dt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),X=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Dt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=X(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=X(r,e+1,o);if(s.length)return s}return[]},Bt=(t={})=>p(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),ot=(t={})=>q(t.quantity,t.qty,t.pivot?.quantity,1)||1,ft=(t={})=>{const e=ot(t),o=p(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=p(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},Kt=(t={})=>{const e=p(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):ft(t)*ot(t)},jt=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Ut(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,X(t))},Qt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},it=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=p(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},Wt=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},Ft=t=>{if(!t)return"";const e=Wt(t),o=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const r=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${z(r)}" alt="Invoice QR" />`}const n=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return n?.[1]?`<img class="qr-image" src="${z(n[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${z(e)}" alt="Invoice QR" />`:`<div class="qr-url">${a(e)}</div>`},Gt=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=zt(),n=et(),r=o?.tenant||{},s=o?.branding||r?.branding||o?.branching||{},i=Mt(),c={...i&&typeof i=="object"?i:{},...t.location&&typeof t.location=="object"?t.location:{}};t.branch||t.branching||t.branding||o?.branch||o?.branching;const l=t.merchant||t.receipt?.merchant||{},u=t.invoice||t.invoice_data||t.receipt?.invoice||{},h=t.qr||t.receipt?.qr||{},b=p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,u.url,t.meta?.invoice?.url,h.invoice_url),N=jt(t).map(y=>({name:Bt(y),qty:ot(y),rate:ft(y),total:Kt(y)})),_=q(t.subtotal,t.totals?.subtotal,N.reduce((y,T)=>y+T.rate*T.qty,0)),k=q(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),A=q(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),x=q(t.total,t.grand_total,t.totals?.grand_total,_+A-k);return{shopName:p(e.shopName,l.name,c.tenant?.name,t.tenant?.name,s.company_name,r.name,nt("tenant_slug"),"PayChat POS"),shopPhone:p(e.shopPhone,l.phone,c.phone,s.phone,r.phone),shopAddress:p(e.shopAddress,s.address,r.branding?.address,t.tenant?.branding?.address,c.tenant?.branding?.address),shopLogoUrl:p(e.shopLogoUrl,c.logo,c.tenant?.logo,t.tenant?.logo,s.logo,r.logo),locationName:p(c.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,Pt),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,u.number,u.invoice_no,u.invoiceNo,u.invoice_number,u.offline_invoice_number,t.meta?.invoice?.number,t.meta?.invoice?.invoice_no,t.meta?.invoice?.invoiceNo,t.meta?.invoice?.invoice_number,t.offline_invoice_number,t.local_invoice_no,Rt(b)),orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:it(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:it(t.batch_codes,t.batchCodes),items:N,subtotal:_,discount:k,tax:A,grandTotal:x,paidAmount:q(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,x),paymentMethod:Qt(t),invoiceUrl:b,invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,h.qr_svg_or_url,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},Ht=(t,e={})=>{const o=e.paperSize||"80mm",n=gt(o),r=o==="58mm",s=e.agentPdf===!0,i=e.customPrintInvoice===!0,c=e.hideInvoiceQr===!0,l=Array.isArray(t.items)?t.items:[],u=Array.isArray(t.kotCodes)?t.kotCodes:[],h=Array.isArray(t.batchCodes)?t.batchCodes:[],b=et(),_=!(t.simpleBilling??b.simpleBilling),k=i?ut(t.shopName):t.shopName,A=i?mt(t.shopAddress):t.shopAddress,x=p(t.invoiceNo),y=i?r?"48px":"64px":n.paychatLogoWidth,T=c?"":Ft(t.invoiceQr||t.reviewQr),U=t.invoiceUrl&&(c||!T)?`<div class="qr-url">${a(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
      font-size: ${i?r?"15px":"18px":n.titleSize};
      font-weight: ${i?"900":"800"};
      text-transform: ${i?"none":"uppercase"};
      ${i?"text-shadow: 0 0 0 #000, 0.25px 0 #000, -0.25px 0 #000; -webkit-text-stroke: 0.25px #000;":""}
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
      max-width: ${y};
      max-height: ${r?"20px":"26px"};
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
      ${!i&&t.shopLogoUrl?`<img class="shop-logo" src="${z(t.shopLogoUrl)}" alt="${z(k)}" />`:""}
      <div class="title">${a(k)}</div>
      ${!i&&t.locationName?`<div class="muted">${a(t.locationName)}</div>`:""}
      ${A?`<div class="muted">${a(A)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${a(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${x&&!i?`<div class="bill-no">INVOICE NO: ${a(x)}</div>`:""}
    <table>
	      ${x&&i?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${a(x)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${a(ht(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${a(t.orderType)}</td></tr>`:""}
	      ${_&&t.tableName?`<tr><td>Table</td><td class="right">${a(t.tableName)}</td></tr>`:""}
	      ${_&&t.guestCount&&!i?`<tr><td>Guests</td><td class="right">${a(t.guestCount)}</td></tr>`:""}
	      ${_&&t.tokenNo&&!i?`<tr><td>Token</td><td class="right">${a(t.tokenNo)}</td></tr>`:""}
	      ${_&&u.length?`<tr><td>KOT</td><td class="right">${a(u.join(", "))}</td></tr>`:""}
	      ${_&&h.length?`<tr><td>Batch</td><td class="right">${a(h.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${r?`
      <div>
        ${l.length?l.map($=>`
          <div class="item-block">
            <div class="item-name">${a($.name)}</div>
            <div class="item-meta">
              <span>${a(W($.qty))} x ${a(d($.rate))}</span>
              <strong>${a(d($.total))}</strong>
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
          ${l.length?l.map($=>`
            <tr>
              <td class="item-name">${a($.name)}</td>
              <td class="right">${a(W($.qty))}</td>
              <td class="right">${a(d($.rate))}</td>
              <td class="right">${a(d($.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${i?`
      <div class="total-row grand"><span>TOTAL</span><span>${a(d(t.grandTotal))}</span></div>
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${a(t.paymentMethod)}</span></div>`:""}
    `:s?`
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
    ${T||U?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!c&&T?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${T||U}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!i?`<img class="paychat-logo" src="${z(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},S=(t,e="-")=>`${e.repeat(t)}
`,bt=(t="")=>`${Ct}${Lt}${t}${qt}${Ot}`,Jt=(t="")=>bt(t),I=(t,e)=>{const o=R(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},g=(t,e,o)=>{const n=R(e),r=Math.max(1,o-n.length-1),s=R(t).slice(0,r),i=Math.max(1,o-s.length-n.length);return`${s}${" ".repeat(i)}${n}
`},F=(t,e)=>{const o=R(t).split(/\s+/).filter(Boolean).flatMap(s=>s.length<=e?[s]:s.match(new RegExp(`.{1,${e}}`,"g"))||[s]),n=[];let r="";return o.forEach(s=>{if(!r){r=s;return}(r+" "+s).length<=e?r+=` ${s}`:(n.push(r),r=s.slice(0,e))}),r&&n.push(r),n.length?n:[""]},Vt=(t,e)=>{const o=F(t.name,e),n=`${W(t.qty)} x ${d(t.rate)}`;return[...o.map(r=>`${r}
`),g(n,d(t.total),e)].join("")},Yt=(t,e)=>{const s=e-5-9-10,i=F(t.name,s),c=`${i[0].padEnd(s)}${W(t.qty).padStart(5)}${d(t.rate).padStart(9)}${d(t.total).padStart(10)}
`,l=i.slice(1).map(u=>`${u}
`).join("");return c+l},_t=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=gt(o),r=o==="58mm",s=e.customPrintInvoice===!0,i=e.hideInvoiceQr===!0,c=e.escposCommands===!0,l=Array.isArray(t.items)?t.items:[],u=Array.isArray(t.kotCodes)?t.kotCodes:[],h=Array.isArray(t.batchCodes)?t.batchCodes:[],b=et(),N=t.simpleBilling??b.simpleBilling,_=r?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,k=s?ut(t.shopName):t.shopName,A=s?mt(t.shopAddress):t.shopAddress,x=p(t.invoiceNo),y=I(k,n),T=A?F(A,n).map(O=>I(O,n)).join(""):"",U=x?g("Invoice No",x,n):"",$=s&&t.tokenNo?`${S(n)}${I(`TOKEN ${t.tokenNo}`,n)}${S(n)}`:"",kt=t.invoiceUrl?`${S(n)}${I(i?"Invoice link":"Invoice/review link",n)}${F(t.invoiceUrl,n).map(O=>`${R(O)}
`).join("")}`:"";return[$,c?Jt(y):y,!s&&t.locationName?I(t.locationName,n):"",T,t.shopPhone?I(`Phone: ${t.shopPhone}`,n):"",S(n),c?bt(U):U,g("Date",ht(t.dateTime),n),t.orderType?g("Type",t.orderType,n):"",!N&&t.tableName?g("Table",t.tableName,n):"",!N&&t.guestCount&&!s?g("Guests",t.guestCount,n):"",!N&&t.tokenNo&&!s?g("Token",t.tokenNo,n):"",!N&&u.length?g("KOT",u.join(","),n):"",!N&&h.length?g("Batch",h.join(","),n):"",S(n),_,_?S(n):"",l.length?l.map(O=>r?Vt(O,n):Yt(O,n)).join(""):I("No items",n),S(n),s?"":g("Subtotal",d(t.subtotal),n),!s&&t.discount?g("Discount",`-${d(t.discount)}`,n):"",!s&&t.tax?g("Tax/GST",d(t.tax),n):"",s?"":S(n),g("TOTAL",d(t.grandTotal),n),t.paidAmount&&!s?g("Paid",d(t.paidAmount),n):"",t.paymentMethod?g("Payment",t.paymentMethod,n):"",kt,S(n),I("Thank you",n),I("Powered by PayChat",n)].join("")},ke=_t,Xt="\x1BE",Zt="\x1BE\0",f=(t="")=>String(t??"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,"").replace(/\s+/g," ").trim(),v=(...t)=>{for(const e of t){const o=f(e);if(o)return o}return""},te=(t="58mm")=>t==="80mm"?48:32,Q=(t,e="-")=>e.repeat(t),V=(t,e=!0)=>e?`${Xt}${t}${Zt}`:t,L=(t,e,o)=>{const n=f(t),r=f(e),s=Math.max(1,o-n.length-r.length);return`${n}${" ".repeat(s)}${r}`},yt=(t,e,o="")=>{const n=f(t);if(!n)return[];const r=Math.max(8,e-o.length),s=[];for(let i=0;i<n.length;i+=r)s.push(`${o}${n.slice(i,i+r)}`);return s},$t=(t={})=>v(t.product_name,t.name,t.product?.name,t.item_name,"Item"),vt=(t={})=>{const e=Number(t.quantity??t.qty??1);return Number.isFinite(e)&&e>0?e:1},ee=(t={},e)=>[v(t.variant,t.variant_name),...Array.isArray(t.modifiers)?t.modifiers.map(n=>v(n.name,n.label,n)):[],v(t.notes,t.note,t.kitchen_note,t.instructions)].filter(Boolean).flatMap(n=>yt(n,e,"  - ")),xt=(t={})=>{const e=t.print_data||t.printData||t.batch||t;return{outlet:v(e.outlet,e.store_name,e.location?.name,e.location_name),code:v(e.batch_code,e.batchCode,e.code,`KOT-${e.id||e.batch_id||""}`),orderNo:v(e.order?.order_no,e.order_no,e.orderNo,e.order?.id,e.order_id),table:v(e.table_display,e.table?.name,e.table?.code,e.order?.table_display),status:v(e.status,"waiting"),time:v(e.sent_at,e.created_at,new Date().toISOString()),orderNotes:v(e.order?.notes,e.notes,e.table_notes),items:Array.isArray(e.items)?e.items:[]}},ne=(t={},e={})=>{const o=e.paperSize||"58mm",n=te(o),r=e.escposCommands===!0,s=xt(t),i=[];return s.outlet&&i.push(V(s.outlet.toUpperCase(),r)),i.push(V("KITCHEN ORDER TOKEN",r)),i.push(Q(n)),i.push(L("KOT",s.code,n)),s.orderNo&&i.push(L("Order",s.orderNo,n)),s.table&&i.push(L("Table",s.table,n)),i.push(L("Status",s.status,n)),i.push(L("Time",s.time.replace("T"," ").slice(0,16),n)),i.push(Q(n)),s.items.forEach(c=>{i.push(L($t(c),`x${vt(c)}`,n)),i.push(...ee(c,n))}),s.orderNotes&&(i.push(Q(n)),i.push(V("Notes",r)),i.push(...yt(s.orderNotes,n))),i.push(Q(n)),i.push(""),i.push(""),i.push(""),i.join(`
`)},oe=(t={})=>{const e=xt(t),o=e.items.map(n=>`
    <tr>
      <td>${f($t(n))}</td>
      <td class="qty">x${vt(n)}</td>
    </tr>
  `).join("");return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: monospace; width: 280px; margin: 0; color: #111; }
    h1, h2, p { margin: 0; }
    h1 { font-size: 18px; text-align: center; }
    h2 { font-size: 14px; text-align: center; margin-bottom: 8px; }
    .line { border-top: 1px dashed #111; margin: 8px 0; }
    .meta { display: flex; justify-content: space-between; gap: 8px; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    td { padding: 3px 0; vertical-align: top; }
    .qty { text-align: right; font-weight: 700; }
    .notes { font-size: 12px; margin-top: 8px; }
  </style>
</head>
<body>
  ${e.outlet?`<h1>${f(e.outlet).toUpperCase()}</h1>`:""}
  <h2>KITCHEN ORDER TOKEN</h2>
  <div class="line"></div>
  <p class="meta"><strong>KOT</strong><span>${f(e.code)}</span></p>
  ${e.orderNo?`<p class="meta"><strong>Order</strong><span>${f(e.orderNo)}</span></p>`:""}
  ${e.table?`<p class="meta"><strong>Table</strong><span>${f(e.table)}</span></p>`:""}
  <p class="meta"><strong>Status</strong><span>${f(e.status)}</span></p>
  <p class="meta"><strong>Time</strong><span>${f(e.time.replace("T"," ").slice(0,16))}</span></p>
  <div class="line"></div>
  <table>${o}</table>
  ${e.orderNotes?`<div class="line"></div><p class="notes"><strong>Notes:</strong> ${f(e.orderNotes)}</p>`:""}
  <div class="line"></div>
</body>
</html>`},se=(t={},e={})=>({text:ne(t,e),html:oe(t),print_mode:e.printMode||"escpos"}),Nt="paychat_print_agent_settings",Z={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},re=8e3,at=12e3,ie=["invoice_url","invoiceUrl","review_url","reviewUrl"],ae=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},st=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),ce=t=>t==="80mm"?"80mm":"58mm",le=t=>t==="pdf"?"pdf":"escpos",w=(t={})=>({...Z,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||Z.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:ce(t?.paperSize),printMode:le(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),E=()=>typeof localStorage>"u"?{...Z}:w(ae(localStorage.getItem(Nt),{})),pe=(t={})=>{const e=w({...E(),...t});try{localStorage.setItem(Nt,JSON.stringify(e))}catch{}return e},Y=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},K=(t,e={},o={})=>{const n=w(e),r=new URL(t,`${n.agentUrl}/`),s={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(s).forEach(([i,c])=>{c!=null&&c!==""&&r.searchParams.set(i,String(c))}),r.toString()},j=async(t,e={},o=re)=>{const n=new AbortController,r=setTimeout(()=>n.abort(),o);try{const s=await fetch(t,{...e,signal:n.signal}),c=(s.headers.get("content-type")||"").includes("application/json")?await s.json().catch(()=>null):await s.text().catch(()=>"");if(!s.ok)throw Y(c?.message||c?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return c}catch(s){throw s?.name==="AbortError"?Y("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",s):s?.code?s:Y("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",s)}finally{clearTimeout(r)}},de=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},ue=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),tt=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(ue)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=tt(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=tt(r,e+1,o);if(s.length)return s}return[]},D=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},P=(...t)=>{for(const e of t){const o=st(e).trim();if(o)return o}return""},J=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return de(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,tt(t))},G=(t={})=>P(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),B=(t={})=>D(t.quantity,t.qty,t.pivot?.quantity,1)||1,H=(t={})=>{const e=B(t),o=P(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=P(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},Tt=(t={})=>{const e=P(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):H(t)*B(t)},me=(t=[])=>t.map(e=>({...e,product_name:G(e),name:G(e),quantity:B(e),qty:B(e),rate:H(e),price:H(e),total:Tt(e)})),he=(t,e)=>{const o=st(t);if(o.length<=e)return[o];const n=[];for(let r=0;r<o.length;r+=e)n.push(o.slice(r,r+e));return n},ge=(t,e)=>{const o=e==="80mm"?48:32;return st(t).split(/\r?\n/).flatMap(n=>he(n,o)).join(`
`)},fe=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=J(t);return n.length?n.map(r=>{const s=G(r),i=B(r),c=H(r),u=Tt(r).toFixed(2),h=`${i} x ${c.toFixed(2)}`,b=Math.max(1,o-h.length-u.length);return`${s}
${h}${" ".repeat(b)}${u}`}).join(`
`):""},be=(t,e,o)=>{const n=J(e);return!n.length||n.some(s=>{const i=G(s);return i&&t.includes(i.slice(0,Math.min(i.length,12)))})?t:`${t}
${fe(e,o)}`},_e=(t,e)=>{if(/total/i.test(t))return t;const o=D(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,J(e).reduce((n,r)=>{const s=D(r.quantity,r.qty,1)||1,i=D(r.rate,r.price,r.unit_price);return n+D(r.total,r.line_total,r.amount,s*i)},0));return`${t}
TOTAL ${o.toFixed(2)}`},ct=t=>`\x1BE${t}\x1BE\0`,ye=(t="",e={})=>{const o=P(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(n=>{const r=n.trim();return r&&(o&&r.toLowerCase()===o.toLowerCase()||/^invoice no\b/i.test(r)||/^total\b/i.test(r))?ct(n):n}).join(`
`)},$e=(t={},e={})=>{for(const o of ie){const n=P(t[o],e[o]);if(n)return n}return P(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},ve=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},St=(t={},e={},o=E())=>{const n=w(o),r=n.paperSize,s={...t||{},items:me(J(t||{}))},i=Gt(s,e||{}),c={paperSize:r,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr,escposCommands:n.printMode==="escpos"};let l=_t(i,c);const u=Ht(i,{...c,agentPdf:n.printMode==="pdf"});typeof l!="string"&&(l=String(l??"")),l=be(l,s,r),l=_e(l,s),l=ge(l,r),n.customPrintInvoice&&(l=ye(l,i)),l.length>at&&(l=`${l.slice(0,at)}
--- Receipt truncated ---`),l=l.replace(/\n*$/,`


`);const h=$e(t,i),b={text:l,html:u,print_mode:n.printMode};return!n.hideInvoiceQr&&h&&ve(h)&&(b.qr={data:h,size:6,error_correction:"M"}),b},xe=async(t=E())=>{const e=w(t);return j(K("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Ne=async(t=E())=>{const e=w(t),o=await j(K("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},Te=async(t=E())=>{const e=w(t);return j(K("/test-print",e),{method:"POST"})},Se=async(t={},e={})=>{const o=w(e.settings||E()),n=St(t,e.context||{},o);return j(K("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},Ie=async(t={},e={})=>{const o=w(e.settings||E()),n=se(t,{paperSize:o.paperSize,printMode:o.printMode,escposCommands:o.printMode==="escpos"});return j(K("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},Pe={getSettings:E,saveSettings:pe,checkHealth:xe,getPrinters:Ne,testPrint:Te,printReceipt:Se,printKot:Ie,buildSafeAgentReceiptPayload:St},Ce={createOrder(t){return m.post("/orders",t)},diningStructure(t={}){return m.get("/dining-structure",{params:t})},bulkSaveTables(t){return m.post("/dining-structure/tables/bulk",t)},updateTablePosition(t,e){return m.patch(`/dining-structure/tables/${t}/position`,e)},list(t={}){return m.get("/tables",{params:t})},update(t,e){return m.patch(`/tables/${t}`,e)},updateStatus(t,e){return m.patch(`/tables/${t}/status`,e)},release(t,e={}){return m.post(`/tables/${t}/release`,e)},createSession(t){return m.post("/table-sessions",t)},openSessions(t={}){return m.get("/table-sessions/open",{params:t})},closeSession(t,e={}){return m.post(`/table-sessions/${t}/close`,e)},assignOrder(t,e){return m.patch(`/orders/${t}/table`,e)},linkOrderTables(t,e){return m.post(`/orders/${t}/tables/link`,e)},sendToKitchen(t,e={}){return m.post(`/orders/${t}/send-to-kitchen`,e)},printKot(t){return m.post(`/orders/${t}/print-kot`)},reprintKitchenBatch(t){return m.post(`/kitchen-batches/${t}/reprint`)},cancelKitchenBatch(t){return m.post(`/kitchen-batches/${t}/cancel`)},generateInlineToken(t){return m.post(`/orders/${t}/inline-token`)}},It="paychat_kitchen_operation_mode",wt="paychat_generate_inline_kitchen_token",At="paychat_inline_kitchen_without_status_management",M={DEDICATED_KDS:"dedicated_kds",INLINE:"inline"},Et=Object.values(M),C=()=>typeof window>"u"?null:window.localStorage||null,lt=t=>{try{const e=C()?.getItem(t);return e?JSON.parse(e):null}catch{return null}},we=()=>{const t=lt("tenant_settings")||{},e=lt("tenant_info")||{},o=t?.kitchen?.operation_mode||t?.raw?.kitchen_operation_mode||e?.settings?.kitchen?.operation_mode||e?.settings?.raw?.kitchen_operation_mode||e?.tenant?.settings?.kitchen?.operation_mode||e?.tenant?.settings?.raw?.kitchen_operation_mode;return Et.includes(o)?o:null},Ae=()=>{const e=C()?.getItem(It);return Et.includes(e)?e:we()||M.DEDICATED_KDS},Oe=t=>{const e=t===M.INLINE?M.INLINE:M.DEDICATED_KDS;return C()?.setItem(It,e),e},Le=()=>Ae()===M.INLINE,qe=()=>C()?.getItem(wt)==="true",ze=t=>{const e=!!t;return C()?.setItem(wt,e?"true":"false"),e},Me=()=>C()?.getItem(At)==="true",Re=t=>{const e=!!t;return C()?.setItem(At,e?"true":"false"),e};export{M as K,Ht as a,ke as b,Me as c,qe as d,Re as e,ze as f,Ae as g,Le as i,Gt as n,Pe as p,Oe as s,Ce as t};
