import{g as V,b as P}from"./index-rmGQcLNg.js";import{o as yt}from"./vendor-qKbVCTru.js";const bt="/color-paychat-logo-main.svg",vt="\x1BE",_t="\x1BE\0",$t="\x1BG",xt="\x1BG\0",tt={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},wt=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},rt=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},St=()=>wt(rt("tenant_info"),{}),it=t=>I(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),at=t=>it(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),ct=t=>{const e=it(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},a=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),T=t=>a(t).replace(/`/g,"&#096;"),I=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),u=t=>Number(t||0).toFixed(2),R=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},lt=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},pt=(t="80mm")=>tt[t]||tt["80mm"],d=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},k=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},Nt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},kt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),K=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(kt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const r=K(t[s],e+1,o);if(r.length)return r}for(const s of Object.values(t)){const r=K(s,e+1,o);if(r.length)return r}return[]},Tt=(t={})=>d(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),Y=(t={})=>k(t.quantity,t.qty,t.pivot?.quantity,1)||1,dt=(t={})=>{const e=Y(t),o=d(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=d(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},It=(t={})=>{const e=d(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):dt(t)*Y(t)},At=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Nt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,K(t))},Pt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return d(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},et=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=d(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},Lt=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},Et=t=>{if(!t)return"";const e=Lt(t),o=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const s=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${T(s)}" alt="Invoice QR" />`}const n=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return n?.[1]?`<img class="qr-image" src="${T(n[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${T(e)}" alt="Invoice QR" />`:`<div class="qr-url">${a(e)}</div>`},Ct=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=St(),n=V(),s=o?.tenant||{},r=o?.branding||{},i=t.location||{},l=At(t).map(f=>({name:Tt(f),qty:Y(f),rate:dt(f),total:It(f)})),c=k(t.subtotal,t.totals?.subtotal,l.reduce((f,y)=>f+y.rate*y.qty,0)),m=k(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),p=k(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),g=k(t.total,t.grand_total,t.totals?.grand_total,c+p-m);return{shopName:d(e.shopName,i.tenant?.name,t.tenant?.name,r.company_name,s.name,rt("tenant_slug"),"PayChat POS"),shopPhone:d(e.shopPhone,i.phone,r.phone,s.phone),shopAddress:d(e.shopAddress,i.address,r.address,s.address),shopLogoUrl:d(e.shopLogoUrl,i.logo,i.tenant?.logo,t.tenant?.logo,r.logo,s.logo),locationName:d(i.name,t.location_name),paychatLogoUrl:d(e.paychatLogoUrl,t.paychat_logo_url,bt),invoiceNo:d(e.invoiceNo,t.invoice_no,t.invoiceNo,t.invoice?.number,t.invoice?.invoice_no,t.invoice?.offline_invoice_number,t.offline_invoice_number,t.local_invoice_no),orderNo:d(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:d(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:d(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:d(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:d(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:d(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:et(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:et(t.batch_codes,t.batchCodes),items:l,subtotal:c,discount:m,tax:p,grandTotal:g,paidAmount:k(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,g),paymentMethod:Pt(t),invoiceUrl:d(e.invoiceUrl,t.invoice_url,t.invoiceUrl,t.invoice?.url),invoiceQr:d(e.invoiceQr,t.invoice_qr,t.invoiceQr,t.qr),reviewQr:d(e.reviewQr,t.review_qr,t.reviewQr),notes:d(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},qt=(t,e={})=>{const o=e.paperSize||"80mm",n=pt(o),s=o==="58mm",r=e.agentPdf===!0,i=e.customPrintInvoice===!0,l=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],p=Array.isArray(t.batchCodes)?t.batchCodes:[],g=V(),y=!(t.simpleBilling??g.simpleBilling),C=i?at(t.shopName):t.shopName,A=i?ct(t.shopAddress):t.shopAddress,x=d(t.invoiceNo,t.orderNo),q=i?s?"48px":"64px":n.paychatLogoWidth,w=l?"":Et(t.invoiceQr||t.reviewQr),O=t.invoiceUrl&&(l||!w)?`<div class="qr-url">${a(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
      max-width: ${q};
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
    ${i&&t.tokenNo?`<div class="top-token">TOKEN ${a(t.tokenNo)}</div>`:""}
    <div class="center">
      ${!i&&t.shopLogoUrl?`<img class="shop-logo" src="${T(t.shopLogoUrl)}" alt="${T(C)}" />`:""}
      <div class="title">${a(C)}</div>
      ${!i&&t.locationName?`<div class="muted">${a(t.locationName)}</div>`:""}
      ${A?`<div class="muted">${a(A)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${a(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${x&&!i?`<div class="bill-no">INVOICE NO: ${a(x)}</div>`:""}
    <table>
	      ${x&&i?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${a(x)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${a(lt(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${a(t.orderType)}</td></tr>`:""}
	      ${y&&t.tableName?`<tr><td>Table</td><td class="right">${a(t.tableName)}</td></tr>`:""}
	      ${y&&t.guestCount&&!i?`<tr><td>Guests</td><td class="right">${a(t.guestCount)}</td></tr>`:""}
	      ${y&&t.tokenNo&&!i?`<tr><td>Token</td><td class="right">${a(t.tokenNo)}</td></tr>`:""}
	      ${y&&m.length?`<tr><td>KOT</td><td class="right">${a(m.join(", "))}</td></tr>`:""}
	      ${y&&p.length?`<tr><td>Batch</td><td class="right">${a(p.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${s?`
      <div>
        ${c.length?c.map(b=>`
          <div class="item-block">
            <div class="item-name">${a(b.name)}</div>
            <div class="item-meta">
              <span>${a(R(b.qty))} x ${a(u(b.rate))}</span>
              <strong>${a(u(b.total))}</strong>
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
          ${c.length?c.map(b=>`
            <tr>
              <td class="item-name">${a(b.name)}</td>
              <td class="right">${a(R(b.qty))}</td>
              <td class="right">${a(u(b.rate))}</td>
              <td class="right">${a(u(b.total))}</td>
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
    ${w||O?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!l&&w?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${w||O}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!i?`<img class="paychat-logo" src="${T(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},v=(t,e="-")=>`${e.repeat(t)}
`,ut=(t="")=>`${vt}${$t}${t}${xt}${_t}`,Ot=(t="")=>ut(t),_=(t,e)=>{const o=I(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},h=(t,e,o)=>{const n=I(e),s=Math.max(1,o-n.length-1),r=I(t).slice(0,s),i=Math.max(1,o-r.length-n.length);return`${r}${" ".repeat(i)}${n}
`},X=(t,e)=>{const o=I(t).split(/\s+/).filter(Boolean).flatMap(r=>r.length<=e?[r]:r.match(new RegExp(`.{1,${e}}`,"g"))||[r]),n=[];let s="";return o.forEach(r=>{if(!s){s=r;return}(s+" "+r).length<=e?s+=` ${r}`:(n.push(s),s=r.slice(0,e))}),s&&n.push(s),n.length?n:[""]},zt=(t,e)=>{const o=X(t.name,e),n=`${R(t.qty)} x ${u(t.rate)}`;return[...o.map(s=>`${s}
`),h(n,u(t.total),e)].join("")},Ut=(t,e)=>{const r=e-5-9-10,i=X(t.name,r),l=`${i[0].padEnd(r)}${R(t.qty).padStart(5)}${u(t.rate).padStart(9)}${u(t.total).padStart(10)}
`,c=i.slice(1).map(m=>`${m}
`).join("");return l+c},mt=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=pt(o),s=o==="58mm",r=e.customPrintInvoice===!0,i=e.hideInvoiceQr===!0,l=e.escposCommands===!0,c=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],p=Array.isArray(t.batchCodes)?t.batchCodes:[],g=V(),f=t.simpleBilling??g.simpleBilling,y=s?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,C=r?at(t.shopName):t.shopName,A=r?ct(t.shopAddress):t.shopAddress,x=d(t.invoiceNo,t.orderNo),q=_(C,n),w=x?h("Invoice No",x,n):"",O=r&&t.tokenNo?`${v(n)}${_(`TOKEN ${t.tokenNo}`,n)}${v(n)}`:"",b=t.invoiceUrl?`${v(n)}${_(i?"Invoice link":"Invoice/review link",n)}${X(t.invoiceUrl,n).map(z=>`${I(z)}
`).join("")}`:"";return[O,l?Ot(q):q,!r&&t.locationName?_(t.locationName,n):"",t.shopPhone?_(`Phone: ${t.shopPhone}`,n):"",A?_(A,n):"",v(n),l?ut(w):w,h("Date",lt(t.dateTime),n),t.orderType?h("Type",t.orderType,n):"",!f&&t.tableName?h("Table",t.tableName,n):"",!f&&t.guestCount&&!r?h("Guests",t.guestCount,n):"",!f&&t.tokenNo&&!r?h("Token",t.tokenNo,n):"",!f&&m.length?h("KOT",m.join(","),n):"",!f&&p.length?h("Batch",p.join(","),n):"",v(n),y,y?v(n):"",c.length?c.map(z=>s?zt(z,n):Ut(z,n)).join(""):_("No items",n),v(n),r?"":h("Subtotal",u(t.subtotal),n),!r&&t.discount?h("Discount",`-${u(t.discount)}`,n):"",!r&&t.tax?h("Tax/GST",u(t.tax),n):"",r?"":v(n),h("TOTAL",u(t.grandTotal),n),t.paidAmount&&!r?h("Paid",u(t.paidAmount),n):"",t.paymentMethod?h("Payment",t.paymentMethod,n):"",b,v(n),_("Thank you",n),_("Powered by PayChat",n)].join("")},le=mt,ht="paychat_print_agent_settings",H={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},Rt=8e3,nt=12e3,Mt=["invoice_url","invoiceUrl","review_url","reviewUrl"],Bt=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},Z=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),jt=t=>t==="80mm"?"80mm":"58mm",Qt=t=>t==="pdf"?"pdf":"escpos",$=(t={})=>({...H,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||H.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:jt(t?.paperSize),printMode:Qt(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),N=()=>typeof localStorage>"u"?{...H}:$(Bt(localStorage.getItem(ht),{})),Wt=(t={})=>{const e=$({...N(),...t});try{localStorage.setItem(ht,JSON.stringify(e))}catch{}return e},G=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},Q=(t,e={},o={})=>{const n=$(e),s=new URL(t,`${n.agentUrl}/`),r={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(r).forEach(([i,l])=>{l!=null&&l!==""&&s.searchParams.set(i,String(l))}),s.toString()},W=async(t,e={},o=Rt)=>{const n=new AbortController,s=setTimeout(()=>n.abort(),o);try{const r=await fetch(t,{...e,signal:n.signal}),l=(r.headers.get("content-type")||"").includes("application/json")?await r.json().catch(()=>null):await r.text().catch(()=>"");if(!r.ok)throw G(l?.message||l?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return l}catch(r){throw r?.name==="AbortError"?G("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",r):r?.code?r:G("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",r)}finally{clearTimeout(s)}},Dt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Gt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),J=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Gt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const s of n){const r=J(t[s],e+1,o);if(r.length)return r}for(const s of Object.values(t)){const r=J(s,e+1,o);if(r.length)return r}return[]},L=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},S=(...t)=>{for(const e of t){const o=Z(e).trim();if(o)return o}return""},D=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Dt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,J(t))},M=(t={})=>S(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),E=(t={})=>L(t.quantity,t.qty,t.pivot?.quantity,1)||1,B=(t={})=>{const e=E(t),o=S(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=S(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},gt=(t={})=>{const e=S(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):B(t)*E(t)},Ft=(t=[])=>t.map(e=>({...e,product_name:M(e),name:M(e),quantity:E(e),qty:E(e),rate:B(e),price:B(e),total:gt(e)})),Kt=(t,e)=>{const o=Z(t);if(o.length<=e)return[o];const n=[];for(let s=0;s<o.length;s+=e)n.push(o.slice(s,s+e));return n},Ht=(t,e)=>{const o=e==="80mm"?48:32;return Z(t).split(/\r?\n/).flatMap(n=>Kt(n,o)).join(`
`)},Jt=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=D(t);return n.length?n.map(s=>{const r=M(s),i=E(s),l=B(s),m=gt(s).toFixed(2),p=`${i} x ${l.toFixed(2)}`,g=Math.max(1,o-p.length-m.length);return`${r}
${p}${" ".repeat(g)}${m}`}).join(`
`):""},Vt=(t,e,o)=>{const n=D(e);return!n.length||n.some(r=>{const i=M(r);return i&&t.includes(i.slice(0,Math.min(i.length,12)))})?t:`${t}
${Jt(e,o)}`},Yt=(t,e)=>{if(/total/i.test(t))return t;const o=L(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,D(e).reduce((n,s)=>{const r=L(s.quantity,s.qty,1)||1,i=L(s.rate,s.price,s.unit_price);return n+L(s.total,s.line_total,s.amount,r*i)},0));return`${t}
TOTAL ${o.toFixed(2)}`},ot=t=>`\x1BE${t}\x1BE\0`,Xt=(t="",e={})=>{const o=S(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(n=>{const s=n.trim();return s&&(o&&s.toLowerCase()===o.toLowerCase()||/^invoice no\b/i.test(s)||/^total\b/i.test(s))?ot(n):n}).join(`
`)},Zt=(t={},e={})=>{for(const o of Mt){const n=S(t[o],e[o]);if(n)return n}return S(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},te=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},ft=(t={},e={},o=N())=>{const n=$(o),s=n.paperSize,r={...t||{},items:Ft(D(t||{}))},i=Ct(r,e||{}),l={paperSize:s,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr,escposCommands:n.printMode==="escpos"};let c=mt(i,l);const m=qt(i,{...l,agentPdf:n.printMode==="pdf"});typeof c!="string"&&(c=String(c??"")),c=Vt(c,r,s),c=Yt(c,r),c=Ht(c,s),n.customPrintInvoice&&(c=Xt(c,i)),c.length>nt&&(c=`${c.slice(0,nt)}
--- Receipt truncated ---`),c=c.replace(/\n*$/,`


`);const p=Zt(t,i),g={text:c,html:m,print_mode:n.printMode};return!n.hideInvoiceQr&&p&&te(p)&&(g.qr={data:p,size:6,error_correction:"M"}),g},ee=async(t=N())=>{const e=$(t);return W(Q("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},ne=async(t=N())=>{const e=$(t),o=await W(Q("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},oe=async(t=N())=>{const e=$(t);return W(Q("/test-print",e),{method:"POST"})},se=async(t={},e={})=>{const o=$(e.settings||N()),n=ft(t,e.context||{},o);return W(Q("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},pe={getSettings:N,saveSettings:Wt,checkHealth:ee,getPrinters:ne,testPrint:oe,printReceipt:se,buildSafeAgentReceiptPayload:ft},de={list(t={}){return P.get("/upi-profiles",{params:t})},create(t){return P.post("/upi-profiles",t)},update(t,e){return P.patch(`/upi-profiles/${t}`,e)},deactivate(t){return P.delete(`/upi-profiles/${t}`)},setDefault(t){return P.patch(`/upi-profiles/${t}/default`)}},j="paychat_pos_wake_lock_enabled",st=()=>{try{return localStorage.getItem(j)==="true"}catch{return!1}},ue=t=>{try{return t?(localStorage.setItem(j,"true"),!0):(localStorage.removeItem(j),!1)}catch{return!1}},re=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},me=()=>{let t=null,e=!1,o=!1,n=0;const s=async()=>{try{await t?.release?.()}catch(p){console.warn("POS wake lock release failed:",p)}finally{t=null}},r=()=>{const p=re();return p.supported?!0:(o||(console.warn(`POS wake lock unavailable: ${p.reason}`),o=!0),!1)},i=async()=>{const p=Date.now();if(!(e||t||!st()||!r()||document.visibilityState!=="visible")&&!(p-n<750)){n=p;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(g){console.warn("POS wake lock failed:",g)}}},l=()=>{i()},c=()=>{document.visibilityState==="visible"?i():s()},m=p=>{p.key===j&&(st()?i():s())};return document.addEventListener("visibilitychange",c),document.addEventListener("pointerdown",l,{passive:!0}),document.addEventListener("touchstart",l,{passive:!0}),document.addEventListener("click",l,{passive:!0}),window.addEventListener("storage",m),i(),()=>{e=!0,document.removeEventListener("visibilitychange",c),document.removeEventListener("pointerdown",l),document.removeEventListener("touchstart",l),document.removeEventListener("click",l),window.removeEventListener("storage",m),s()}},ie="paychat-pos",U="cache",F=yt(ie,1,{upgrade(t){t.createObjectStore(U)}}),he={async set(t,e){await(await F).put(U,e,t)},async get(t){return await(await F).get(U,t)},async clear(){await(await F).clear(U)}};export{qt as a,le as b,he as c,ue as d,re as e,st as g,Ct as n,pe as p,me as s,de as u};
