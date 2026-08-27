import{g as yt}from"./index-Df4AQCUb.js";import{c as Ut,t as ye}from"./usePosInteractionFeedback-CdJ6jujS.js";import{a as z,c as be}from"./registration-batches-DjBTYa_U.js";import{p as Se}from"./registration-shell-H1t-sKek.js";import{l as Ie}from"./locationService-BSFQ3mNH.js";import{d as Ne}from"./vendor-vue-DNBbdLq8.js";import{p as $e}from"./productService-B60jeO_v.js";import{D as ve}from"./vendor-qKbVCTru.js";const we="/color-paychat-logo-main.svg",xe="\x1BE",Ee="\x1BE\0",Ae="\x1BG",Oe="\x1BG\0",Te=1,ke=3,vt={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},zt=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},bt=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},Ce=()=>zt(bt("tenant_info"),{}),Pe=()=>zt(bt("selected_location"),{}),Ft=t=>B(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),Qt=t=>Ft(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),Bt=t=>{const e=Ft(t);if(!e)return"";const n=e.split(",").map(s=>s.trim()).filter(Boolean);return(n.length?n.slice(0,2).join(", "):e).slice(0,80)},l=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),O=t=>l(t).replace(/`/g,"&#096;"),B=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),u=t=>Number(t||0).toFixed(2),st=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},Kt=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},jt=(t="80mm")=>vt[t]||vt["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},qe=t=>{const e=String(t||"").trim();if(!e)return"";try{const n=typeof window<"u"?window.location.origin:"https://paychat.local",a=new URL(e,n).pathname.split("/").map(i=>i.trim()).filter(Boolean),r=a.findIndex(i=>["invoice","invoices"].includes(i.toLowerCase())),o=r>=0?a[r+1]:a[a.length-1];return decodeURIComponent(o||"").trim()}catch{const a=e.split("?")[0].split("#")[0].split("/").map(r=>r.trim()).filter(Boolean);return a[a.length-1]||""}},F=(...t)=>{for(const e of t){if(e==null||e==="")continue;const n=Number(e);if(Number.isFinite(n))return n}return 0},De=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Le=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),ut=(t,e=0,n=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Le)?t:[];if(typeof t!="object"||n.has(t))return[];n.add(t);const s=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const a of s){const r=ut(t[a],e+1,n);if(r.length)return r}for(const a of Object.values(t)){const r=ut(a,e+1,n);if(r.length)return r}return[]},Me=(t={})=>p(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),St=(t={})=>F(t.quantity,t.qty,t.pivot?.quantity,1)||1,Gt=(t={})=>{const e=St(t),n=p(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(n!=="")return Number(n||0);const s=p(t.total,t.line_total,t.amount,t.subtotal);return Number(s||0)/e},Re=(t={})=>{const e=p(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):Gt(t)*St(t)},Ue=(t={})=>{const e=t.invoice||t.invoice_data||{},n=t.data||t.order||{};return De(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,n.items,n.order_items,n.line_items,n.cart_items,n.invoice_items,n.details,ut(t))},ze=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},wt=(...t)=>{const e=[];return t.flat().forEach(n=>{if(!n)return;if(typeof n=="string"||typeof n=="number"){e.push(String(n));return}const s=p(n.code,n.kot_code,n.batch_code,n.token_code,n.id);s&&e.push(String(s))}),[...new Set(e)]},Fe=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const n=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(n)&&(e=n)}catch{}return e.trim()},xt=(t,e="Invoice QR")=>{if(!t)return"";const n=Fe(t),s=n.match(/<svg\b[\s\S]*?<\/svg>/i);if(s){const r=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(s[0])}`;return`<img class="qr-image" src="${O(r)}" alt="${O(e)}" />`}const a=n.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return a?.[1]?`<img class="qr-image" src="${O(a[1])}" alt="${O(e)}" />`:/^(data:image\/|https?:\/\/|\/)/i.test(n)?`<img class="qr-image" src="${O(n)}" alt="${O(e)}" />`:`<div class="qr-url">${l(n)}</div>`},Qe=(t={})=>{const n=(Array.isArray(t.payments)?t.payments:[]).find(s=>String(s?.payment_method||s?.method||"").toLowerCase()==="upi"&&p(s.upi_qr_url,s.upiQrUrl,s.upi_qr_string,s.upi_payment_link,s.meta?.upi_qr_url,s.meta?.upi_qr_string,s.meta?.upi_payment_link,s.qr_payload,s.qr));return p(t.upi_qr_url,t.upiQrUrl,t.upi_qr_string,t.upi_payment_link,t.payment?.upi_qr_url,t.payment?.upiQrUrl,t.payment?.upi_qr_string,t.payment?.upi_payment_link,t.payment?.meta?.upi_qr_url,t.payment?.meta?.upi_qr_string,t.payment?.meta?.upi_payment_link,t.qr?.upi_qr_url,t.qr?.upi_qr_string,t.qr?.upi_payment_link,t.receipt?.qr?.upi_qr_url,t.receipt?.qr?.upi_qr_string,t.receipt?.qr?.upi_payment_link,n?.upi_qr_url,n?.upiQrUrl,n?.upi_qr_string,n?.upi_payment_link,n?.meta?.upi_qr_url,n?.meta?.upi_qr_string,n?.meta?.upi_payment_link,n?.qr_payload,n?.qr)},Be=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const n=Ce(),s=yt(),a=n?.tenant||{},r=n?.branding||a?.branding||n?.branching||{},o=Pe(),i={...o&&typeof o=="object"?o:{},...t.location&&typeof t.location=="object"?t.location:{}};t.branch||t.branching||t.branding||n?.branch||n?.branching;const c=t.merchant||t.receipt?.merchant||{},d=t.invoice||t.invoice_data||t.receipt?.invoice||{},m=t.qr||t.receipt?.qr||{},$=p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,d.url,t.meta?.invoice?.url,m.invoice_url),b=p(e.upiQr,e.paymentQr,Qe(t)),h=Ue(t).map(S=>({name:Me(S),qty:St(S),rate:Gt(S),total:Re(S)})),L=F(t.subtotal,t.totals?.subtotal,h.reduce((S,w)=>S+w.rate*w.qty,0)),C=F(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),v=F(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),M=F(t.total,t.grand_total,t.totals?.grand_total,L+v-C);return{shopName:p(e.shopName,c.name,i.tenant?.name,t.tenant?.name,r.company_name,a.name,bt("tenant_slug"),"PayChat POS"),shopPhone:p(e.shopPhone,c.phone,i.phone,r.phone,a.phone),shopAddress:p(e.shopAddress,r.address,a.branding?.address,t.tenant?.branding?.address,i.tenant?.branding?.address),shopLogoUrl:p(e.shopLogoUrl,i.logo,i.tenant?.logo,t.tenant?.logo,r.logo,a.logo),locationName:p(i.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,we),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,d.number,d.invoice_no,d.invoiceNo,d.invoice_number,d.offline_invoice_number,t.meta?.invoice?.number,t.meta?.invoice?.invoice_no,t.meta?.invoice?.invoiceNo,t.meta?.invoice?.invoice_number,t.offline_invoice_number,t.local_invoice_no,qe($)),orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table_display,t.tableDisplay,t.table_session?.table_display,t.tableSession?.tableDisplay,t.table_session?.table?.name,t.tableSession?.table?.name,t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:wt(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:wt(t.batch_codes,t.batchCodes),items:h,subtotal:L,discount:C,tax:v,grandTotal:M,paidAmount:F(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,M),paymentMethod:ze(t),invoiceUrl:$,upiQr:b,invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,m.qr_svg_or_url,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:s.simpleBilling,billingLabel:s.billingLabel}},Ke=(t,e={})=>{const n=e.paperSize||"80mm",s=jt(n),a=n==="58mm",r=e.agentPdf===!0,o=e.customPrintInvoice===!0,i=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],d=Array.isArray(t.kotCodes)?t.kotCodes:[],m=Array.isArray(t.batchCodes)?t.batchCodes:[],$=yt(),h=!(t.simpleBilling??$.simpleBilling),L=o?Qt(t.shopName):t.shopName,C=o?Bt(t.shopAddress):t.shopAddress,v=p(t.invoiceNo),M=o?a?"48px":"64px":s.paychatLogoWidth,S=!i&&t.upiQr?xt(t.upiQr,"UPI payment QR"):"",w=!i&&!S?xt(t.invoiceQr||t.reviewQr):"",Z=!t.upiQr&&t.invoiceUrl&&(i||!w)?`<div class="qr-url">${l(t.invoiceUrl)}</div>`:"";return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Thermal Bill</title>
  <style>
    @page { size: ${s.width} auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0 0 ${o?"18px":"0"};
      background: #fff;
      color: #000;
      font-family: "Courier New", monospace;
      font-size: ${s.fontSize};
      line-height: ${o?"1.08":"1.28"};
    }
    .receipt {
      width: ${s.width};
      padding: ${o?"2px 4px 14px":s.padding};
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { font-size: 0.88em; }
    .powered { font-size: ${o?"0.72em":"0.88em"}; }
    .title {
      color: #000;
      font-size: ${o?a?"15px":"18px":s.titleSize};
      font-weight: ${o?"900":"800"};
      text-transform: ${o?"none":"uppercase"};
      ${o?"text-shadow: 0 0 0 #000, 0.25px 0 #000, -0.25px 0 #000; -webkit-text-stroke: 0.25px #000;":""}
      word-break: break-word;
    }
    .shop-logo {
      display: block;
      max-width: ${s.logoMaxWidth};
      max-height: ${a?"54px":"74px"};
      object-fit: contain;
      margin: 0 auto 4px;
    }
    .paychat-logo {
      display: block;
      max-width: ${M};
      max-height: ${a?"20px":"26px"};
      object-fit: contain;
      margin: 2px auto 1px;
    }
    .bill-no {
      font-size: 1.15em;
      font-weight: ${o?"900":"700"};
      text-align: center;
      margin: ${o?"1px 0":"3px 0"};
      word-break: break-word;
    }
    .bill-no-row td {
      color: #000;
      font-weight: 900;
      padding-top: 0;
    }
    .line {
      border-top: 1px dashed #000;
      margin: ${o?"2px 0":"6px 0"};
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    td, th {
      padding: ${o?"1px 0":"2px 0"};
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
      padding: ${o?"1px 0":"3px 0"};
      border-bottom: 1px dotted #999;
    }
    .item-meta,
    .total-row {
      display: flex;
      justify-content: space-between;
      gap: 6px;
    }
    .grand {
      border-top: ${o?"2px solid #000":"1px dashed #000"};
      color: #000;
      padding-top: ${o?"3px":"5px"};
      margin-top: ${o?"2px":"4px"};
      font-weight: 900;
      font-size: ${o?"1.22em":"1.12em"};
      ${o?"text-shadow: 0.25px 0 #000, -0.25px 0 #000;":""}
    }
    .top-token {
      border-bottom: 1px dashed #000;
      font-size: ${a?"1.55em":"1.75em"};
      font-weight: 900;
      margin-bottom: ${o?"3px":"6px"};
      padding-bottom: ${o?"3px":"6px"};
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
      width: ${s.qrSize};
      height: ${s.qrSize};
      max-width: ${s.qrSize};
      max-height: ${s.qrSize};
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
    ${o&&t.tokenNo?`<div class="top-token">TOKEN ${l(t.tokenNo)}</div>`:""}
    <div class="center">
      ${!o&&t.shopLogoUrl?`<img class="shop-logo" src="${O(t.shopLogoUrl)}" alt="${O(L)}" />`:""}
      <div class="title">${l(L)}</div>
      ${!o&&t.locationName?`<div class="muted">${l(t.locationName)}</div>`:""}
      ${C?`<div class="muted">${l(C)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${l(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${v&&!o?`<div class="bill-no">INVOICE NO: ${l(v)}</div>`:""}
    <table>
	      ${v&&o?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${l(v)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${l(Kt(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${l(t.orderType)}</td></tr>`:""}
	      ${(h||o)&&t.tableName?`<tr><td>Table</td><td class="right">${l(t.tableName)}</td></tr>`:""}
	      ${h&&t.guestCount&&!o?`<tr><td>Guests</td><td class="right">${l(t.guestCount)}</td></tr>`:""}
	      ${h&&t.tokenNo&&!o?`<tr><td>Token</td><td class="right">${l(t.tokenNo)}</td></tr>`:""}
	      ${h&&d.length?`<tr><td>KOT</td><td class="right">${l(d.join(", "))}</td></tr>`:""}
	      ${h&&m.length?`<tr><td>Batch</td><td class="right">${l(m.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${a?`
      <div>
        ${c.length?c.map(N=>`
          <div class="item-block">
            <div class="item-name">${l(N.name)}</div>
            <div class="item-meta">
              <span>${l(st(N.qty))} x ${l(u(N.rate))}</span>
              <strong>${l(u(N.total))}</strong>
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
          ${c.length?c.map(N=>`
            <tr>
              <td class="item-name">${l(N.name)}</td>
              <td class="right">${l(st(N.qty))}</td>
              <td class="right">${l(u(N.rate))}</td>
              <td class="right">${l(u(N.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${o?`
      <div class="total-row grand"><span>TOTAL</span><span>${l(u(t.grandTotal))}</span></div>
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${l(t.paymentMethod)}</span></div>`:""}
    `:r?`
      <table class="pdf-totals">
        <tbody>
          <tr><td>Subtotal</td><td class="pdf-total-value">${l(u(t.subtotal))}</td></tr>
          ${t.discount?`<tr><td>Discount</td><td class="pdf-total-value">-${l(u(t.discount))}</td></tr>`:""}
          ${t.tax?`<tr><td>Tax/GST</td><td class="pdf-total-value">${l(u(t.tax))}</td></tr>`:""}
          <tr class="grand"><td>TOTAL</td><td class="pdf-total-value">${l(u(t.grandTotal))}</td></tr>
          ${t.paidAmount?`<tr><td>Paid</td><td class="pdf-total-value">${l(u(t.paidAmount))}</td></tr>`:""}
          ${t.paymentMethod?`<tr><td>Payment</td><td class="pdf-total-value">${l(t.paymentMethod)}</td></tr>`:""}
        </tbody>
      </table>
    `:`
      <div class="total-row"><span>Subtotal</span><span>${l(u(t.subtotal))}</span></div>
      ${t.discount?`<div class="total-row"><span>Discount</span><span>-${l(u(t.discount))}</span></div>`:""}
      ${t.tax?`<div class="total-row"><span>Tax/GST</span><span>${l(u(t.tax))}</span></div>`:""}
      <div class="total-row grand"><span>TOTAL</span><span>${l(u(t.grandTotal))}</span></div>
      ${t.paidAmount?`<div class="total-row"><span>Paid</span><span>${l(u(t.paidAmount))}</span></div>`:""}
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${l(t.paymentMethod)}</span></div>`:""}
    `}
    ${w||Z?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!i&&w?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${w||Z}
      </div>
    `:""}
    ${S?`
      <div class="line"></div>
      <div class="qr-wrap">
        <div class="muted">Scan QR to pay via UPI</div>
        ${S}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!o?`<img class="paychat-logo" src="${O(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},x=(t,e="-")=>`${e.repeat(t)}
`,Yt=(t="")=>`${xe}${Ae}${t}${Oe}${Ee}`,je=(t="")=>Yt(t),E=(t,e)=>{const n=B(t).slice(0,e),s=Math.max(0,Math.floor((e-n.length)/2));return`${" ".repeat(s)}${n}
`},g=(t,e,n)=>{const s=B(e),a=Math.max(1,n-s.length-1),r=B(t).slice(0,a),o=Math.max(1,n-r.length-s.length);return`${r}${" ".repeat(o)}${s}
`},at=(t,e)=>{const n=B(t).split(/\s+/).filter(Boolean).flatMap(r=>r.length<=e?[r]:r.match(new RegExp(`.{1,${e}}`,"g"))||[r]),s=[];let a="";return n.forEach(r=>{if(!a){a=r;return}(a+" "+r).length<=e?a+=` ${r}`:(s.push(a),a=r.slice(0,e))}),a&&s.push(a),s.length?s:[""]},Ge=(t,e)=>{const n=at(t.name,e),s=`${st(t.qty)} x ${u(t.rate)}`;return[...n.map(a=>`${a}
`),g(s,u(t.total),e)].join("")},Ye=(t,e)=>{const r=e-5-9-10,o=at(t.name,r),i=`${o[0].padEnd(r)}${st(t.qty).padStart(5)}${u(t.rate).padStart(9)}${u(t.total).padStart(10)}
`,c=o.slice(1).map(d=>`${d}
`).join("");return i+c},Wt=(t,e={})=>{const n=e.paperSize||"80mm",{columns:s}=jt(n),a=n==="58mm",r=e.customPrintInvoice===!0,o=e.hideInvoiceQr===!0,i=e.escposCommands===!0,c=Array.isArray(t.items)?t.items:[],d=Array.isArray(t.kotCodes)?t.kotCodes:[],m=Array.isArray(t.batchCodes)?t.batchCodes:[],$=yt(),b=t.simpleBilling??$.simpleBilling,h=a?"":`${"Item".padEnd(s-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,L=r?Qt(t.shopName):t.shopName,C=r?Bt(t.shopAddress):t.shopAddress,v=p(t.invoiceNo),M=E(L,s),S=C?at(C,s).map(U=>E(U,s)).join(""):"",w=v?g("Invoice No",v,s):"",Z=r&&t.tokenNo?`${x(s)}${E(`TOKEN ${t.tokenNo}`,s)}${x(s)}`:"",N=!t.upiQr&&t.invoiceUrl?`${x(s)}${E(o?"Invoice link":"Invoice/review link",s)}${at(t.invoiceUrl,s).map(U=>`${B(U)}
`).join("")}`:"",he=t.upiQr&&!o?`${x(s)}${E("Scan QR to pay via UPI",s)}`:"";return[Z,i?je(M):M,!r&&t.locationName?E(t.locationName,s):"",S,t.shopPhone?E(`Phone: ${t.shopPhone}`,s):"",x(s),i?Yt(w):w,g("Date",Kt(t.dateTime),s),t.orderType?g("Type",t.orderType,s):"",(!b||r)&&t.tableName?g("Table",t.tableName,s):"",!b&&t.guestCount&&!r?g("Guests",t.guestCount,s):"",!b&&t.tokenNo&&!r?g("Token",t.tokenNo,s):"",!b&&d.length?g("KOT",d.join(","),s):"",!b&&m.length?g("Batch",m.join(","),s):"",x(s),h,h?x(s):"",c.length?c.map(U=>a?Ge(U,s):Ye(U,s)).join(""):E("No items",s),x(s),r?"":g("Subtotal",u(t.subtotal),s),!r&&t.discount?g("Discount",`-${u(t.discount)}`,s):"",!r&&t.tax?g("Tax/GST",u(t.tax),s):"",r?"":x(s),g("TOTAL",u(t.grandTotal),s),t.paidAmount&&!r?g("Paid",u(t.paidAmount),s):"",t.paymentMethod?g("Payment",t.paymentMethod,s):"",he,N,x(s),E("Thank you",s),E("Powered by PayChat",s),...Array(r?ke:Te).fill(`
`)].join("")},bs=Wt,Jt="\x1BE",Ht="\x1BE\0",We="\x1Ba\0",Je="\x1Ba",He="!",Ve="!\0",Xe=1,Ze=3,f=(t="")=>String(t??"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,"").replace(/\s+/g," ").trim(),y=(...t)=>{for(const e of t){const n=f(e);if(n)return n}return""},tn=(t="58mm")=>t==="80mm"?48:32,K=(t,e="-")=>e.repeat(t),W=(t,e=!0)=>e?`${Jt}${t}${Ht}`:t,en=(t,e=!0)=>e?`${He}${Jt}${t}${Ht}${Ve}`:t,nn=(t,e)=>{const n=f(t),s=Math.max(0,Math.floor((e-n.length)/2));return`${" ".repeat(s)}${n}`},dt=(t,e,n=!0)=>n?`${Je}${t}${We}`:nn(t,e),j=(t,e,n)=>{const s=f(t),a=f(e),r=Math.max(1,n-s.length-a.length);return`${s}${" ".repeat(r)}${a}`},It=(t,e,n="")=>{const s=f(t);if(!s)return[];const a=Math.max(8,e-n.length),r=[],o=s.split(" ");let i="";return o.forEach(c=>{if(!i){i=c;return}if(`${i} ${c}`.length<=a){i=`${i} ${c}`;return}r.push(i),i=c}),i&&r.push(i),r.flatMap(c=>{if(c.length<=a)return[`${n}${c}`];const d=[];for(let m=0;m<c.length;m+=a)d.push(`${n}${c.slice(m,m+a)}`);return d})},tt=(t={})=>t&&typeof t=="object"?y(t.table_display,t.tableDisplay,t.name,t.code,t.table_name,t.tableName):"",sn=(t={})=>{const e=[t,t.order,t.table_session,t.tableSession,t.order?.table_session,t.order?.tableSession,t.table,t.order?.table].filter(Boolean);for(const n of e){const s=y(n.table_display,n.tableDisplay,n.table_group_label,n.tableGroupLabel);if(s)return s}for(const n of e){const a=(Array.isArray(n.tables)?n.tables:[]).map(tt).filter(Boolean);if(a.length)return a.join(" + ")}for(const n of e){const s=Array.isArray(n.linked_tables)?n.linked_tables:Array.isArray(n.linkedTables)?n.linkedTables:[],a=[tt(n.primary_table||n.primaryTable),tt(n.table),...s.map(tt)].filter(Boolean);if(a.length)return[...new Set(a)].join(" + ")}for(const n of e){const s=y(n.table_name,n.tableName,n.name,n.code);if(s)return s}return""},Vt=(t={})=>y(t.product_name,t.name,t.product?.name,t.item_name,"Item"),Xt=(t={})=>{const e=Number(t.quantity??t.qty??1);return Number.isFinite(e)&&e>0?e:1},Zt=t=>Number.isInteger(t)?String(t):String(t).replace(/\.0+$/,""),an=(t={},e)=>[y(t.variant,t.variant_name),...Array.isArray(t.modifiers)?t.modifiers.map(s=>y(s.name,s.label,s)):[],y(t.notes,t.note,t.kitchen_note,t.instructions)].filter(Boolean).flatMap(s=>It(s,e,"  - ")),te=(t={})=>{const e=t.print_data||t.printData||t.batch||t,n=y(e.batch_code,e.batchCode,e.code,`KOT-${e.id||e.batch_id||""}`);return{outlet:y(e.outlet,e.store_name,e.location?.name,e.location_name),code:n,tokenNo:y(e.token_no,e.tokenNo,e.token_number,e.tokenNumber,e.token?.token_code,e.token?.token_no,e.order?.token?.token_code,e.order?.token_no,n),orderNo:y(e.order?.order_no,e.order_no,e.orderNo,e.order?.id,e.order_id),table:sn(e),status:y(e.status,"waiting"),time:y(e.sent_at,e.created_at,new Date().toISOString()),orderNotes:y(e.order?.notes,e.notes,e.table_notes),items:Array.isArray(e.items)?e.items:[]}},on=(t={},e,n=!0)=>{const a=`${Zt(Xt(t))} x`,r=" ".repeat(Math.min(7,a.length+2)),o=It(Vt(t),e-r.length);return o.length?[`${W(a.padEnd(r.length-1),n)} ${o[0].trim()}`,...o.slice(1).map(i=>`${r}${i.trim()}`)]:[W(a,n)]},rn=(t={},e={})=>{const n=e.paperSize||"58mm",s=tn(n),a=e.escposCommands===!0,r=te(t),o=[];return r.outlet&&o.push(dt(W(r.outlet.toUpperCase(),a),s,a)),o.push(dt(W("KITCHEN ORDER TOKEN",a),s,a)),o.push(K(s)),o.push(dt(en(`TOKEN ${r.tokenNo||r.code}`,a),s,a)),o.push(K(s)),o.push(j("KOT",r.code,s)),r.orderNo&&o.push(j("Order",r.orderNo,s)),r.table&&o.push(j("Table",r.table,s)),o.push(j("Status",r.status,s)),o.push(j("Time",r.time.replace("T"," ").slice(0,16),s)),o.push(K(s)),r.items.forEach(i=>{o.push(...on(i,s,a)),o.push(...an(i,s)),o.push(...Array(Xe).fill(""))}),r.orderNotes&&(o.push(K(s)),o.push(W("Notes",a)),o.push(...It(r.orderNotes,s))),o.push(K(s)),o.push(...Array(Ze).fill("")),o.join(`
`)},cn=(t={})=>{const e=te(t),n=e.items.map(s=>`
    <div class="item">
      <div class="qty">${f(Zt(Xt(s)))} x</div>
      <div class="name">${f(Vt(s))}</div>
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
  <div class="items">${n}</div>
  ${e.orderNotes?`<div class="line"></div><p class="notes"><strong>Notes:</strong> ${f(e.orderNotes)}</p>`:""}
  <div class="line"></div>
</body>
</html>`},ln=(t={},e={})=>({text:rn(t,e),html:cn(t),print_mode:e.printMode||"escpos"}),ee="paychat_print_agent_settings",mt={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},dn=8e3,Et=12e3,pn=1,un=3,mn=["invoice_url","invoiceUrl","review_url","reviewUrl"],_n=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},Nt=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),gn=t=>t==="80mm"?"80mm":"58mm",fn=t=>t==="pdf"?"pdf":"escpos",k=(t={})=>({...mt,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||mt.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:gn(t?.paperSize),printMode:fn(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),P=()=>typeof localStorage>"u"?{...mt}:k(_n(localStorage.getItem(ee),{})),hn=(t={})=>{const e=k({...P(),...t});try{localStorage.setItem(ee,JSON.stringify(e))}catch{}return e},pt=(t,e="PRINT_AGENT_ERROR",n=null)=>{const s=new Error(t);return s.code=e,n&&(s.cause=n),s},V=(t,e={},n={})=>{const s=k(e),a=new URL(t,`${s.agentUrl}/`),r={token:s.token,size:s.paperSize,printer_name:s.printerName,copies:1,print_mode:s.printMode,...n};return Object.entries(r).forEach(([o,i])=>{i!=null&&i!==""&&a.searchParams.set(o,String(i))}),a.toString()},X=async(t,e={},n=dn)=>{const s=new AbortController,a=setTimeout(()=>s.abort(),n);try{const r=await fetch(t,{...e,signal:s.signal}),i=(r.headers.get("content-type")||"").includes("application/json")?await r.json().catch(()=>null):await r.text().catch(()=>"");if(!r.ok)throw pt(i?.message||i?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return i}catch(r){throw r?.name==="AbortError"?pt("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",r):r?.code?r:pt("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",r)}finally{clearTimeout(a)}},yn=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},bn=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),_t=(t,e=0,n=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(bn)?t:[];if(typeof t!="object"||n.has(t))return[];n.add(t);const s=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const a of s){const r=_t(t[a],e+1,n);if(r.length)return r}for(const a of Object.values(t)){const r=_t(a,e+1,n);if(r.length)return r}return[]},Y=(...t)=>{for(const e of t){const n=Number(e);if(Number.isFinite(n))return n}return 0},A=(...t)=>{for(const e of t){const n=Nt(e).trim();if(n)return n}return""},it=(t={})=>{const e=t.invoice||t.invoice_data||{},n=t.data||t.order||{};return yn(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,n.items,n.order_items,n.line_items,n.cart_items,n.invoice_items,n.details,_t(t))},ot=(t={})=>A(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),J=(t={})=>Y(t.quantity,t.qty,t.pivot?.quantity,1)||1,rt=(t={})=>{const e=J(t),n=A(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(n!=="")return Number(n||0);const s=A(t.total,t.line_total,t.amount,t.subtotal);return Number(s||0)/e},ne=(t={})=>{const e=A(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):rt(t)*J(t)},Sn=(t=[])=>t.map(e=>({...e,product_name:ot(e),name:ot(e),quantity:J(e),qty:J(e),rate:rt(e),price:rt(e),total:ne(e)})),In=(t,e)=>{const n=Nt(t);if(n.length<=e)return[n];const s=[];for(let a=0;a<n.length;a+=e)s.push(n.slice(a,a+e));return s},Nn=(t,e)=>{const n=e==="80mm"?48:32;return Nt(t).split(/\r?\n/).flatMap(s=>In(s,n)).join(`
`)},$n=(t={},e="58mm")=>{const n=e==="80mm"?48:32,s=it(t);return s.length?s.map(a=>{const r=ot(a),o=J(a),i=rt(a),d=ne(a).toFixed(2),m=`${o} x ${i.toFixed(2)}`,$=Math.max(1,n-m.length-d.length);return`${r}
${m}${" ".repeat($)}${d}`}).join(`
`):""},vn=(t,e,n)=>{const s=it(e);return!s.length||s.some(r=>{const o=ot(r);return o&&t.includes(o.slice(0,Math.min(o.length,12)))})?t:`${t}
${$n(e,n)}`},wn=(t,e)=>{if(/total/i.test(t))return t;const n=Y(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,it(e).reduce((s,a)=>{const r=Y(a.quantity,a.qty,1)||1,o=Y(a.rate,a.price,a.unit_price);return s+Y(a.total,a.line_total,a.amount,r*o)},0));return`${t}
TOTAL ${n.toFixed(2)}`},At=t=>`\x1BE${t}\x1BE\0`,xn=(t="",e={})=>{const n=A(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(s=>{const a=s.trim();return a&&(n&&a.toLowerCase()===n.toLowerCase()||/^invoice no\b/i.test(a)||/^total\b/i.test(a))?At(s):s}).join(`
`)},En=(t={},e={})=>{for(const n of mn){const s=A(t[n],e[n]);if(s)return s}return A(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},An=(t={},e={})=>{const s=(Array.isArray(t.payments)?t.payments:[]).find(a=>String(a?.payment_method||a?.method||"").toLowerCase()==="upi"&&A(a.upi_qr_url,a.upiQrUrl,a.upi_qr_string,a.upi_payment_link,a.meta?.upi_qr_url,a.meta?.upi_qr_string,a.meta?.upi_payment_link,a.qr_payload,a.qr));return A(e.upiQr,e.paymentQr,t.upi_qr_url,t.upiQrUrl,t.upi_qr_string,t.upi_payment_link,t.payment?.upi_qr_url,t.payment?.upiQrUrl,t.payment?.upi_qr_string,t.payment?.upi_payment_link,t.payment?.meta?.upi_qr_url,t.payment?.meta?.upi_qr_string,t.payment?.meta?.upi_payment_link,t.qr?.upi_qr_url,t.qr?.upi_qr_string,t.qr?.upi_payment_link,t.receipt?.qr?.upi_qr_url,t.receipt?.qr?.upi_qr_string,t.receipt?.qr?.upi_payment_link,s?.upi_qr_url,s?.upiQrUrl,s?.upi_qr_string,s?.upi_payment_link,s?.meta?.upi_qr_url,s?.meta?.upi_qr_string,s?.meta?.upi_payment_link,s?.qr_payload,s?.qr)},On=t=>{try{const e=new URL(t);return["http:","https:","upi:"].includes(e.protocol)}catch{return A(t)!==""}},se=(t={},e={},n=P())=>{const s=k(n),a=s.paperSize,r={...t||{},items:Sn(it(t||{}))},o=Be(r,e||{}),i={paperSize:a,customPrintInvoice:s.customPrintInvoice,hideInvoiceQr:s.hideInvoiceQr,escposCommands:s.printMode==="escpos"&&a!=="80mm"};let c=Wt(o,i);const d=Ke(o,{...i,agentPdf:s.printMode==="pdf"});typeof c!="string"&&(c=String(c??"")),c=vn(c,r,a),c=wn(c,r),c=Nn(c,a),s.customPrintInvoice&&a!=="80mm"&&(c=xn(c,o)),c.length>Et&&(c=`${c.slice(0,Et)}
--- Receipt truncated ---`),c=c.replace(/\n*$/,`
`.repeat(s.customPrintInvoice?un:pn));const m=An(t,o),$=En(t,o),b=m||$,h={text:c,html:d,print_mode:s.printMode};return!s.hideInvoiceQr&&b&&On(b)&&(h.qr={data:b,size:6,error_correction:"M"}),h},Tn=async(t=P())=>{const e=k(t);return X(V("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},kn=async(t=P())=>{const e=k(t),n=await X(V("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(n)?n:Array.isArray(n?.printers)?n.printers:Array.isArray(n?.data)?n.data:[]},Cn=async(t=P())=>{const e=k(t);return X(V("/test-print",e),{method:"POST"})},Pn=async(t={},e={})=>{const n=k(e.settings||P()),s=se(t,e.context||{},n);return X(V("/print",n),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(s)})},qn=async(t={},e={})=>{const n=k(e.settings||P()),s=ln(t,{paperSize:n.paperSize,printMode:n.printMode,escposCommands:n.printMode==="escpos"});return X(V("/print",n),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(s)})},Ss={getSettings:P,saveSettings:hn,checkHealth:Tn,getPrinters:kn,testPrint:Cn,printReceipt:Pn,printKot:qn,buildSafeAgentReceiptPayload:se},Dn={list(t={}){return z.get("/upi-profiles",{params:t})},create(t){return z.post("/upi-profiles",t)},update(t,e){return z.patch(`/upi-profiles/${t}`,e)},deactivate(t){return z.delete(`/upi-profiles/${t}`)},setDefault(t){return z.patch(`/upi-profiles/${t}/default`)}},Ot="paychat_lightning_catalog_products",Tt="paychat_lightning_catalog_categories",et="paychat_lightning_catalog_updated_at",kt=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},Ct=t=>{const e=t&&t.data?t.data:t;return Array.isArray(e)?e:e&&Array.isArray(e.data)?e.data:e&&e.data&&Array.isArray(e.data.data)?e.data.data:e&&Array.isArray(e.products)?e.products:e&&e.data&&Array.isArray(e.data.products)?e.data.products:e&&Array.isArray(e.categories)?e.categories:[]},Ln=(t={})=>t.category_id||t.categoryId||t.category?.id||t.categories?.[0]?.id||t.product_category_id||t.pivot?.category_id||null,Mn=(t={})=>t.category_name||t.category?.name||t.categories?.[0]?.name||t.category||t.product_category||"",Pt=(t={})=>{const e=Ln(t),n=Mn(t);return{...t,id:t.id||t.product_id||t.sku||t.barcode||t.name,name:t.name||t.product_name||t.title||"Item",price:Number(t.price||t.selling_price||t.rate||t.amount||0),category_id:e,category_name:n,category_key:String(e||n||"").toLowerCase(),sku:t.sku||t.code||"",barcode:t.barcode||t.ean||t.upc||""}},qt=(t={})=>({...t,id:t.id||t.value||t.name,name:t.name||t.description||t.label||"Category",key:String(t.id||t.value||t.name||t.description||t.label||"").toLowerCase()}),Rn=(t=[])=>{const e=new Set;return t.map(n=>({id:n.category_id||n.category_name,name:n.category_name||"Uncategorized",key:String(n.category_id||n.category_name||"").toLowerCase()})).filter(n=>!n.id||e.has(n.key)?!1:(e.add(n.key),!0))},Dt=Ne("catalogCache",{state:()=>({products:[],categories:[],loading:!1,error:"",lastUpdatedAt:localStorage.getItem(et)||""}),getters:{activeProducts:t=>t.products.filter(e=>e&&e.id&&e.name),hasCachedCatalog:t=>t.products.length>0},actions:{loadCached(){this.products=(kt(localStorage.getItem(Ot),[])||[]).map(Pt),this.categories=(kt(localStorage.getItem(Tt),[])||[]).map(qt),this.lastUpdatedAt=localStorage.getItem(et)||""},persist(){localStorage.setItem(Ot,JSON.stringify(this.products)),localStorage.setItem(Tt,JSON.stringify(this.categories)),localStorage.setItem(et,new Date().toISOString()),this.lastUpdatedAt=localStorage.getItem(et)||""},async refresh(t={}){this.loading=!0,this.error="";try{const e={per_page:500};t.locationId&&(e.location_id=t.locationId);const[n,s]=await Promise.all([$e.list(e),Ut.list({per_page:500})]);this.products=Ct(n).map(Pt);const a=Ct(s).map(qt),r=Rn(this.products),o=new Set;this.categories=[...a,...r].filter(i=>{const c=i.key||String(i.id||i.name||"").toLowerCase();return!c||o.has(c)?!1:(o.add(c),!0)}),this.persist()}catch(e){this.error=e?.response?.data?.message||e?.message||"Catalog refresh failed",this.products.length||this.loadCached()}finally{this.loading=!1}},async bootstrap(t={}){this.loadCached(),await this.refresh(t)}}}),ct="paychat_pos_offline_mode_enabled",$t="pos_offline_mode",ae="paychat_offline_mode_cache",oe="paychat_offline_mode_cache_meta",gt="paychat:offline-mode-changed",Un=720*60*1e3,T=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},q=()=>T(localStorage.getItem(ae),{})||{},zn=t=>localStorage.setItem(ae,JSON.stringify(t||{})),re=()=>T(localStorage.getItem(oe),{})||{},Fn=t=>localStorage.setItem(oe,JSON.stringify(t||{})),Qn=(t={},e)=>{const n=Date.parse(t?.resources?.[e]?.last_synced_at||"");return Number.isFinite(n)&&Date.now()-n<Un},nt=(t,e="")=>{const n=t?.data?.data||t?.data||t||{};return Array.isArray(n)?n:Array.isArray(n.data)?n.data:e&&Array.isArray(n[e])?n[e]:Array.isArray(n.products)?n.products:Array.isArray(n.categories)?n.categories:Array.isArray(n.tables)?n.tables:Array.isArray(n.dining_tables)?n.dining_tables:[]},Bn=()=>(T(localStorage.getItem("selected_location"),{})||{}).id||localStorage.getItem("location_id")||"",H=()=>{const t=localStorage.getItem(ct);return t!==null?t==="true":localStorage.getItem($t)==="true"},Kn=()=>H()||typeof navigator<"u"&&navigator.onLine===!1,jn=t=>(localStorage.setItem(ct,t?"true":"false"),localStorage.removeItem($t),window.dispatchEvent(new CustomEvent(gt,{detail:{enabled:!!t}})),!!t),Gn=t=>{const e=s=>t(s.detail?.enabled??H()),n=s=>{[ct,$t].includes(s.key)&&t(H())};return window.addEventListener(gt,e),window.addEventListener("storage",n),()=>{window.removeEventListener(gt,e),window.removeEventListener("storage",n)}},ie=()=>{const t=re(),e=q(),n=t.resources||{},s=[{key:"products",label:"Products",count:e.products?.length||n.products?.count||0},{key:"categories",label:"Categories",count:e.categories?.length||n.categories?.count||0},{key:"diningStructure",label:"Table layout",count:e.diningStructure?.tables?.length||n.diningStructure?.count||0},{key:"upiProfiles",label:"UPI profiles",count:e.upiProfiles?.length||n.upiProfiles?.count||0},{key:"paymentMethods",label:"Payment methods",count:e.paymentMethods?.length||n.paymentMethods?.count||0},{key:"tenantContext",label:"Tenant context",count:n.tenantContext?.count||0},{key:"locations",label:"Locations",count:e.locations?.length||n.locations?.count||0}].map(a=>({...a,status:n[a.key]?.status||"missing",error:n[a.key]?.error||"",last_synced_at:n[a.key]?.last_synced_at||""}));return{enabled:H(),ready:s.every(a=>a.status==="ready"),lastPreparedAt:t.last_prepared_at||"",checklist:s,cache:e}},Lt=(t,e,n)=>{t.resources=t.resources||{},t.resources[e]={...t.resources[e]||{},...n,updated_at:new Date().toISOString()}},Yn=async({force:t=!1,locationId:e=Bn()}={})=>{const n=q(),s=re();s.resources=s.resources||{};const a=[],r=async(o,i,c=d=>Array.isArray(d)?d.length:+!!d)=>{if(!(!t&&n[o]&&Qn(s,o)))try{const d=await i();n[o]=d,Lt(s,o,{status:"ready",count:c(d),error:"",last_synced_at:new Date().toISOString()})}catch(d){const m=d?.response?.data?.message||d?.message||`${o} failed to load`;Lt(s,o,{status:"failed",error:m}),a.push({key:o,message:m})}};if(await r("products",async()=>{const o=Dt();return!t&&o.hasCachedCatalog||await o.refresh({locationId:e}),o.products}),await r("categories",async()=>{const o=Dt();if(!t&&o.categories?.length)return o.categories;const i=await Ut.list({per_page:500});return nt(i,"categories")}),await r("diningStructure",async()=>{if(!e)return{tables:[]};const o=await ye.diningStructure({location_id:e}),i=o?.data?.data||o?.data||{};return{...i,tables:i.tables||i.dining_tables||[]}},o=>o?.tables?.length||0),await r("upiProfiles",async()=>{const o=await Dn.list({location_id:e||void 0,include_global:1});return nt(o,"profiles")}),await r("paymentMethods",async()=>{const o=await Se.getMethods();return nt(o,"methods")}),await r("locations",async()=>{const o=await Ie.list();return nt(o,"locations")}),await r("tenantContext",async()=>({tenant_info:T(localStorage.getItem("tenant_info"),{}),tenant_tax_config:T(localStorage.getItem("tenant_tax_config"),null),tenant_settings:T(localStorage.getItem("tenant_settings"),{}),tenant_slug:localStorage.getItem("tenant_slug"),tenant_id:localStorage.getItem("tenant_id"),tenant_api_key:localStorage.getItem("tenant_api_key")}),o=>+!!(o?.tenant_slug||o?.tenant_info)),s.last_prepared_at=new Date().toISOString(),zn(n),Fn(s),a.length){const o=new Error(a.map(i=>i.message).join(", "));throw o.resources=a,o}return ie()},Wn=()=>q(),Jn=()=>{const t=q();return Array.isArray(t.products)?t.products:T(localStorage.getItem("paychat_lightning_catalog_products"),[])||[]},Hn=()=>{const t=q();return Array.isArray(t.categories)?t.categories:T(localStorage.getItem("paychat_lightning_catalog_categories"),[])||[]},ce=()=>q().diningStructure||{tables:[]},Vn=(t=null)=>{const e=ce(),n=e.tables||e.dining_tables||[];return Array.isArray(n)?t?n.filter(s=>!s.location_id||String(s.location_id)===String(t)):n:[]},Xn=()=>{const t=q(),e=Array.isArray(t.paymentMethods)?t.paymentMethods:[];return e.length?e:[{type:"cash",name:"Cash",label:"Cash",enabled:!0},{type:"upi",name:"UPI",label:"UPI",enabled:!0}]},Zn=()=>{const t=q(),e=Array.isArray(t.upiProfiles)?t.upiProfiles:[];if(e.length)return e;const n=T(localStorage.getItem("tenant_info"),{})||{},s=localStorage.getItem("owner_upi_id")||localStorage.getItem("static_upi_id")||n?.branding?.upi_id||n?.tenant?.upi_id||"";return s?[{id:"offline-default-upi",label:"Default UPI",name:"Default UPI",upi_id:s,is_active:!0,is_default:!0,offline_generated:!0}]:[]},Is={POS_OFFLINE_MODE_KEY:ct,isOfflineModeEnabled:H,isOfflineRuntime:Kn,setOfflineModeEnabled:jn,subscribeToOfflineModeChanges:Gn,prepareOfflineData:Yn,getOfflineReadiness:ie,getOfflineCache:Wn,getCachedProducts:Jn,getCachedCategories:Hn,getCachedDiningStructure:ce,getCachedTables:Vn,getCachedPaymentMethods:Xn,getCachedUpiProfiles:Zn},le="paychat_offline_released_tables",de="paychat:offline-table-released",ts=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},lt=()=>ts(localStorage.getItem(le),[])||[],pe=t=>{localStorage.setItem(le,JSON.stringify(t||[]))},I=t=>t==null||t===""?"":String(t),ft=(t=[])=>Array.from(new Set(t.map(I).filter(Boolean))),es=(t={})=>ft([t.table_id,t.primary_table_id,...Array.isArray(t.linked_table_ids)?t.linked_table_ids:[],t.table_snapshot?.id,t.table_snapshot?.table_id,t.primary_table?.id,...Array.isArray(t.tables)?t.tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.linked_tables)?t.linked_tables.map(e=>e?.id||e?.table_id):[]]),Ns=()=>lt(),$s=(t={})=>{const e=es(t);if(!e.length)return null;const n=I(t.local_order_id),s={local_order_id:n,order_id:I(t.order_id||t.backend_order_id),table_session_id:I(t.table_session_id),table_ids:e,released_at:new Date().toISOString()},a=lt().filter(r=>n?I(r.local_order_id)!==n:!r.table_ids?.some(o=>e.includes(I(o))));return a.push(s),pe(a),window.dispatchEvent(new CustomEvent(de,{detail:s})),s},ns=t=>{const e=I(t);if(!e)return;const n=lt().filter(s=>I(s.local_order_id)!==e);pe(n),window.dispatchEvent(new CustomEvent(de,{detail:{local_order_id:e,cleared:!0}}))},vs=(t={},e=lt())=>{const n=ft([t.table_id,t.table?.id,t.__gridTable?.id,t.order?.table_id,t.order?.table?.id,ss(t)].flat()),s=I(t.order_id||t.order?.id),a=I(t.table_session_id||t.order?.table_session_id||t.order?.table_session?.id||(t.order?t.id:null));return e.some(r=>{const o=ft(r.table_ids||[]);return!!(n.some(i=>o.includes(i))||s&&I(r.order_id)===s||a&&I(r.table_session_id)===a)})},ss=(t={})=>[...Array.isArray(t.tables)?t.tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.linked_tables)?t.linked_tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.order?.tables)?t.order.tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.order?.linked_tables)?t.order.linked_tables.map(e=>e?.id||e?.table_id):[]];let G=null;const _={PENDING_SYNC:"pending_sync",SYNCING:"syncing",SYNCED:"synced",FAILED:"failed"},D=()=>(G||(G=new ve("paychatpos_offline_db"),G.version(1).stores({offlineOrders:"local_order_id, status, created_at, synced_at, backend_order_id"}),G.version(2).stores({offlineOrders:"local_order_id, status, created_at, synced_at, backend_order_id",offlineTableSessions:"local_session_id, status, location_id, primary_table_id, local_order_id, updated_at",offlineTableOrders:"local_order_id, status, location_id, table_session_id, primary_table_id, updated_at",offlineKotBatches:"local_kot_id, local_order_id, status, created_at"})),G),ht=(t,e=new WeakSet)=>{if(t==null||typeof t=="string"||typeof t=="number"||typeof t=="boolean")return t;if(typeof t=="bigint")return Number(t);if(t instanceof Date)return t.toISOString();if(typeof File<"u"&&t instanceof File)return{name:t.name,type:t.type,size:t.size,last_modified:t.lastModified};if(typeof t=="object"&&!e.has(t))return e.add(t),Array.isArray(t)?t.map(n=>ht(n,e)).filter(n=>n!==void 0):Object.entries(t).reduce((n,[s,a])=>{if(typeof a=="function"||typeof a=="symbol")return n;const r=ht(a,e);return r!==void 0&&(n[s]=r),n},{})},ws=async t=>{const e=D(),n=new Date().toISOString(),s=ht(t),a=await e.offlineOrders.get(s.local_order_id);return a?.status===_.SYNCED?a.payload||s:(await e.offlineOrders.put({...a||{},local_order_id:s.local_order_id,status:_.PENDING_SYNC,created_at:a?.created_at||n,updated_at:n,payload:s,sync_error:null,backend_order_id:a?.backend_order_id||null,synced_at:a?.synced_at||null,backend_response:a?.backend_response||null}),s)},ue=async()=>D().offlineOrders.where("status").anyOf(_.PENDING_SYNC,_.FAILED).toArray(),xs=async({includeSynced:t=!1}={})=>{const e=D();return(t?await e.offlineOrders.toArray():await e.offlineOrders.where("status").anyOf(_.PENDING_SYNC,_.FAILED,_.SYNCING).toArray()).sort((s,a)=>Date.parse(a.created_at||0)-Date.parse(s.created_at||0))},as=async t=>D().offlineOrders.get(t),Mt=async t=>D().offlineOrders.update(t,{status:_.SYNCING,sync_error:null,updated_at:new Date().toISOString()}),os=async(t=15)=>{const e=D(),n=Date.now()-Number(t||15)*60*1e3,s=await e.offlineOrders.where("status").equals(_.SYNCING).toArray();let a=0;for(const r of s){const o=Date.parse(r.updated_at||r.created_at||"");Number.isFinite(o)&&o>n||(await e.offlineOrders.update(r.local_order_id,{status:_.PENDING_SYNC,sync_error:null,updated_at:new Date().toISOString()}),a+=1,console.log("[Offline Sync] stale syncing order recovered",r.local_order_id))}return a},rs=async(t,e)=>{const n=D(),s=e?.data||e||{},a=await n.offlineOrders.get(t),r=s?.side_effects?.table_session||s?.data?.side_effects?.table_session;return(!(a?.payload?.dining_flow==="table_service")||r!=="failed")&&ns(t),n.offlineOrders.update(t,{status:_.SYNCED,sync_error:null,backend_order_id:s?.order?.id||s?.data?.order?.id||s?.order_id||null,synced_at:new Date().toISOString(),updated_at:new Date().toISOString(),backend_response:s})},is=async(t,e)=>D().offlineOrders.update(t,{status:_.FAILED,sync_error:e?.response?.data||e?.message||String(e),updated_at:new Date().toISOString()}),Es=async()=>{await os();const t=await ue(),e={synced:0,failed:0,total:t.length},n=s=>{const a=s?.response?.status,r=s?.response?.data||s?.data||s||{},o=String(r?.error_code||r?.message||r?.error||s?.message||"").toLowerCase();return a===409&&(o.includes("processing")||o.includes("syncing")||o.includes("locked"))};e.orders=[];for(const s of t){const a=await cs(s.local_order_id,{isFreshProcessingConflict:n});e.orders.push(a),a.status===_.SYNCED&&(e.synced+=1),a.status===_.FAILED&&(e.failed+=1)}return e},cs=async(t,e={})=>{const n=await as(t);if(!n)throw new Error("Offline order not found");if(n.status===_.SYNCED)return{local_order_id:t,status:_.SYNCED,response:n.backend_response};const s=e.isFreshProcessingConflict||(a=>{const r=a?.response?.status,o=a?.response?.data||a?.data||a||{},i=String(o?.error_code||o?.message||o?.error||a?.message||"").toLowerCase();return r===409&&(i.includes("processing")||i.includes("syncing")||i.includes("locked"))});try{await Mt(t),console.log("[Offline Sync] syncing order",t);const a=localStorage.getItem("tenant_api_key"),r=a?{"X-Tenant-Api-Key":a}:{},o=await z.post("/offline-orders/sync",n.payload,{headers:r});return await rs(t,o),console.log("[Offline Sync] synced order",t),{local_order_id:t,status:_.SYNCED,response:o?.data||o}}catch(a){return s(a)?(console.log("[Offline Sync] backend still processing order",t),await Mt(t),{local_order_id:t,status:_.SYNCING,error:a}):(be({type:"offline_sync_failure",action:"offline.sync_order",local_order_id:t,backend_message:a?.response?.data?.message||a?.message||String(a)}),await is(t,a),{local_order_id:t,status:_.FAILED,error:a?.response?.data||a?.message||String(a)})}},As=async()=>(await ue()).length,me="paychat_kitchen_operation_mode",_e="paychat_generate_inline_kitchen_token",ge="paychat_inline_kitchen_without_status_management",Q={DEDICATED_KDS:"dedicated_kds",INLINE:"inline"},fe=Object.values(Q),R=()=>typeof window>"u"?null:window.localStorage||null,Rt=t=>{try{const e=R()?.getItem(t);return e?JSON.parse(e):null}catch{return null}},ls=()=>{const t=Rt("tenant_settings")||{},e=Rt("tenant_info")||{},n=t?.kitchen?.operation_mode||t?.raw?.kitchen_operation_mode||e?.settings?.kitchen?.operation_mode||e?.settings?.raw?.kitchen_operation_mode||e?.tenant?.settings?.kitchen?.operation_mode||e?.tenant?.settings?.raw?.kitchen_operation_mode;return fe.includes(n)?n:null},ds=()=>{const e=R()?.getItem(me);return fe.includes(e)?e:ls()||Q.DEDICATED_KDS},Os=t=>{const e=t===Q.INLINE?Q.INLINE:Q.DEDICATED_KDS;return R()?.setItem(me,e),e},Ts=()=>ds()===Q.INLINE,ks=()=>R()?.getItem(_e)==="true",Cs=t=>{const e=!!t;return R()?.setItem(_e,e?"true":"false"),e},Ps=()=>R()?.getItem(ge)==="true",qs=t=>{const e=!!t;return R()?.setItem(ge,e?"true":"false"),e};export{Q as K,de as O,Ke as a,bs as b,Ps as c,As as d,Es as e,Dt as f,D as g,Ns as h,Ts as i,ds as j,ks as k,vs as l,$s as m,Be as n,Is as o,Ss as p,xs as q,cs as r,ws as s,Os as t,Dn as u,qs as v,Cs as w};
