import{g as D,b as k}from"./index-Drzfi3q8.js";import{o as st}from"./vendor-qKbVCTru.js";const it="/color-paychat-logo-main.svg",H={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},at=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},Y=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},ct=()=>at(Y("tenant_info"),{}),c=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),L=t=>c(t).replace(/`/g,"&#096;"),T=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),d=t=>Number(t||0).toFixed(2),I=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},X=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},Z=(t="80mm")=>H[t]||H["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},S=(...t)=>{for(const e of t){if(e==null||e==="")continue;const o=Number(e);if(Number.isFinite(o))return o}return 0},lt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},pt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),M=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(pt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=M(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=M(r,e+1,o);if(s.length)return s}return[]},ut=(t={})=>p(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),Q=(t={})=>S(t.quantity,t.qty,t.pivot?.quantity,1)||1,tt=(t={})=>{const e=Q(t),o=p(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=p(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},dt=(t={})=>{const e=p(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):tt(t)*Q(t)},mt=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return lt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,M(t))},ht=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},K=(...t)=>{const e=[];return t.flat().forEach(o=>{if(!o)return;if(typeof o=="string"||typeof o=="number"){e.push(String(o));return}const n=p(o.code,o.kot_code,o.batch_code,o.token_code,o.id);n&&e.push(String(n))}),[...new Set(e)]},gt=(t,e)=>{if(!t)return"";const o=String(t);return o.trim().startsWith("<svg")||o.trim().startsWith("<img")?`<div class="qr-embed">${o}</div>`:/^(data:image\/|https?:\/\/|\/)/i.test(o)?`<img class="qr-image" src="${L(o)}" alt="Invoice QR" />`:`<div class="qr-url">${c(o)}</div>`},ft=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const o=ct(),n=D(),r=o?.tenant||{},s=o?.branding||{},a=t.location||{},i=mt(t).map(h=>({name:ut(h),qty:Q(h),rate:tt(h),total:dt(h)})),m=S(t.subtotal,t.totals?.subtotal,i.reduce((h,w)=>h+w.rate*w.qty,0)),u=S(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),l=S(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),f=S(t.total,t.grand_total,t.totals?.grand_total,m+l-u);return{shopName:p(e.shopName,a.tenant?.name,t.tenant?.name,s.company_name,r.name,Y("tenant_slug"),"PayChat POS"),shopPhone:p(e.shopPhone,a.phone,s.phone,r.phone),shopAddress:p(e.shopAddress,a.address,s.address,r.address),shopLogoUrl:p(e.shopLogoUrl,a.logo,a.tenant?.logo,t.tenant?.logo,s.logo,r.logo),locationName:p(a.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,it),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,t.invoice?.number,t.invoice?.invoice_no,t.invoice?.offline_invoice_number,t.offline_invoice_number,t.local_invoice_no),orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:K(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:K(t.batch_codes,t.batchCodes),items:i,subtotal:m,discount:u,tax:l,grandTotal:f,paidAmount:S(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,f),paymentMethod:ht(t),invoiceUrl:p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,t.invoice?.url),invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},yt=(t,e={})=>{const o=e.paperSize||"80mm",n=Z(o),r=o==="58mm",s=Array.isArray(t.items)?t.items:[],a=Array.isArray(t.kotCodes)?t.kotCodes:[],i=Array.isArray(t.batchCodes)?t.batchCodes:[],m=D(),l=!(t.simpleBilling??m.simpleBilling),f=t.billingLabel||m.billingLabel||"Order",h=gt(t.invoiceQr||t.reviewQr,n.qrSize),w=!h&&t.invoiceUrl?`<div class="qr-url">${c(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
      line-height: 1.28;
    }
    .receipt {
      width: ${n.width};
      padding: ${n.padding};
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { font-size: 0.88em; }
    .title {
      font-size: ${n.titleSize};
      font-weight: 700;
      text-transform: uppercase;
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
      max-width: ${n.paychatLogoWidth};
      max-height: ${r?"20px":"26px"};
      object-fit: contain;
      margin: 2px auto 1px;
    }
    .bill-no {
      font-size: 1.15em;
      font-weight: 700;
      text-align: center;
      margin: 3px 0;
      word-break: break-word;
    }
    .line {
      border-top: 1px dashed #000;
      margin: 6px 0;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    td, th {
      padding: 2px 0;
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
      padding: 3px 0;
      border-bottom: 1px dotted #999;
    }
    .item-meta,
    .total-row {
      display: flex;
      justify-content: space-between;
      gap: 6px;
    }
    .grand {
      border-top: 1px dashed #000;
      padding-top: 5px;
      margin-top: 4px;
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
    <div class="center">
      ${t.shopLogoUrl?`<img class="shop-logo" src="${L(t.shopLogoUrl)}" alt="${L(t.shopName)}" />`:""}
      <div class="title">${c(t.shopName)}</div>
      ${t.locationName?`<div class="muted">${c(t.locationName)}</div>`:""}
      ${t.shopAddress?`<div class="muted">${c(t.shopAddress)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${c(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${t.invoiceNo?`<div class="bill-no">BILL NO: ${c(t.invoiceNo)}</div>`:""}
    <table>
	      ${t.orderNo?`<tr><td>${c(f)}</td><td class="right">${c(t.orderNo)}</td></tr>`:""}
      <tr><td>Date</td><td class="right">${c(X(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${c(t.orderType)}</td></tr>`:""}
	      ${l&&t.tableName?`<tr><td>Table</td><td class="right">${c(t.tableName)}</td></tr>`:""}
	      ${l&&t.guestCount?`<tr><td>Guests</td><td class="right">${c(t.guestCount)}</td></tr>`:""}
	      ${l&&t.tokenNo?`<tr><td>Token</td><td class="right">${c(t.tokenNo)}</td></tr>`:""}
	      ${l&&a.length?`<tr><td>KOT</td><td class="right">${c(a.join(", "))}</td></tr>`:""}
	      ${l&&i.length?`<tr><td>Batch</td><td class="right">${c(i.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${r?`
      <div>
        ${s.length?s.map(y=>`
          <div class="item-block">
            <div class="item-name">${c(y.name)}</div>
            <div class="item-meta">
              <span>${c(I(y.qty))} x ${c(d(y.rate))}</span>
              <strong>${c(d(y.total))}</strong>
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
          ${s.length?s.map(y=>`
            <tr>
              <td class="item-name">${c(y.name)}</td>
              <td class="right">${c(I(y.qty))}</td>
              <td class="right">${c(d(y.rate))}</td>
              <td class="right">${c(d(y.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    <div class="total-row"><span>Subtotal</span><span>${c(d(t.subtotal))}</span></div>
    ${t.discount?`<div class="total-row"><span>Discount</span><span>-${c(d(t.discount))}</span></div>`:""}
    ${t.tax?`<div class="total-row"><span>Tax/GST</span><span>${c(d(t.tax))}</span></div>`:""}
    <div class="total-row grand"><span>TOTAL</span><span>${c(d(t.grandTotal))}</span></div>
    ${t.paidAmount?`<div class="total-row"><span>Paid</span><span>${c(d(t.paidAmount))}</span></div>`:""}
    ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${c(t.paymentMethod)}</span></div>`:""}
    ${h||w?`
      <div class="line"></div>
      <div class="qr-wrap">
        <div class="muted">Scan QR for invoice/review</div>
        ${h||w}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted">
      ${t.paychatLogoUrl?`<img class="paychat-logo" src="${L(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},v=(t,e="-")=>`${e.repeat(t)}
`,b=(t,e)=>{const o=T(t).slice(0,e),n=Math.max(0,Math.floor((e-o.length)/2));return`${" ".repeat(n)}${o}
`},g=(t,e,o)=>{const n=T(e),r=Math.max(1,o-n.length-1),s=T(t).slice(0,r),a=Math.max(1,o-s.length-n.length);return`${s}${" ".repeat(a)}${n}
`},G=(t,e)=>{const o=T(t).split(/\s+/).filter(Boolean).flatMap(s=>s.length<=e?[s]:s.match(new RegExp(`.{1,${e}}`,"g"))||[s]),n=[];let r="";return o.forEach(s=>{if(!r){r=s;return}(r+" "+s).length<=e?r+=` ${s}`:(n.push(r),r=s.slice(0,e))}),r&&n.push(r),n.length?n:[""]},bt=(t,e)=>{const o=G(t.name,e),n=`${I(t.qty)} x ${d(t.rate)}`;return[...o.map(r=>`${r}
`),g(n,d(t.total),e)].join("")},_t=(t,e)=>{const s=e-5-9-10,a=G(t.name,s),i=`${a[0].padEnd(s)}${I(t.qty).padStart(5)}${d(t.rate).padStart(9)}${d(t.total).padStart(10)}
`,m=a.slice(1).map(u=>`${u}
`).join("");return i+m},et=(t,e={})=>{const o=e.paperSize||"80mm",{columns:n}=Z(o),r=o==="58mm",s=Array.isArray(t.items)?t.items:[],a=Array.isArray(t.kotCodes)?t.kotCodes:[],i=Array.isArray(t.batchCodes)?t.batchCodes:[],m=D(),u=t.simpleBilling??m.simpleBilling,l=t.billingLabel||m.billingLabel||"Order",f=r?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`;return[b(t.shopName,n),t.locationName?b(t.locationName,n):"",t.shopPhone?b(`Phone: ${t.shopPhone}`,n):"",t.shopAddress?b(t.shopAddress,n):"",v(n),t.invoiceNo?b(`BILL NO: ${t.invoiceNo}`,n):"",t.orderNo?g(l,t.orderNo,n):"",g("Date",X(t.dateTime),n),t.orderType?g("Type",t.orderType,n):"",!u&&t.tableName?g("Table",t.tableName,n):"",!u&&t.guestCount?g("Guests",t.guestCount,n):"",!u&&t.tokenNo?g("Token",t.tokenNo,n):"",!u&&a.length?g("KOT",a.join(","),n):"",!u&&i.length?g("Batch",i.join(","),n):"",v(n),f,f?v(n):"",s.length?s.map(h=>r?bt(h,n):_t(h,n)).join(""):b("No items",n),v(n),g("Subtotal",d(t.subtotal),n),t.discount?g("Discount",`-${d(t.discount)}`,n):"",t.tax?g("Tax/GST",d(t.tax),n):"",v(n),g("TOTAL",d(t.grandTotal),n),t.paidAmount?g("Paid",d(t.paidAmount),n):"",t.paymentMethod?g("Payment",t.paymentMethod,n):"",t.invoiceUrl?`${v(n)}${b("Invoice/review link",n)}${G(t.invoiceUrl,n).map(h=>`${T(h)}
`).join("")}`:"",v(n),b("Thank you",n),b("Powered by PayChat",n)].join("")},Qt=et,nt="paychat_print_agent_settings",B={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1},vt=8e3,J=12e3,$t=["invoice_url","invoiceUrl","review_url","reviewUrl"],St=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},F=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),xt=t=>t==="80mm"?"80mm":"58mm",wt=t=>t==="pdf"?"pdf":"escpos",_=(t={})=>({...B,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||B.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:xt(t?.paperSize),printMode:wt(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout}),$=()=>typeof localStorage>"u"?{...B}:_(St(localStorage.getItem(nt),{})),kt=(t={})=>{const e=_({...$(),...t});try{localStorage.setItem(nt,JSON.stringify(e))}catch{}return e},R=(t,e="PRINT_AGENT_ERROR",o=null)=>{const n=new Error(t);return n.code=e,o&&(n.cause=o),n},z=(t,e={},o={})=>{const n=_(e),r=new URL(t,`${n.agentUrl}/`),s={token:n.token,size:n.paperSize,printer_name:n.printerName,copies:1,print_mode:n.printMode,...o};return Object.entries(s).forEach(([a,i])=>{i!=null&&i!==""&&r.searchParams.set(a,String(i))}),r.toString()},U=async(t,e={},o=vt)=>{const n=new AbortController,r=setTimeout(()=>n.abort(),o);try{const s=await fetch(t,{...e,signal:n.signal}),i=(s.headers.get("content-type")||"").includes("application/json")?await s.json().catch(()=>null):await s.text().catch(()=>"");if(!s.ok)throw R(i?.message||i?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return i}catch(s){throw s?.name==="AbortError"?R("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",s):s?.code?s:R("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",s)}finally{clearTimeout(r)}},Nt=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},Tt=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),W=(t,e=0,o=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(Tt)?t:[];if(typeof t!="object"||o.has(t))return[];o.add(t);const n=["items","order_items","orderItems","line_items","lineItems","cart"];for(const r of n){const s=W(t[r],e+1,o);if(s.length)return s}for(const r of Object.values(t)){const s=W(r,e+1,o);if(s.length)return s}return[]},N=(...t)=>{for(const e of t){const o=Number(e);if(Number.isFinite(o))return o}return 0},x=(...t)=>{for(const e of t){const o=F(e).trim();if(o)return o}return""},O=(t={})=>{const e=t.invoice||t.invoice_data||{},o=t.data||t.order||{};return Nt(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,e.items,e.order_items,o.items,o.order_items,o.line_items,W(t))},q=(t={})=>x(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),A=(t={})=>N(t.quantity,t.qty,t.pivot?.quantity,1)||1,E=(t={})=>{const e=A(t),o=x(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(o!=="")return Number(o||0);const n=x(t.total,t.line_total,t.amount,t.subtotal);return Number(n||0)/e},ot=(t={})=>{const e=x(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):E(t)*A(t)},At=(t=[])=>t.map(e=>({...e,product_name:q(e),name:q(e),quantity:A(e),qty:A(e),rate:E(e),price:E(e),total:ot(e)})),Lt=(t,e)=>{const o=F(t);if(o.length<=e)return[o];const n=[];for(let r=0;r<o.length;r+=e)n.push(o.slice(r,r+e));return n},Pt=(t,e)=>{const o=e==="80mm"?48:32;return F(t).split(/\r?\n/).flatMap(n=>Lt(n,o)).join(`
`)},It=(t={},e="58mm")=>{const o=e==="80mm"?48:32,n=O(t);return n.length?n.map(r=>{const s=q(r),a=A(r),i=E(r),u=ot(r).toFixed(2),l=`${a} x ${i.toFixed(2)}`,f=Math.max(1,o-l.length-u.length);return`${s}
${l}${" ".repeat(f)}${u}`}).join(`
`):""},qt=(t,e,o)=>{const n=O(e);return!n.length||n.some(s=>{const a=q(s);return a&&t.includes(a.slice(0,Math.min(a.length,12)))})?t:`${t}
${It(e,o)}`},Et=(t,e)=>{if(/total/i.test(t))return t;const o=N(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,O(e).reduce((n,r)=>{const s=N(r.quantity,r.qty,1)||1,a=N(r.rate,r.price,r.unit_price);return n+N(r.total,r.line_total,r.amount,s*a)},0));return`${t}
TOTAL ${o.toFixed(2)}`},Ct=(t={},e={})=>{for(const o of $t){const n=x(t[o],e[o]);if(n)return n}return x(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},zt=t=>{try{const e=new URL(t);return e.protocol==="http:"||e.protocol==="https:"}catch{return!1}},rt=(t={},e={},o=$())=>{const n=_(o),r=n.paperSize,s={...t||{},items:At(O(t||{}))},a=ft(s,e||{});let i=et(a,{paperSize:r});const m=yt(a,{paperSize:r});typeof i!="string"&&(i=String(i??"")),i=qt(i,s,r),i=Et(i,s),i=Pt(i,r),i.length>J&&(i=`${i.slice(0,J)}
--- Receipt truncated ---`),i=i.replace(/\n*$/,`


`);const u=Ct(t,a),l={text:i,html:m,print_mode:n.printMode};return u&&zt(u)&&(l.qr={data:u,size:6,error_correction:"M"}),l},Ut=async(t=$())=>{const e=_(t);return U(z("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Ot=async(t=$())=>{const e=_(t),o=await U(z("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(o)?o:Array.isArray(o?.printers)?o.printers:Array.isArray(o?.data)?o.data:[]},Rt=async(t=$())=>{const e=_(t);return U(z("/test-print",e),{method:"POST"})},jt=async(t={},e={})=>{const o=_(e.settings||$()),n=rt(t,e.context||{},o);return U(z("/print",o),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(n)})},Gt={getSettings:$,saveSettings:kt,checkHealth:Ut,getPrinters:Ot,testPrint:Rt,printReceipt:jt,buildSafeAgentReceiptPayload:rt},Ft={list(t={}){return k.get("/upi-profiles",{params:t})},create(t){return k.post("/upi-profiles",t)},update(t,e){return k.patch(`/upi-profiles/${t}`,e)},deactivate(t){return k.delete(`/upi-profiles/${t}`)},setDefault(t){return k.patch(`/upi-profiles/${t}/default`)}},C="paychat_pos_wake_lock_enabled",V=()=>{try{return localStorage.getItem(C)==="true"}catch{return!1}},Ht=t=>{try{return t?(localStorage.setItem(C,"true"),!0):(localStorage.removeItem(C),!1)}catch{return!1}},Mt=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},Kt=()=>{let t=null,e=!1,o=!1,n=0;const r=async()=>{try{await t?.release?.()}catch(l){console.warn("POS wake lock release failed:",l)}finally{t=null}},s=()=>{const l=Mt();return l.supported?!0:(o||(console.warn(`POS wake lock unavailable: ${l.reason}`),o=!0),!1)},a=async()=>{const l=Date.now();if(!(e||t||!V()||!s()||document.visibilityState!=="visible")&&!(l-n<750)){n=l;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(f){console.warn("POS wake lock failed:",f)}}},i=()=>{a()},m=()=>{document.visibilityState==="visible"?a():r()},u=l=>{l.key===C&&(V()?a():r())};return document.addEventListener("visibilitychange",m),document.addEventListener("pointerdown",i,{passive:!0}),document.addEventListener("touchstart",i,{passive:!0}),document.addEventListener("click",i,{passive:!0}),window.addEventListener("storage",u),a(),()=>{e=!0,document.removeEventListener("visibilitychange",m),document.removeEventListener("pointerdown",i),document.removeEventListener("touchstart",i),document.removeEventListener("click",i),window.removeEventListener("storage",u),r()}},Bt="paychat-pos",P="cache",j=st(Bt,1,{upgrade(t){t.createObjectStore(P)}}),Jt={async set(t,e){await(await j).put(P,e,t)},async get(t){return await(await j).get(P,t)},async clear(){await(await j).clear(P)}};export{yt as a,Qt as b,Jt as c,Ht as d,Mt as e,V as g,ft as n,Gt as p,Kt as s,Ft as u};
