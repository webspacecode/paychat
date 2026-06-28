import{g as J,b as P}from"./index-D8Ybcfac.js";import{o as ft}from"./vendor-qKbVCTru.js";const yt="/color-paychat-logo-main.svg",tt={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},bt=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},st=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},vt=()=>bt(st("tenant_info"),{}),it=t=>A(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),at=t=>it(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),ct=t=>{const e=it(t);if(!e)return"";const o=e.split(",").map(n=>n.trim()).filter(Boolean);return(o.length?o.slice(0,2).join(", "):e).slice(0,80)},a=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),I=t=>a(t).replace(/`/g,"&#096;"),A=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),u=t=>Number(t||0).toFixed(2),z=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},lt=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},pt=(t="80mm")=>tt[t]||tt["80mm"],d=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},T=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},_t=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},$t=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),F=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some($t)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=F(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=F(r,e+1,o);if(s.length)return s}return[]},xt=(t={})=>d(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),V=(t={})=>T(t.quantity,t.qty,t.pivot?.quantity,1)||1,dt=(t={})=>{const e=V(t),o=d(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=d(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},wt=(t={})=>{const e=d(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):dt(t)*V(t)},Nt=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return _t(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,F(t))},kt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return d(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},et=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=d(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},St=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const o=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(o)&&(e=o)}catch{}return e.trim()},Tt=t=>{if(!t)return"";const e=St(t),o=e.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const r=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${I(r)}" alt="Invoice QR" />`}const n=e.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);return n?.[1]?`<img class="qr-image" src="${I(n[1])}" alt="Invoice QR" />`:/^(data:image\/|https?:\/\/|\/)/i.test(e)?`<img class="qr-image" src="${I(e)}" alt="Invoice QR" />`:`<div class="qr-url">${a(e)}</div>`},It=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=vt(),n=J(),r=o?.tenant||{},s=o?.branding||{},i=t.location||{},l=Nt(t).map(b=>({name:xt(b),qty:V(b),rate:dt(b),total:wt(b)})),c=T(t.subtotal,t.totals?.subtotal,l.reduce((b,f)=>b+f.rate*f.qty,0)),m=T(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),p=T(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),g=T(t.total,t.grand_total,t.totals?.grand_total,c+p-m);return{shopName:d(e.shopName,i.tenant?.name,t.tenant?.name,s.company_name,r.name,st("tenant_slug"),"PayChat POS"),shopPhone:d(e.shopPhone,i.phone,s.phone,r.phone),shopAddress:d(e.shopAddress,i.address,s.address,r.address),shopLogoUrl:d(e.shopLogoUrl,i.logo,i.tenant?.logo,t.tenant?.logo,s.logo,r.logo),locationName:d(i.name,t.location_name),paychatLogoUrl:d(e.paychatLogoUrl,t.paychat_logo_url,yt),invoiceNo:d(e.invoiceNo,t.invoice_no,t.invoiceNo,t.invoice?.number,t.invoice?.invoice_no,t.invoice?.offline_invoice_number,t.offline_invoice_number,t.local_invoice_no),orderNo:d(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:d(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:d(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:d(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:d(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:d(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:et(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:et(t.batch_codes,t.batchCodes),items:l,subtotal:c,discount:m,tax:p,grandTotal:g,paidAmount:T(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,g),paymentMethod:kt(t),invoiceUrl:d(e.invoiceUrl,t.invoice_url,t.invoiceUrl,t.invoice?.url),invoiceQr:d(e.invoiceQr,t.invoice_qr,t.invoiceQr,t.qr),reviewQr:d(e.reviewQr,t.review_qr,t.reviewQr),notes:d(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},At=(t,e={})=>{const o=e.paperSize||"80mm",n=pt(o),r=o==="58mm",s=e.agentPdf===!0,i=e.customPrintInvoice===!0,l=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],m=Array.isArray(t.kotCodes)?t.kotCodes:[],p=Array.isArray(t.batchCodes)?t.batchCodes:[],g=J(),f=!(t.simpleBilling??g.simpleBilling),Q=t.billingLabel||g.billingLabel||"Order",L=i?at(t.shopName):t.shopName,w=i?ct(t.shopAddress):t.shopAddress,S=d(t.invoiceNo,i?t.orderNo:""),W=i?r?"48px":"64px":n.paychatLogoWidth,v=l?"":Tt(t.invoiceQr||t.reviewQr),Z=t.invoiceUrl&&(l||!v)?`<div class="qr-url">${a(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
      max-width: ${W};
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
      ${!i&&t.shopLogoUrl?`<img class="shop-logo" src="${I(t.shopLogoUrl)}" alt="${I(L)}" />`:""}
      <div class="title">${a(L)}</div>
      ${!i&&t.locationName?`<div class="muted">${a(t.locationName)}</div>`:""}
      ${w?`<div class="muted">${a(w)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${a(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${S&&!i?`<div class="bill-no">BILL NO: ${a(S)}</div>`:""}
    <table>
	      ${S&&i?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${a(S)}</strong></td></tr>`:""}
	      ${!i&&t.orderNo?`<tr><td>${a(Q)}</td><td class="right">${a(t.orderNo)}</td></tr>`:""}
      <tr><td>Date</td><td class="right">${a(lt(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${a(t.orderType)}</td></tr>`:""}
	      ${f&&t.tableName?`<tr><td>Table</td><td class="right">${a(t.tableName)}</td></tr>`:""}
	      ${f&&t.guestCount&&!i?`<tr><td>Guests</td><td class="right">${a(t.guestCount)}</td></tr>`:""}
	      ${f&&t.tokenNo&&!i?`<tr><td>Token</td><td class="right">${a(t.tokenNo)}</td></tr>`:""}
	      ${f&&m.length?`<tr><td>KOT</td><td class="right">${a(m.join(", "))}</td></tr>`:""}
	      ${f&&p.length?`<tr><td>Batch</td><td class="right">${a(p.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${r?`
      <div>
        ${c.length?c.map(_=>`
          <div class="item-block">
            <div class="item-name">${a(_.name)}</div>
            <div class="item-meta">
              <span>${a(z(_.qty))} x ${a(u(_.rate))}</span>
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
          ${c.length?c.map(_=>`
            <tr>
              <td class="item-name">${a(_.name)}</td>
              <td class="right">${a(z(_.qty))}</td>
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
    `:s?`
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
    ${v||Z?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!l&&v?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${v||Z}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!i?`<img class="paychat-logo" src="${I(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},$=(t,e="-")=>`${e.repeat(t)}
`,y=(t,e)=>{const o=A(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},h=(t,e,o)=>{const n=A(e),r=Math.max(1,o-n.length-1),s=A(t).slice(0,r),i=Math.max(1,o-s.length-n.length);return`${s}${" ".repeat(i)}${n}
`},Y=(t,e)=>{const o=A(t).split(/\s+/).filter(Boolean).flatMap(s=>s.length<=e?[s]:s.match(new RegExp(`.{1,${e}}`,"g"))||[s]),n=[];let r="";return o.forEach(s=>{if(!r){r=s;return}(r+" "+s).length<=e?r+=` ${s}`:(n.push(r),r=s.slice(0,e))}),r&&n.push(r),n.length?n:[""]},Lt=(t,e)=>{const o=Y(t.name,e),n=`${z(t.qty)} x ${u(t.rate)}`;return[...o.map(r=>`${r}
`),h(n,u(t.total),e)].join("")},Pt=(t,e)=>{const s=e-5-9-10,i=Y(t.name,s),l=`${i[0].padEnd(s)}${z(t.qty).padStart(5)}${u(t.rate).padStart(9)}${u(t.total).padStart(10)}
`,c=i.slice(1).map(m=>`${m}
`).join("");return l+c},ut=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=pt(o),r=o==="58mm",s=e.customPrintInvoice===!0,i=e.hideInvoiceQr===!0,l=Array.isArray(t.items)?t.items:[],c=Array.isArray(t.kotCodes)?t.kotCodes:[],m=Array.isArray(t.batchCodes)?t.batchCodes:[],p=J(),g=t.simpleBilling??p.simpleBilling,b=t.billingLabel||p.billingLabel||"Order",f=r?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,Q=s?at(t.shopName):t.shopName,L=s?ct(t.shopAddress):t.shopAddress,w=d(t.invoiceNo,s?t.orderNo:""),S=s&&t.tokenNo?`${$(n)}${y(`TOKEN ${t.tokenNo}`,n)}${$(n)}`:"",W=t.invoiceUrl?`${$(n)}${y(i?"Invoice link":"Invoice/review link",n)}${Y(t.invoiceUrl,n).map(v=>`${A(v)}
`).join("")}`:"";return[S,y(Q,n),!s&&t.locationName?y(t.locationName,n):"",t.shopPhone?y(`Phone: ${t.shopPhone}`,n):"",L?y(L,n):"",$(n),w&&s?h("Invoice No",w,n):"",w&&!s?y(`BILL NO: ${w}`,n):"",!s&&t.orderNo?h(b,t.orderNo,n):"",h("Date",lt(t.dateTime),n),t.orderType?h("Type",t.orderType,n):"",!g&&t.tableName?h("Table",t.tableName,n):"",!g&&t.guestCount&&!s?h("Guests",t.guestCount,n):"",!g&&t.tokenNo&&!s?h("Token",t.tokenNo,n):"",!g&&c.length?h("KOT",c.join(","),n):"",!g&&m.length?h("Batch",m.join(","),n):"",$(n),f,f?$(n):"",l.length?l.map(v=>r?Lt(v,n):Pt(v,n)).join(""):y("No items",n),$(n),s?"":h("Subtotal",u(t.subtotal),n),!s&&t.discount?h("Discount",`-${u(t.discount)}`,n):"",!s&&t.tax?h("Tax/GST",u(t.tax),n):"",s?"":$(n),h("TOTAL",u(t.grandTotal),n),t.paidAmount&&!s?h("Paid",u(t.paidAmount),n):"",t.paymentMethod?h("Payment",t.paymentMethod,n):"",W,$(n),y("Thank you",n),y("Powered by PayChat",n)].join("")},oe=ut,mt="paychat_print_agent_settings",K={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},Et=8e3,nt=12e3,Ct=["invoice_url","invoiceUrl","review_url","reviewUrl"],qt=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},X=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),zt=t=>t==="80mm"?"80mm":"58mm",Ot=t=>t==="pdf"?"pdf":"escpos",x=(t={})=>({...K,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||K.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:zt(t?.paperSize),printMode:Ot(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),k=()=>typeof localStorage>"u"?{...K}:x(qt(localStorage.getItem(mt),{})),Ut=(t={})=>{const e=x({...k(),...t});try{localStorage.setItem(mt,JSON.stringify(e))}catch{}return e},D=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},M=(t,e={},o={})=>{const n=x(e),r=new URL(t,`${n.agentUrl}/`),s={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(s).forEach(([i,l])=>{l!=null&&l!==""&&r.searchParams.set(i,String(l))}),r.toString()},j=async(t,e={},o=Et)=>{const n=new AbortController,r=setTimeout(()=>n.abort(),o);try{const s=await fetch(t,{...e,signal:n.signal}),l=(s.headers.get("content-type")||"").includes("application/json")?await s.json().catch(()=>null):await s.text().catch(()=>"");if(!s.ok)throw D(l?.message||l?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return l}catch(s){throw s?.name==="AbortError"?D("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",s):s?.code?s:D("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",s)}finally{clearTimeout(r)}},Rt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Mt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),H=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Mt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=H(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=H(r,e+1,o);if(s.length)return s}return[]},E=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},N=(...t)=>{for(const e of t){const o=X(e).trim();if(o)return o}return""},B=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Rt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,H(t))},O=(t={})=>N(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),C=(t={})=>E(t.quantity,t.qty,t.pivot?.quantity,1)||1,U=(t={})=>{const e=C(t),o=N(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=N(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},gt=(t={})=>{const e=N(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):U(t)*C(t)},jt=(t=[])=>t.map(e=>({...e,product_name:O(e),name:O(e),quantity:C(e),qty:C(e),rate:U(e),price:U(e),total:gt(e)})),Bt=(t,e)=>{const o=X(t);if(o.length<=e)return[o];const n=[];for(let r=0;r<o.length;r+=e)n.push(o.slice(r,r+e));return n},Qt=(t,e)=>{const o=e==="80mm"?48:32;return X(t).split(/\r?\n/).flatMap(n=>Bt(n,o)).join(`
`)},Wt=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=B(t);return n.length?n.map(r=>{const s=O(r),i=C(r),l=U(r),m=gt(r).toFixed(2),p=`${i} x ${l.toFixed(2)}`,g=Math.max(1,o-p.length-m.length);return`${s}
${p}${" ".repeat(g)}${m}`}).join(`
`):""},Dt=(t,e,o)=>{const n=B(e);return!n.length||n.some(s=>{const i=O(s);return i&&t.includes(i.slice(0,Math.min(i.length,12)))})?t:`${t}
${Wt(e,o)}`},Gt=(t,e)=>{if(/total/i.test(t))return t;const o=E(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,B(e).reduce((n,r)=>{const s=E(r.quantity,r.qty,1)||1,i=E(r.rate,r.price,r.unit_price);return n+E(r.total,r.line_total,r.amount,s*i)},0));return`${t}
TOTAL ${o.toFixed(2)}`},ot=t=>`\x1BE${t}\x1BE\0`,Ft=(t="",e={})=>{const o=N(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(n=>{const r=n.trim();return r&&(o&&r.toLowerCase()===o.toLowerCase()||/^invoice no\b/i.test(r)||/^total\b/i.test(r))?ot(n):n}).join(`
`)},Kt=(t={},e={})=>{for(const o of Ct){const n=N(t[o],e[o]);if(n)return n}return N(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},Ht=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},ht=(t={},e={},o=k())=>{const n=x(o),r=n.paperSize,s={...t||{},items:jt(B(t||{}))},i=It(s,e||{}),l={paperSize:r,customPrintInvoice:n.customPrintInvoice,hideInvoiceQr:n.hideInvoiceQr};let c=ut(i,l);const m=At(i,{...l,agentPdf:n.printMode==="pdf"});typeof c!="string"&&(c=String(c??"")),c=Dt(c,s,r),c=Gt(c,s),c=Qt(c,r),n.customPrintInvoice&&(c=Ft(c,i)),c.length>nt&&(c=`${c.slice(0,nt)}
--- Receipt truncated ---`),c=c.replace(/\n*$/,`


`);const p=Kt(t,i),g={text:c,html:m,print_mode:n.printMode};return!n.hideInvoiceQr&&p&&Ht(p)&&(g.qr={data:p,size:6,error_correction:"M"}),g},Jt=async(t=k())=>{const e=x(t);return j(M("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Vt=async(t=k())=>{const e=x(t),o=await j(M("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},Yt=async(t=k())=>{const e=x(t);return j(M("/test-print",e),{method:"POST"})},Xt=async(t={},e={})=>{const o=x(e.settings||k()),n=ht(t,e.context||{},o);return j(M("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},re={getSettings:k,saveSettings:Ut,checkHealth:Jt,getPrinters:Vt,testPrint:Yt,printReceipt:Xt,buildSafeAgentReceiptPayload:ht},se={list(t={}){return P.get("/upi-profiles",{params:t})},create(t){return P.post("/upi-profiles",t)},update(t,e){return P.patch(`/upi-profiles/${t}`,e)},deactivate(t){return P.delete(`/upi-profiles/${t}`)},setDefault(t){return P.patch(`/upi-profiles/${t}/default`)}},R="paychat_pos_wake_lock_enabled",rt=()=>{try{return localStorage.getItem(R)==="true"}catch{return!1}},ie=t=>{try{return t?(localStorage.setItem(R,"true"),!0):(localStorage.removeItem(R),!1)}catch{return!1}},Zt=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},ae=()=>{let t=null,e=!1,o=!1,n=0;const r=async()=>{try{await t?.release?.()}catch(p){console.warn("POS wake lock release failed:",p)}finally{t=null}},s=()=>{const p=Zt();return p.supported?!0:(o||(console.warn(`POS wake lock unavailable: ${p.reason}`),o=!0),!1)},i=async()=>{const p=Date.now();if(!(e||t||!rt()||!s()||document.visibilityState!=="visible")&&!(p-n<750)){n=p;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(g){console.warn("POS wake lock failed:",g)}}},l=()=>{i()},c=()=>{document.visibilityState==="visible"?i():r()},m=p=>{p.key===R&&(rt()?i():r())};return document.addEventListener("visibilitychange",c),document.addEventListener("pointerdown",l,{passive:!0}),document.addEventListener("touchstart",l,{passive:!0}),document.addEventListener("click",l,{passive:!0}),window.addEventListener("storage",m),i(),()=>{e=!0,document.removeEventListener("visibilitychange",c),document.removeEventListener("pointerdown",l),document.removeEventListener("touchstart",l),document.removeEventListener("click",l),window.removeEventListener("storage",m),r()}},te="paychat-pos",q="cache",G=ft(te,1,{upgrade(t){t.createObjectStore(q)}}),ce={async set(t,e){await(await G).put(q,e,t)},async get(t){return await(await G).get(q,t)},async clear(){await(await G).clear(q)}};export{At as a,oe as b,ce as c,ie as d,Zt as e,rt as g,It as n,re as p,ae as s,se as u};
