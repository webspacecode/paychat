import{g as rt}from"./index-CT88HlNI.js";const jt="/color-paychat-logo-main.svg",Kt="\x1BE",Ft="\x1BE\0",Gt="\x1BG",Wt="\x1BG\0",Ht=1,Jt=3,dt={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},ft=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},at=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},Vt=()=>ft(at("tenant_info"),{}),Yt=()=>ft(at("selected_location"),{}),bt=t=>M(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),yt=t=>bt(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),$t=t=>{const e=bt(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},l=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),A=t=>l(t).replace(/`/g,"&#096;"),M=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),d=t=>Number(t||0).toFixed(2),H=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},vt=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},xt=(t="80mm")=>dt[t]||dt["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},Xt=t=>{const e=String(t||"").trim();if(!e)return"";try{const o=typeof window<"u"?window.location.origin:"https://paychat.local",s=new URL(e,o).pathname.split("/").map(a=>a.trim()).filter(Boolean),i=s.findIndex(a=>["invoice","invoices"].includes(a.toLowerCase())),r=i>=0?s[i+1]:s[s.length-1];return decodeURIComponent(r||"").trim()}catch{const s=e.split("?")[0].split("#")[0].split("/").map(i=>i.trim()).filter(Boolean);return s[s.length-1]||""}},D=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},Zt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},te=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),nt=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(te)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const s of n){const i=nt(t[s],e+1,o);if(i.length)return i}for(const s of Object.values(t)){const i=nt(s,e+1,o);if(i.length)return i}return[]},ee=(t={})=>p(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),ct=(t={})=>D(t.quantity,t.qty,t.pivot?.quantity,1)||1,Nt=(t={})=>{const e=ct(t),o=p(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=p(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},ne=(t={})=>{const e=p(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):Nt(t)*ct(t)},oe=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Zt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,o.items,o.order_items,o.line_items,o.cart_items,o.invoice_items,o.details,nt(t))},se=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},mt=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=p(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},ie=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},ut=(t,e="Invoice QR")=>{if(!t)return"";const o=ie(t),n=o.match(/<svg\b[\s\S]*?<\/svg>/i);if(n){const i=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(n[0])}`;return`<img class="qr-image" src="${A(i)}" alt="${A(e)}" />`}const s=o.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return s?.[1]?`<img class="qr-image" src="${A(s[1])}" alt="${A(e)}" />`:/^(data:image\/|https?:\/\/|\/)/i.test(o)?`<img class="qr-image" src="${A(o)}" alt="${A(e)}" />`:`<div class="qr-url">${l(o)}</div>`},re=(t={})=>{const o=(Array.isArray(t.payments)?t.payments:[]).find(n=>String(n?.payment_method||n?.method||"").toLowerCase()==="upi"&&p(n.upi_qr_url,n.upiQrUrl,n.meta?.upi_qr_url,n.qr_payload,n.qr));return p(t.upi_qr_url,t.upiQrUrl,t.payment?.upi_qr_url,t.payment?.upiQrUrl,t.payment?.meta?.upi_qr_url,t.qr?.upi_qr_url,t.receipt?.qr?.upi_qr_url,o?.upi_qr_url,o?.upiQrUrl,o?.meta?.upi_qr_url,o?.qr_payload,o?.qr)},ae=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=Vt(),n=rt(),s=o?.tenant||{},i=o?.branding||s?.branding||o?.branching||{},r=Yt(),a={...r&&typeof r=="object"?r:{},...t.location&&typeof t.location=="object"?t.location:{}};t.branch||t.branching||t.branding||o?.branch||o?.branching;const c=t.merchant||t.receipt?.merchant||{},m=t.invoice||t.invoice_data||t.receipt?.invoice||{},u=t.qr||t.receipt?.qr||{},x=p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,m.url,t.meta?.invoice?.url,u.invoice_url),b=p(e.upiQr,e.paymentQr,re(t)),g=oe(t).map(y=>({name:ee(y),qty:ct(y),rate:Nt(y),total:ne(y)})),O=D(t.subtotal,t.totals?.subtotal,g.reduce((y,I)=>y+I.rate*I.qty,0)),k=D(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),N=D(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),q=D(t.total,t.grand_total,t.totals?.grand_total,O+N-k);return{shopName:p(e.shopName,c.name,a.tenant?.name,t.tenant?.name,i.company_name,s.name,at("tenant_slug"),"PayChat POS"),shopPhone:p(e.shopPhone,c.phone,a.phone,i.phone,s.phone),shopAddress:p(e.shopAddress,i.address,s.branding?.address,t.tenant?.branding?.address,a.tenant?.branding?.address),shopLogoUrl:p(e.shopLogoUrl,a.logo,a.tenant?.logo,t.tenant?.logo,i.logo,s.logo),locationName:p(a.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,jt),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,m.number,m.invoice_no,m.invoiceNo,m.invoice_number,m.offline_invoice_number,t.meta?.invoice?.number,t.meta?.invoice?.invoice_no,t.meta?.invoice?.invoiceNo,t.meta?.invoice?.invoice_number,t.offline_invoice_number,t.local_invoice_no,Xt(x)),orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table_display,t.tableDisplay,t.table_session?.table_display,t.tableSession?.tableDisplay,t.table_session?.table?.name,t.tableSession?.table?.name,t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:mt(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:mt(t.batch_codes,t.batchCodes),items:g,subtotal:O,discount:k,tax:N,grandTotal:q,paidAmount:D(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,q),paymentMethod:se(t),invoiceUrl:x,upiQr:b,invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,u.qr_svg_or_url,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},ce=(t,e={})=>{const o=e.paperSize||"80mm",n=xt(o),s=o==="58mm",i=e.agentPdf===!0,r=e.customPrintInvoice===!0,a=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],u=Array.isArray(t.batchCodes)?t.batchCodes:[],x=rt(),g=!(t.simpleBilling??x.simpleBilling),O=r?yt(t.shopName):t.shopName,k=r?$t(t.shopAddress):t.shopAddress,N=p(t.invoiceNo),q=r?s?"48px":"64px":n.paychatLogoWidth,y=!a&&t.upiQr?ut(t.upiQr,"UPI payment QR"):"",I=!a&&!y?ut(t.invoiceQr||t.reviewQr):"",G=!t.upiQr&&t.invoiceUrl&&(a||!I)?`<div class="qr-url">${l(t.invoiceUrl)}</div>`:"";return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Thermal Bill</title>
  <style>
    @page { size: ${n.width} auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0 0 ${r?"18px":"0"};
      background: #fff;
      color: #000;
      font-family: "Courier New", monospace;
      font-size: ${n.fontSize};
      line-height: ${r?"1.08":"1.28"};
    }
    .receipt {
      width: ${n.width};
      padding: ${r?"2px 4px 14px":n.padding};
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
      max-width: ${q};
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
    ${r&&t.tokenNo?`<div class="top-token">TOKEN ${l(t.tokenNo)}</div>`:""}
    <div class="center">
      ${!r&&t.shopLogoUrl?`<img class="shop-logo" src="${A(t.shopLogoUrl)}" alt="${A(O)}" />`:""}
      <div class="title">${l(O)}</div>
      ${!r&&t.locationName?`<div class="muted">${l(t.locationName)}</div>`:""}
      ${k?`<div class="muted">${l(k)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${l(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${N&&!r?`<div class="bill-no">INVOICE NO: ${l(N)}</div>`:""}
    <table>
	      ${N&&r?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${l(N)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${l(vt(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${l(t.orderType)}</td></tr>`:""}
	      ${(g||r)&&t.tableName?`<tr><td>Table</td><td class="right">${l(t.tableName)}</td></tr>`:""}
	      ${g&&t.guestCount&&!r?`<tr><td>Guests</td><td class="right">${l(t.guestCount)}</td></tr>`:""}
	      ${g&&t.tokenNo&&!r?`<tr><td>Token</td><td class="right">${l(t.tokenNo)}</td></tr>`:""}
	      ${g&&m.length?`<tr><td>KOT</td><td class="right">${l(m.join(", "))}</td></tr>`:""}
	      ${g&&u.length?`<tr><td>Batch</td><td class="right">${l(u.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${s?`
      <div>
        ${c.length?c.map(v=>`
          <div class="item-block">
            <div class="item-name">${l(v.name)}</div>
            <div class="item-meta">
              <span>${l(H(v.qty))} x ${l(d(v.rate))}</span>
              <strong>${l(d(v.total))}</strong>
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
          ${c.length?c.map(v=>`
            <tr>
              <td class="item-name">${l(v.name)}</td>
              <td class="right">${l(H(v.qty))}</td>
              <td class="right">${l(d(v.rate))}</td>
              <td class="right">${l(d(v.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${r?`
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
    ${I||G?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!a&&I?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${I||G}
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
      ${t.paychatLogoUrl&&!r?`<img class="paychat-logo" src="${A(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},S=(t,e="-")=>`${e.repeat(t)}
`,It=(t="")=>`${Kt}${Gt}${t}${Wt}${Ft}`,le=(t="")=>It(t),T=(t,e)=>{const o=M(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},h=(t,e,o)=>{const n=M(e),s=Math.max(1,o-n.length-1),i=M(t).slice(0,s),r=Math.max(1,o-i.length-n.length);return`${i}${" ".repeat(r)}${n}
`},J=(t,e)=>{const o=M(t).split(/\s+/).filter(Boolean).flatMap(i=>i.length<=e?[i]:i.match(new RegExp(`.{1,${e}}`,"g"))||[i]),n=[];let s="";return o.forEach(i=>{if(!s){s=i;return}(s+" "+i).length<=e?s+=` ${i}`:(n.push(s),s=i.slice(0,e))}),s&&n.push(s),n.length?n:[""]},pe=(t,e)=>{const o=J(t.name,e),n=`${H(t.qty)} x ${d(t.rate)}`;return[...o.map(s=>`${s}
`),h(n,d(t.total),e)].join("")},de=(t,e)=>{const i=e-5-9-10,r=J(t.name,i),a=`${r[0].padEnd(i)}${H(t.qty).padStart(5)}${d(t.rate).padStart(9)}${d(t.total).padStart(10)}
`,c=r.slice(1).map(m=>`${m}
`).join("");return a+c},St=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=xt(o),s=o==="58mm",i=e.customPrintInvoice===!0,r=e.hideInvoiceQr===!0,a=e.escposCommands===!0,c=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],u=Array.isArray(t.batchCodes)?t.batchCodes:[],x=rt(),b=t.simpleBilling??x.simpleBilling,g=s?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,O=i?yt(t.shopName):t.shopName,k=i?$t(t.shopAddress):t.shopAddress,N=p(t.invoiceNo),q=T(O,n),y=k?J(k,n).map(L=>T(L,n)).join(""):"",I=N?h("Invoice No",N,n):"",G=i&&t.tokenNo?`${S(n)}${T(`TOKEN ${t.tokenNo}`,n)}${S(n)}`:"",v=!t.upiQr&&t.invoiceUrl?`${S(n)}${T(r?"Invoice link":"Invoice/review link",n)}${J(t.invoiceUrl,n).map(L=>`${M(L)}
`).join("")}`:"",Bt=t.upiQr&&!r?`${S(n)}${T("Scan QR to pay via UPI",n)}`:"";return[G,a?le(q):q,!i&&t.locationName?T(t.locationName,n):"",y,t.shopPhone?T(`Phone: ${t.shopPhone}`,n):"",S(n),a?It(I):I,h("Date",vt(t.dateTime),n),t.orderType?h("Type",t.orderType,n):"",(!b||i)&&t.tableName?h("Table",t.tableName,n):"",!b&&t.guestCount&&!i?h("Guests",t.guestCount,n):"",!b&&t.tokenNo&&!i?h("Token",t.tokenNo,n):"",!b&&m.length?h("KOT",m.join(","),n):"",!b&&u.length?h("Batch",u.join(","),n):"",S(n),g,g?S(n):"",c.length?c.map(L=>s?pe(L,n):de(L,n)).join(""):T("No items",n),S(n),i?"":h("Subtotal",d(t.subtotal),n),!i&&t.discount?h("Discount",`-${d(t.discount)}`,n):"",!i&&t.tax?h("Tax/GST",d(t.tax),n):"",i?"":S(n),h("TOTAL",d(t.grandTotal),n),t.paidAmount&&!i?h("Paid",d(t.paidAmount),n):"",t.paymentMethod?h("Payment",t.paymentMethod,n):"",Bt,v,S(n),T("Thank you",n),T("Powered by PayChat",n),...Array(i?Jt:Ht).fill(`
`)].join("")},sn=St,Tt="\x1BE",Et="\x1BE\0",me="\x1Ba\0",ue="\x1Ba",he="!",_e="!\0",ge=1,fe=3,_=(t="")=>String(t??"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,"").replace(/\s+/g," ").trim(),f=(...t)=>{for(const e of t){const o=_(e);if(o)return o}return""},be=(t="58mm")=>t==="80mm"?48:32,R=(t,e="-")=>e.repeat(t),B=(t,e=!0)=>e?`${Tt}${t}${Et}`:t,ye=(t,e=!0)=>e?`${he}${Tt}${t}${Et}${_e}`:t,$e=(t,e)=>{const o=_(t),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}`},tt=(t,e,o=!0)=>o?`${ue}${t}${me}`:$e(t,e),U=(t,e,o)=>{const n=_(t),s=_(e),i=Math.max(1,o-n.length-s.length);return`${n}${" ".repeat(i)}${s}`},lt=(t,e,o="")=>{const n=_(t);if(!n)return[];const s=Math.max(8,e-o.length),i=[],r=n.split(" ");let a="";return r.forEach(c=>{if(!a){a=c;return}if(`${a} ${c}`.length<=s){a=`${a} ${c}`;return}i.push(a),a=c}),a&&i.push(a),i.flatMap(c=>{if(c.length<=s)return[`${o}${c}`];const m=[];for(let u=0;u<c.length;u+=s)m.push(`${o}${c.slice(u,u+s)}`);return m})},W=(t={})=>t&&typeof t=="object"?f(t.table_display,t.tableDisplay,t.name,t.code,t.table_name,t.tableName):"",ve=(t={})=>{const e=[t,t.order,t.table_session,t.tableSession,t.order?.table_session,t.order?.tableSession,t.table,t.order?.table].filter(Boolean);for(const o of e){const n=f(o.table_display,o.tableDisplay,o.table_group_label,o.tableGroupLabel);if(n)return n}for(const o of e){const s=(Array.isArray(o.tables)?o.tables:[]).map(W).filter(Boolean);if(s.length)return s.join(" + ")}for(const o of e){const n=Array.isArray(o.linked_tables)?o.linked_tables:Array.isArray(o.linkedTables)?o.linkedTables:[],s=[W(o.primary_table||o.primaryTable),W(o.table),...n.map(W)].filter(Boolean);if(s.length)return[...new Set(s)].join(" + ")}for(const o of e){const n=f(o.table_name,o.tableName,o.name,o.code);if(n)return n}return""},At=(t={})=>f(t.product_name,t.name,t.product?.name,t.item_name,"Item"),wt=(t={})=>{const e=Number(t.quantity??t.qty??1);return Number.isFinite(e)&&e>0?e:1},kt=t=>Number.isInteger(t)?String(t):String(t).replace(/\.0+$/,""),xe=(t={},e)=>[f(t.variant,t.variant_name),...Array.isArray(t.modifiers)?t.modifiers.map(n=>f(n.name,n.label,n)):[],f(t.notes,t.note,t.kitchen_note,t.instructions)].filter(Boolean).flatMap(n=>lt(n,e,"  - ")),Pt=(t={})=>{const e=t.print_data||t.printData||t.batch||t,o=f(e.batch_code,e.batchCode,e.code,`KOT-${e.id||e.batch_id||""}`);return{outlet:f(e.outlet,e.store_name,e.location?.name,e.location_name),code:o,tokenNo:f(e.token_no,e.tokenNo,e.token_number,e.tokenNumber,e.token?.token_code,e.token?.token_no,e.order?.token?.token_code,e.order?.token_no,o),orderNo:f(e.order?.order_no,e.order_no,e.orderNo,e.order?.id,e.order_id),table:ve(e),status:f(e.status,"waiting"),time:f(e.sent_at,e.created_at,new Date().toISOString()),orderNotes:f(e.order?.notes,e.notes,e.table_notes),items:Array.isArray(e.items)?e.items:[]}},Ne=(t={},e,o=!0)=>{const s=`${kt(wt(t))} x`,i=" ".repeat(Math.min(7,s.length+2)),r=lt(At(t),e-i.length);return r.length?[`${B(s.padEnd(i.length-1),o)} ${r[0].trim()}`,...r.slice(1).map(a=>`${i}${a.trim()}`)]:[B(s,o)]},Ie=(t={},e={})=>{const o=e.paperSize||"58mm",n=be(o),s=e.escposCommands===!0,i=Pt(t),r=[];return i.outlet&&r.push(tt(B(i.outlet.toUpperCase(),s),n,s)),r.push(tt(B("KITCHEN ORDER TOKEN",s),n,s)),r.push(R(n)),r.push(tt(ye(`TOKEN ${i.tokenNo||i.code}`,s),n,s)),r.push(R(n)),r.push(U("KOT",i.code,n)),i.orderNo&&r.push(U("Order",i.orderNo,n)),i.table&&r.push(U("Table",i.table,n)),r.push(U("Status",i.status,n)),r.push(U("Time",i.time.replace("T"," ").slice(0,16),n)),r.push(R(n)),i.items.forEach(a=>{r.push(...Ne(a,n,s)),r.push(...xe(a,n)),r.push(...Array(ge).fill(""))}),i.orderNotes&&(r.push(R(n)),r.push(B("Notes",s)),r.push(...lt(i.orderNotes,n))),r.push(R(n)),r.push(...Array(fe).fill("")),r.join(`
`)},Se=(t={})=>{const e=Pt(t),o=e.items.map(n=>`
    <div class="item">
      <div class="qty">${_(kt(wt(n)))} x</div>
      <div class="name">${_(At(n))}</div>
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
  ${e.outlet?`<h1>${_(e.outlet).toUpperCase()}</h1>`:""}
  <h2>KITCHEN ORDER TOKEN</h2>
  <div class="line"></div>
  <div class="token">TOKEN ${_(e.tokenNo||e.code)}</div>
  <div class="line"></div>
  <p class="meta"><strong>KOT</strong><span>${_(e.code)}</span></p>
  ${e.orderNo?`<p class="meta"><strong>Order</strong><span>${_(e.orderNo)}</span></p>`:""}
  ${e.table?`<p class="meta"><strong>Table</strong><span>${_(e.table)}</span></p>`:""}
  <p class="meta"><strong>Status</strong><span>${_(e.status)}</span></p>
  <p class="meta"><strong>Time</strong><span>${_(e.time.replace("T"," ").slice(0,16))}</span></p>
  <div class="line"></div>
  <div class="items">${o}</div>
  ${e.orderNotes?`<div class="line"></div><p class="notes"><strong>Notes:</strong> ${_(e.orderNotes)}</p>`:""}
  <div class="line"></div>
</body>
</html>`},Te=(t={},e={})=>({text:Ie(t,e),html:Se(t),print_mode:e.printMode||"escpos"}),Ot="paychat_print_agent_settings",ot={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},Ee=8e3,ht=12e3,Ae=1,we=3,ke=["invoice_url","invoiceUrl","review_url","reviewUrl"],Pe=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},pt=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),Oe=t=>t==="80mm"?"80mm":"58mm",qe=t=>t==="pdf"?"pdf":"escpos",w=(t={})=>({...ot,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||ot.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:Oe(t?.paperSize),printMode:qe(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),P=()=>typeof localStorage>"u"?{...ot}:w(Pe(localStorage.getItem(Ot),{})),Ce=(t={})=>{const e=w({...P(),...t});try{localStorage.setItem(Ot,JSON.stringify(e))}catch{}return e},et=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},K=(t,e={},o={})=>{const n=w(e),s=new URL(t,`${n.agentUrl}/`),i={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(i).forEach(([r,a])=>{a!=null&&a!==""&&s.searchParams.set(r,String(a))}),s.toString()},F=async(t,e={},o=Ee)=>{const n=new AbortController,s=setTimeout(()=>n.abort(),o);try{const i=await fetch(t,{...e,signal:n.signal}),a=(i.headers.get("content-type")||"").includes("application/json")?await i.json().catch(()=>null):await i.text().catch(()=>"");if(!i.ok)throw et(a?.message||a?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return a}catch(i){throw i?.name==="AbortError"?et("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",i):i?.code?i:et("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",i)}finally{clearTimeout(s)}},Le=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},De=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),st=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(De)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const s of n){const i=st(t[s],e+1,o);if(i.length)return i}for(const s of Object.values(t)){const i=st(s,e+1,o);if(i.length)return i}return[]},Q=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},E=(...t)=>{for(const e of t){const o=pt(e).trim();if(o)return o}return""},X=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Le(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,o.items,o.order_items,o.line_items,o.cart_items,o.invoice_items,o.details,st(t))},V=(t={})=>E(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),j=(t={})=>Q(t.quantity,t.qty,t.pivot?.quantity,1)||1,Y=(t={})=>{const e=j(t),o=E(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=E(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},qt=(t={})=>{const e=E(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):Y(t)*j(t)},ze=(t=[])=>t.map(e=>({...e,product_name:V(e),name:V(e),quantity:j(e),qty:j(e),rate:Y(e),price:Y(e),total:qt(e)})),Me=(t,e)=>{const o=pt(t);if(o.length<=e)return[o];const n=[];for(let s=0;s<o.length;s+=e)n.push(o.slice(s,s+e));return n},Re=(t,e)=>{const o=e==="80mm"?48:32;return pt(t).split(/\r?\n/).flatMap(n=>Me(n,o)).join(`
`)},Ue=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=X(t);return n.length?n.map(s=>{const i=V(s),r=j(s),a=Y(s),m=qt(s).toFixed(2),u=`${r} x ${a.toFixed(2)}`,x=Math.max(1,o-u.length-m.length);return`${i}
${u}${" ".repeat(x)}${m}`}).join(`
`):""},Qe=(t,e,o)=>{const n=X(e);return!n.length||n.some(i=>{const r=V(i);return r&&t.includes(r.slice(0,Math.min(r.length,12)))})?t:`${t}
${Ue(e,o)}`},Be=(t,e)=>{if(/total/i.test(t))return t;const o=Q(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,X(e).reduce((n,s)=>{const i=Q(s.quantity,s.qty,1)||1,r=Q(s.rate,s.price,s.unit_price);return n+Q(s.total,s.line_total,s.amount,i*r)},0));return`${t}
TOTAL ${o.toFixed(2)}`},_t=t=>`\x1BE${t}\x1BE\0`,je=(t="",e={})=>{const o=E(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(n=>{const s=n.trim();return s&&(o&&s.toLowerCase()===o.toLowerCase()||/^invoice no\b/i.test(s)||/^total\b/i.test(s))?_t(n):n}).join(`
`)},Ke=(t={},e={})=>{for(const o of ke){const n=E(t[o],e[o]);if(n)return n}return E(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},Fe=(t={},e={})=>{const n=(Array.isArray(t.payments)?t.payments:[]).find(s=>String(s?.payment_method||s?.method||"").toLowerCase()==="upi"&&E(s.upi_qr_url,s.upiQrUrl,s.meta?.upi_qr_url,s.qr_payload,s.qr));return E(e.upiQr,e.paymentQr,t.upi_qr_url,t.upiQrUrl,t.payment?.upi_qr_url,t.payment?.upiQrUrl,t.payment?.meta?.upi_qr_url,t.qr?.upi_qr_url,t.receipt?.qr?.upi_qr_url,n?.upi_qr_url,n?.upiQrUrl,n?.meta?.upi_qr_url,n?.qr_payload,n?.qr)},Ge=t=>{try{const e=new URL(t);return["http:","https:","upi:"].includes(e.protocol)}catch{return E(t)!==""}},Ct=(t={},e={},o=P())=>{const n=w(o),s=n.paperSize,i={...t||{},items:ze(X(t||{}))},r=ae(i,e||{}),a={paperSize:s,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr,escposCommands:n.printMode==="escpos"&&s!=="80mm"};let c=St(r,a);const m=ce(r,{...a,agentPdf:n.printMode==="pdf"});typeof c!="string"&&(c=String(c??"")),c=Qe(c,i,s),c=Be(c,i),c=Re(c,s),n.customPrintInvoice&&s!=="80mm"&&(c=je(c,r)),c.length>ht&&(c=`${c.slice(0,ht)}
--- Receipt truncated ---`),c=c.replace(/\n*$/,`
`.repeat(n.customPrintInvoice?we:Ae));const u=Fe(t,r),x=Ke(t,r),b=u||x,g={text:c,html:m,print_mode:n.printMode};return!n.hideInvoiceQr&&b&&Ge(b)&&(g.qr={data:b,size:6,error_correction:"M"}),g},We=async(t=P())=>{const e=w(t);return F(K("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},He=async(t=P())=>{const e=w(t),o=await F(K("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},Je=async(t=P())=>{const e=w(t);return F(K("/test-print",e),{method:"POST"})},Ve=async(t={},e={})=>{const o=w(e.settings||P()),n=Ct(t,e.context||{},o);return F(K("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},Ye=async(t={},e={})=>{const o=w(e.settings||P()),n=Te(t,{paperSize:o.paperSize,printMode:o.printMode,escposCommands:o.printMode==="escpos"});return F(K("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},rn={getSettings:P,saveSettings:Ce,checkHealth:We,getPrinters:He,testPrint:Je,printReceipt:Ve,printKot:Ye,buildSafeAgentReceiptPayload:Ct},Lt="paychat_offline_released_tables",Dt="paychat:offline-table-released",Xe=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},Z=()=>Xe(localStorage.getItem(Lt),[])||[],zt=t=>{localStorage.setItem(Lt,JSON.stringify(t||[]))},$=t=>t==null||t===""?"":String(t),it=(t=[])=>Array.from(new Set(t.map($).filter(Boolean))),Ze=(t={})=>it([t.table_id,t.primary_table_id,...Array.isArray(t.linked_table_ids)?t.linked_table_ids:[],t.table_snapshot?.id,t.table_snapshot?.table_id,t.primary_table?.id,...Array.isArray(t.tables)?t.tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.linked_tables)?t.linked_tables.map(e=>e?.id||e?.table_id):[]]),an=()=>Z(),cn=(t={})=>{const e=Ze(t);if(!e.length)return null;const o=$(t.local_order_id),n={local_order_id:o,order_id:$(t.order_id||t.backend_order_id),table_session_id:$(t.table_session_id),table_ids:e,released_at:new Date().toISOString()},s=Z().filter(i=>o?$(i.local_order_id)!==o:!i.table_ids?.some(r=>e.includes($(r))));return s.push(n),zt(s),window.dispatchEvent(new CustomEvent(Dt,{detail:n})),n},ln=t=>{const e=$(t);if(!e)return;const o=Z().filter(n=>$(n.local_order_id)!==e);zt(o),window.dispatchEvent(new CustomEvent(Dt,{detail:{local_order_id:e,cleared:!0}}))},pn=(t={},e=Z())=>{const o=it([t.table_id,t.table?.id,t.__gridTable?.id,t.order?.table_id,t.order?.table?.id,tn(t)].flat()),n=$(t.order_id||t.order?.id),s=$(t.table_session_id||t.order?.table_session_id||t.order?.table_session?.id||(t.order?t.id:null));return e.some(i=>{const r=it(i.table_ids||[]);return!!(o.some(a=>r.includes(a))||n&&$(i.order_id)===n||s&&$(i.table_session_id)===s)})},tn=(t={})=>[...Array.isArray(t.tables)?t.tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.linked_tables)?t.linked_tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.order?.tables)?t.order.tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.order?.linked_tables)?t.order.linked_tables.map(e=>e?.id||e?.table_id):[]],Mt="paychat_kitchen_operation_mode",Rt="paychat_generate_inline_kitchen_token",Ut="paychat_inline_kitchen_without_status_management",z={DEDICATED_KDS:"dedicated_kds",INLINE:"inline"},Qt=Object.values(z),C=()=>typeof window>"u"?null:window.localStorage||null,gt=t=>{try{const e=C()?.getItem(t);return e?JSON.parse(e):null}catch{return null}},en=()=>{const t=gt("tenant_settings")||{},e=gt("tenant_info")||{},o=t?.kitchen?.operation_mode||t?.raw?.kitchen_operation_mode||e?.settings?.kitchen?.operation_mode||e?.settings?.raw?.kitchen_operation_mode||e?.tenant?.settings?.kitchen?.operation_mode||e?.tenant?.settings?.raw?.kitchen_operation_mode;return Qt.includes(o)?o:null},nn=()=>{const e=C()?.getItem(Mt);return Qt.includes(e)?e:en()||z.DEDICATED_KDS},dn=t=>{const e=t===z.INLINE?z.INLINE:z.DEDICATED_KDS;return C()?.setItem(Mt,e),e},mn=()=>nn()===z.INLINE,un=()=>C()?.getItem(Rt)==="true",hn=t=>{const e=!!t;return C()?.setItem(Rt,e?"true":"false"),e},_n=()=>C()?.getItem(Ut)==="true",gn=t=>{const e=!!t;return C()?.setItem(Ut,e?"true":"false"),e};export{z as K,Dt as O,ce as a,sn as b,_n as c,nn as d,un as e,pn as f,an as g,gn as h,mn as i,hn as j,ln as k,cn as m,ae as n,rn as p,dn as s};
