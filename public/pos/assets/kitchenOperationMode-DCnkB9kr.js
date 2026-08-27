import{g as bt}from"./index-Mi8RxirV.js";import{a as Ie,c as Ne}from"./upiProfiles-AMmR2C3j.js";import{Q as $e,D as ve}from"./vendor-qKbVCTru.js";import{c as zt,t as we}from"./usePosInteractionFeedback-CdJ6jujS.js";import{a as F,c as xe}from"./registration-batches-DjBTYa_U.js";import{p as Ee}from"./registration-shell-H1t-sKek.js";import{l as Ae}from"./locationService-BSFQ3mNH.js";import{d as Oe}from"./vendor-vue-DNBbdLq8.js";import{p as ke}from"./productService-CxDtQCS-.js";const Te="/color-paychat-logo-main.svg",Pe="\x1BE",Ce="\x1BE\0",qe="\x1BG",Le="\x1BG\0",De=1,Me=3,wt={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},St=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},j=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},Ft=()=>St(j("tenant_info"),{}),Qt=()=>St(j("selected_location"),{}),Ue=()=>St(j("paychat_offline_mode_cache"),{}),Bt=t=>K(t).replace(/\s+-\s+/g," ").replace(/\s{2,}/g," ").trim(),jt=t=>Bt(t).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim().toLowerCase().replace(/\b[a-z]/g,e=>e.toUpperCase()),Kt=t=>{const e=Bt(t);if(!e)return"";const n=e.split(",").map(s=>s.trim()).filter(Boolean);return(n.length?n.slice(0,2).join(", "):e).slice(0,80)},l=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),w=t=>l(t).replace(/`/g,"&#096;"),K=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),m=t=>Number(t||0).toFixed(2),ot=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},Gt=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},Yt=(t="80mm")=>wt[t]||wt["80mm"],p=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},Re=t=>{const e=String(t||"").trim();if(!e)return"";try{const n=typeof window<"u"?window.location.origin:"https://paychat.local",o=new URL(e,n).pathname.split("/").map(i=>i.trim()).filter(Boolean),r=o.findIndex(i=>["invoice","invoices"].includes(i.toLowerCase())),a=r>=0?o[r+1]:o[o.length-1];return decodeURIComponent(a||"").trim()}catch{const o=e.split("?")[0].split("#")[0].split("/").map(r=>r.trim()).filter(Boolean);return o[o.length-1]||""}},Q=(...t)=>{for(const e of t){if(e==null||e==="")continue;const n=Number(e);if(Number.isFinite(n))return n}return 0},ze=(t="")=>String(t||"").trim().toLowerCase()==="upi",Fe=t=>{const e=Number(t||0);return Number.isFinite(e)?e.toFixed(2):"0.00"},Qe=({upiId:t="",payeeName:e="",amount:n=0,note:s=""}={})=>{const o=String(t||"").trim();return o?`upi://pay?${new URLSearchParams({pa:o,pn:String(e||"POS Store").trim()||"POS Store",am:Fe(n),cu:"INR",tn:String(s||"POS Payment").trim()||"POS Payment"}).toString()}`:""},Be=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},je=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),mt=(t,e=0,n=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(je)?t:[];if(typeof t!="object"||n.has(t))return[];n.add(t);const s=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const o of s){const r=mt(t[o],e+1,n);if(r.length)return r}for(const o of Object.values(t)){const r=mt(o,e+1,n);if(r.length)return r}return[]},Ke=(t={})=>p(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),It=(t={})=>Q(t.quantity,t.qty,t.pivot?.quantity,1)||1,Wt=(t={})=>{const e=It(t),n=p(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(n!=="")return Number(n||0);const s=p(t.total,t.line_total,t.amount,t.subtotal);return Number(s||0)/e},Ge=(t={})=>{const e=p(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):Wt(t)*It(t)},Ye=(t={})=>{const e=t.invoice||t.invoice_data||{},n=t.data||t.order||{};return Be(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,n.items,n.order_items,n.line_items,n.cart_items,n.invoice_items,n.details,mt(t))},Jt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return p(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},xt=(...t)=>{const e=[];return t.flat().forEach(n=>{if(!n)return;if(typeof n=="string"||typeof n=="number"){e.push(String(n));return}const s=p(n.code,n.kot_code,n.batch_code,n.token_code,n.id);s&&e.push(String(s))}),[...new Set(e)]},We=t=>{let e=String(t||"").trim();if(!e)return"";if(e.startsWith('"')&&e.endsWith('"'))try{e=JSON.parse(e)}catch{}if(/&lt;\s*(?:svg|img)\b/i.test(e)&&(e=e.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&quot;/gi,'"').replace(/&#0?39;/gi,"'").replace(/&amp;/gi,"&")),!/<(?:svg|img)\b/i.test(e)&&/^[a-z0-9+/=\s]+$/i.test(e))try{const n=typeof atob=="function"?atob(e.replace(/\s+/g,"")):"";/<(?:svg|img)\b/i.test(n)&&(e=n)}catch{}return e.trim()},Je=(t="")=>/^(data:image\/|\/)/i.test(t)||/^https?:\/\/.+\.(?:png|jpe?g|gif|webp|svg)(?:[?#].*)?$/i.test(t),He=(t="")=>{try{const e=$e.create(t,{errorCorrectionLevel:"M"}),n=4,s=3,o=e.modules.size,r=(o+s*2)*n,a=[];for(let c=0;c<o;c+=1)for(let d=0;d<o;d+=1)e.modules.get(c,d)&&a.push(`<rect x="${(d+s)*n}" y="${(c+s)*n}" width="${n}" height="${n}"/>`);const i=`<svg xmlns="http://www.w3.org/2000/svg" width="${r}" height="${r}" viewBox="0 0 ${r} ${r}"><rect width="100%" height="100%" fill="#fff"/><g fill="#000">${a.join("")}</g></svg>`;return`data:image/svg+xml;charset=utf-8,${encodeURIComponent(i)}`}catch{return""}},Et=(t,e="Invoice QR")=>{if(!t)return"";const n=We(t),s=/upi payment/i.test(e),o=n.match(/<svg\b[\s\S]*?<\/svg>/i);if(o){const a=`data:image/svg+xml;charset=utf-8,${encodeURIComponent(o[0])}`;return`<img class="qr-image" src="${w(a)}" alt="${w(e)}" />`}const r=n.match(/<img\b[^>]*\bsrc\s*=\s*["']([^"']+)["'][^>]*>/i);if(r?.[1])return`<img class="qr-image" src="${w(r[1])}" alt="${w(e)}" />`;if(s&&!Je(n)){const a=He(n);if(a)return`<img class="qr-image" src="${w(a)}" alt="${w(e)}" />`}return/^(data:image\/|https?:\/\/|\/)/i.test(n)?`<img class="qr-image" src="${w(n)}" alt="${w(e)}" />`:`<div class="qr-url">${l(n)}</div>`},Ve=(t={})=>{const n=(Array.isArray(t.payments)?t.payments:[]).find(s=>String(s?.payment_method||s?.method||"").toLowerCase()==="upi"&&p(s.upi_qr_url,s.upiQrUrl,s.upi_qr_string,s.upi_payment_link,s.meta?.upi_qr_url,s.meta?.upi_qr_string,s.meta?.upi_payment_link,s.qr_payload,s.qr));return p(t.upi_qr_url,t.upiQrUrl,t.upi_qr_string,t.upi_payment_link,t.payment?.upi_qr_url,t.payment?.upiQrUrl,t.payment?.upi_qr_string,t.payment?.upi_payment_link,t.payment?.meta?.upi_qr_url,t.payment?.meta?.upi_qr_string,t.payment?.meta?.upi_payment_link,t.qr?.upi_qr_url,t.qr?.upi_qr_string,t.qr?.upi_payment_link,t.receipt?.qr?.upi_qr_url,t.receipt?.qr?.upi_qr_string,t.receipt?.qr?.upi_payment_link,n?.upi_qr_url,n?.upiQrUrl,n?.upi_qr_string,n?.upi_payment_link,n?.meta?.upi_qr_url,n?.meta?.upi_qr_string,n?.meta?.upi_payment_link,n?.qr_payload,n?.qr)},Xe=(t={},e={})=>{if(!ze(e.paymentMethod||Jt(t)))return"";const n=Ft(),s=n?.tenant||{},o=n?.branding||s?.branding||n?.branching||{},r={...Qt(),...t.location&&typeof t.location=="object"?t.location:{}},a=Ue(),i=Array.isArray(a.upiProfiles)?a.upiProfiles:[],c=p(t.payment?.upi_profile_id,t.upi_profile_id,t.selected_upi_profile_id,t.payment?.selected_upi_profile_id),u=(c?i.find(g=>String(g?.id||g?.uuid||g?.profile_id||"")===String(c)):null)||Ie(i,r.id),I=p(Ne(u||{}),t.payment?.upi_id,t.upi_id,j("owner_upi_id"),j("static_upi_id"),o.upi_id,s.upi_id);return Qe({upiId:I,payeeName:p(u?.payee_name,u?.payeeName,o.company_name,s.name,e.shopName,"POS Store"),amount:e.grandTotal,note:e.invoiceNo?`Invoice ${e.invoiceNo}`:"POS Payment"})},Ze=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const n=Ft(),s=bt(),o=n?.tenant||{},r=n?.branding||o?.branding||n?.branching||{},a=Qt(),i={...a&&typeof a=="object"?a:{},...t.location&&typeof t.location=="object"?t.location:{}};t.branch||t.branching||t.branding||n?.branch||n?.branching;const c=t.merchant||t.receipt?.merchant||{},d=t.invoice||t.invoice_data||t.receipt?.invoice||{},u=t.qr||t.receipt?.qr||{},I=p(e.invoiceUrl,t.invoice_url,t.invoiceUrl,d.url,t.meta?.invoice?.url,u.invoice_url),g=Ye(t).map(v=>({name:Ke(v),qty:It(v),rate:Wt(v),total:Ge(v)})),y=Q(t.subtotal,t.totals?.subtotal,g.reduce((v,b)=>v+b.rate*b.qty,0)),M=Q(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),C=Q(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),$=Q(t.total,t.grand_total,t.totals?.grand_total,y+C-M),U=Jt(t),O={paymentMethod:U,grandTotal:$,shopName:p(e.shopName,c.name,i.tenant?.name,t.tenant?.name,r.company_name,o.name,j("tenant_slug"),"PayChat POS"),invoiceNo:p(e.invoiceNo,t.invoice_no,t.invoiceNo,d.number,d.invoice_no,d.invoiceNo,d.invoice_number,d.offline_invoice_number,t.meta?.invoice?.number,t.meta?.invoice?.invoice_no,t.meta?.invoice?.invoiceNo,t.meta?.invoice?.invoice_number,t.offline_invoice_number,t.local_invoice_no,Re(I))},k=p(e.upiQr,e.paymentQr,Ve(t),Xe(t,O));return{shopName:O.shopName,shopPhone:p(e.shopPhone,c.phone,i.phone,r.phone,o.phone),shopAddress:p(e.shopAddress,r.address,o.branding?.address,t.tenant?.branding?.address,i.tenant?.branding?.address),shopLogoUrl:p(e.shopLogoUrl,i.logo,i.tenant?.logo,t.tenant?.logo,r.logo,o.logo),locationName:p(i.name,t.location_name),paychatLogoUrl:p(e.paychatLogoUrl,t.paychat_logo_url,Te),invoiceNo:O.invoiceNo,orderNo:p(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:p(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:p(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:p(t.table_display,t.tableDisplay,t.table_session?.table_display,t.tableSession?.tableDisplay,t.table_session?.table?.name,t.tableSession?.table?.name,t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:p(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:p(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:xt(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:xt(t.batch_codes,t.batchCodes),items:g,subtotal:y,discount:M,tax:C,grandTotal:$,paidAmount:Q(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,$),paymentMethod:U,invoiceUrl:I,upiQr:k,invoiceQr:p(e.invoiceQr,t.invoice_qr,t.invoiceQr,u.qr_svg_or_url,t.qr),reviewQr:p(e.reviewQr,t.review_qr,t.reviewQr),notes:p(t.print_note,t.note),simpleBilling:s.simpleBilling,billingLabel:s.billingLabel}},tn=(t,e={})=>{const n=e.paperSize||"80mm",s=Yt(n),o=n==="58mm",r=e.agentPdf===!0,a=e.customPrintInvoice===!0,i=e.hideInvoiceQr===!0,c=Array.isArray(t.items)?t.items:[],d=Array.isArray(t.kotCodes)?t.kotCodes:[],u=Array.isArray(t.batchCodes)?t.batchCodes:[],I=bt(),y=!(t.simpleBilling??I.simpleBilling),M=a?jt(t.shopName):t.shopName,C=a?Kt(t.shopAddress):t.shopAddress,$=p(t.invoiceNo),U=a?o?"48px":"64px":s.paychatLogoWidth,O=!i&&t.upiQr?Et(t.upiQr,"UPI payment QR"):"",k=!i&&!O?Et(t.invoiceQr||t.reviewQr):"",v=!t.upiQr&&t.invoiceUrl&&(i||!k)?`<div class="qr-url">${l(t.invoiceUrl)}</div>`:"";return`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Thermal Bill</title>
  <style>
    @page { size: ${s.width} auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0 0 ${a?"18px":"0"};
      background: #fff;
      color: #000;
      font-family: "Courier New", monospace;
      font-size: ${s.fontSize};
      line-height: ${a?"1.08":"1.28"};
    }
    .receipt {
      width: ${s.width};
      padding: ${a?"2px 4px 14px":s.padding};
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { font-size: 0.88em; }
    .powered { font-size: ${a?"0.72em":"0.88em"}; }
    .title {
      color: #000;
      font-size: ${a?o?"15px":"18px":s.titleSize};
      font-weight: ${a?"900":"800"};
      text-transform: ${a?"none":"uppercase"};
      ${a?"text-shadow: 0 0 0 #000, 0.25px 0 #000, -0.25px 0 #000; -webkit-text-stroke: 0.25px #000;":""}
      word-break: break-word;
    }
    .shop-logo {
      display: block;
      max-width: ${s.logoMaxWidth};
      max-height: ${o?"54px":"74px"};
      object-fit: contain;
      margin: 0 auto 4px;
    }
    .paychat-logo {
      display: block;
      max-width: ${U};
      max-height: ${o?"20px":"26px"};
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
      font-size: ${o?"1.55em":"1.75em"};
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
    ${a&&t.tokenNo?`<div class="top-token">TOKEN ${l(t.tokenNo)}</div>`:""}
    <div class="center">
      ${!a&&t.shopLogoUrl?`<img class="shop-logo" src="${w(t.shopLogoUrl)}" alt="${w(M)}" />`:""}
      <div class="title">${l(M)}</div>
      ${!a&&t.locationName?`<div class="muted">${l(t.locationName)}</div>`:""}
      ${C?`<div class="muted">${l(C)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${l(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${$&&!a?`<div class="bill-no">INVOICE NO: ${l($)}</div>`:""}
    <table>
	      ${$&&a?`<tr class="bill-no-row"><td><strong>Invoice No</strong></td><td class="right"><strong>${l($)}</strong></td></tr>`:""}
      <tr><td>Date</td><td class="right">${l(Gt(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${l(t.orderType)}</td></tr>`:""}
	      ${(y||a)&&t.tableName?`<tr><td>Table</td><td class="right">${l(t.tableName)}</td></tr>`:""}
	      ${y&&t.guestCount&&!a?`<tr><td>Guests</td><td class="right">${l(t.guestCount)}</td></tr>`:""}
	      ${y&&t.tokenNo&&!a?`<tr><td>Token</td><td class="right">${l(t.tokenNo)}</td></tr>`:""}
	      ${y&&d.length?`<tr><td>KOT</td><td class="right">${l(d.join(", "))}</td></tr>`:""}
	      ${y&&u.length?`<tr><td>Batch</td><td class="right">${l(u.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${o?`
      <div>
        ${c.length?c.map(b=>`
          <div class="item-block">
            <div class="item-name">${l(b.name)}</div>
            <div class="item-meta">
              <span>${l(ot(b.qty))} x ${l(m(b.rate))}</span>
              <strong>${l(m(b.total))}</strong>
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
              <td class="item-name">${l(b.name)}</td>
              <td class="right">${l(ot(b.qty))}</td>
              <td class="right">${l(m(b.rate))}</td>
              <td class="right">${l(m(b.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    ${a?`
      <div class="total-row grand"><span>TOTAL</span><span>${l(m(t.grandTotal))}</span></div>
      ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${l(t.paymentMethod)}</span></div>`:""}
    `:r?`
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
    ${k||v?`
      <div class="line"></div>
      <div class="qr-wrap">
        ${!i&&k?'<div class="muted">Scan QR for invoice/review</div>':'<div class="muted">Invoice link</div>'}
        ${k||v}
      </div>
    `:""}
    ${O?`
      <div class="line"></div>
      <div class="qr-wrap">
        <div class="muted">Scan QR to pay via UPI</div>
        ${O}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted powered">
      ${t.paychatLogoUrl&&!a?`<img class="paychat-logo" src="${w(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},x=(t,e="-")=>`${e.repeat(t)}
`,Ht=(t="")=>`${Pe}${qe}${t}${Le}${Ce}`,en=(t="")=>Ht(t),E=(t,e)=>{const n=K(t).slice(0,e),s=Math.max(0,Math.floor((e-n.length)/2));return`${" ".repeat(s)}${n}
`},f=(t,e,n)=>{const s=K(e),o=Math.max(1,n-s.length-1),r=K(t).slice(0,o),a=Math.max(1,n-r.length-s.length);return`${r}${" ".repeat(a)}${s}
`},at=(t,e)=>{const n=K(t).split(/\s+/).filter(Boolean).flatMap(r=>r.length<=e?[r]:r.match(new RegExp(`.{1,${e}}`,"g"))||[r]),s=[];let o="";return n.forEach(r=>{if(!o){o=r;return}(o+" "+r).length<=e?o+=` ${r}`:(s.push(o),o=r.slice(0,e))}),o&&s.push(o),s.length?s:[""]},nn=(t,e)=>{const n=at(t.name,e),s=`${ot(t.qty)} x ${m(t.rate)}`;return[...n.map(o=>`${o}
`),f(s,m(t.total),e)].join("")},sn=(t,e)=>{const r=e-5-9-10,a=at(t.name,r),i=`${a[0].padEnd(r)}${ot(t.qty).padStart(5)}${m(t.rate).padStart(9)}${m(t.total).padStart(10)}
`,c=a.slice(1).map(d=>`${d}
`).join("");return i+c},Vt=(t,e={})=>{const n=e.paperSize||"80mm",{columns:s}=Yt(n),o=n==="58mm",r=e.customPrintInvoice===!0,a=e.hideInvoiceQr===!0,i=e.escposCommands===!0,c=Array.isArray(t.items)?t.items:[],d=Array.isArray(t.kotCodes)?t.kotCodes:[],u=Array.isArray(t.batchCodes)?t.batchCodes:[],I=bt(),g=t.simpleBilling??I.simpleBilling,y=o?"":`${"Item".padEnd(s-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`,M=r?jt(t.shopName):t.shopName,C=r?Kt(t.shopAddress):t.shopAddress,$=p(t.invoiceNo),U=E(M,s),O=C?at(C,s).map(z=>E(z,s)).join(""):"",k=$?f("Invoice No",$,s):"",v=r&&t.tokenNo?`${x(s)}${E(`TOKEN ${t.tokenNo}`,s)}${x(s)}`:"",b=!t.upiQr&&t.invoiceUrl?`${x(s)}${E(a?"Invoice link":"Invoice/review link",s)}${at(t.invoiceUrl,s).map(z=>`${K(z)}
`).join("")}`:"",Se=t.upiQr&&!a?`${x(s)}${E("Scan QR to pay via UPI",s)}`:"";return[v,i?en(U):U,!r&&t.locationName?E(t.locationName,s):"",O,t.shopPhone?E(`Phone: ${t.shopPhone}`,s):"",x(s),i?Ht(k):k,f("Date",Gt(t.dateTime),s),t.orderType?f("Type",t.orderType,s):"",(!g||r)&&t.tableName?f("Table",t.tableName,s):"",!g&&t.guestCount&&!r?f("Guests",t.guestCount,s):"",!g&&t.tokenNo&&!r?f("Token",t.tokenNo,s):"",!g&&d.length?f("KOT",d.join(","),s):"",!g&&u.length?f("Batch",u.join(","),s):"",x(s),y,y?x(s):"",c.length?c.map(z=>o?nn(z,s):sn(z,s)).join(""):E("No items",s),x(s),r?"":f("Subtotal",m(t.subtotal),s),!r&&t.discount?f("Discount",`-${m(t.discount)}`,s):"",!r&&t.tax?f("Tax/GST",m(t.tax),s):"",r?"":x(s),f("TOTAL",m(t.grandTotal),s),t.paidAmount&&!r?f("Paid",m(t.paidAmount),s):"",t.paymentMethod?f("Payment",t.paymentMethod,s):"",Se,b,x(s),E("Thank you",s),E("Powered by PayChat",s),...Array(r?Me:De).fill(`
`)].join("")},ks=Vt,Xt="\x1BE",Zt="\x1BE\0",on="\x1Ba\0",an="\x1Ba",rn="!",cn="!\0",ln=1,dn=3,h=(t="")=>String(t??"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,"").replace(/\s+/g," ").trim(),S=(...t)=>{for(const e of t){const n=h(e);if(n)return n}return""},pn=(t="58mm")=>t==="80mm"?48:32,G=(t,e="-")=>e.repeat(t),H=(t,e=!0)=>e?`${Xt}${t}${Zt}`:t,un=(t,e=!0)=>e?`${rn}${Xt}${t}${Zt}${cn}`:t,mn=(t,e)=>{const n=h(t),s=Math.max(0,Math.floor((e-n.length)/2));return`${" ".repeat(s)}${n}`},pt=(t,e,n=!0)=>n?`${an}${t}${on}`:mn(t,e),Y=(t,e,n)=>{const s=h(t),o=h(e),r=Math.max(1,n-s.length-o.length);return`${s}${" ".repeat(r)}${o}`},Nt=(t,e,n="")=>{const s=h(t);if(!s)return[];const o=Math.max(8,e-n.length),r=[],a=s.split(" ");let i="";return a.forEach(c=>{if(!i){i=c;return}if(`${i} ${c}`.length<=o){i=`${i} ${c}`;return}r.push(i),i=c}),i&&r.push(i),r.flatMap(c=>{if(c.length<=o)return[`${n}${c}`];const d=[];for(let u=0;u<c.length;u+=o)d.push(`${n}${c.slice(u,u+o)}`);return d})},et=(t={})=>t&&typeof t=="object"?S(t.table_display,t.tableDisplay,t.name,t.code,t.table_name,t.tableName):"",_n=(t={})=>{const e=[t,t.order,t.table_session,t.tableSession,t.order?.table_session,t.order?.tableSession,t.table,t.order?.table].filter(Boolean);for(const n of e){const s=S(n.table_display,n.tableDisplay,n.table_group_label,n.tableGroupLabel);if(s)return s}for(const n of e){const o=(Array.isArray(n.tables)?n.tables:[]).map(et).filter(Boolean);if(o.length)return o.join(" + ")}for(const n of e){const s=Array.isArray(n.linked_tables)?n.linked_tables:Array.isArray(n.linkedTables)?n.linkedTables:[],o=[et(n.primary_table||n.primaryTable),et(n.table),...s.map(et)].filter(Boolean);if(o.length)return[...new Set(o)].join(" + ")}for(const n of e){const s=S(n.table_name,n.tableName,n.name,n.code);if(s)return s}return""},te=(t={})=>S(t.product_name,t.name,t.product?.name,t.item_name,"Item"),ee=(t={})=>{const e=Number(t.quantity??t.qty??1);return Number.isFinite(e)&&e>0?e:1},ne=t=>Number.isInteger(t)?String(t):String(t).replace(/\.0+$/,""),gn=(t={},e)=>[S(t.variant,t.variant_name),...Array.isArray(t.modifiers)?t.modifiers.map(s=>S(s.name,s.label,s)):[],S(t.notes,t.note,t.kitchen_note,t.instructions)].filter(Boolean).flatMap(s=>Nt(s,e,"  - ")),se=(t={})=>{const e=t.print_data||t.printData||t.batch||t,n=S(e.batch_code,e.batchCode,e.code,`KOT-${e.id||e.batch_id||""}`);return{outlet:S(e.outlet,e.store_name,e.location?.name,e.location_name),code:n,tokenNo:S(e.token_no,e.tokenNo,e.token_number,e.tokenNumber,e.token?.token_code,e.token?.token_no,e.order?.token?.token_code,e.order?.token_no,n),orderNo:S(e.order?.order_no,e.order_no,e.orderNo,e.order?.id,e.order_id),table:_n(e),status:S(e.status,"waiting"),time:S(e.sent_at,e.created_at,new Date().toISOString()),orderNotes:S(e.order?.notes,e.notes,e.table_notes),items:Array.isArray(e.items)?e.items:[]}},fn=(t={},e,n=!0)=>{const o=`${ne(ee(t))} x`,r=" ".repeat(Math.min(7,o.length+2)),a=Nt(te(t),e-r.length);return a.length?[`${H(o.padEnd(r.length-1),n)} ${a[0].trim()}`,...a.slice(1).map(i=>`${r}${i.trim()}`)]:[H(o,n)]},hn=(t={},e={})=>{const n=e.paperSize||"58mm",s=pn(n),o=e.escposCommands===!0,r=se(t),a=[];return r.outlet&&a.push(pt(H(r.outlet.toUpperCase(),o),s,o)),a.push(pt(H("KITCHEN ORDER TOKEN",o),s,o)),a.push(G(s)),a.push(pt(un(`TOKEN ${r.tokenNo||r.code}`,o),s,o)),a.push(G(s)),a.push(Y("KOT",r.code,s)),r.orderNo&&a.push(Y("Order",r.orderNo,s)),r.table&&a.push(Y("Table",r.table,s)),a.push(Y("Status",r.status,s)),a.push(Y("Time",r.time.replace("T"," ").slice(0,16),s)),a.push(G(s)),r.items.forEach(i=>{a.push(...fn(i,s,o)),a.push(...gn(i,s)),a.push(...Array(ln).fill(""))}),r.orderNotes&&(a.push(G(s)),a.push(H("Notes",o)),a.push(...Nt(r.orderNotes,s))),a.push(G(s)),a.push(...Array(dn).fill("")),a.join(`
`)},yn=(t={})=>{const e=se(t),n=e.items.map(s=>`
    <div class="item">
      <div class="qty">${h(ne(ee(s)))} x</div>
      <div class="name">${h(te(s))}</div>
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
  ${e.outlet?`<h1>${h(e.outlet).toUpperCase()}</h1>`:""}
  <h2>KITCHEN ORDER TOKEN</h2>
  <div class="line"></div>
  <div class="token">TOKEN ${h(e.tokenNo||e.code)}</div>
  <div class="line"></div>
  <p class="meta"><strong>KOT</strong><span>${h(e.code)}</span></p>
  ${e.orderNo?`<p class="meta"><strong>Order</strong><span>${h(e.orderNo)}</span></p>`:""}
  ${e.table?`<p class="meta"><strong>Table</strong><span>${h(e.table)}</span></p>`:""}
  <p class="meta"><strong>Status</strong><span>${h(e.status)}</span></p>
  <p class="meta"><strong>Time</strong><span>${h(e.time.replace("T"," ").slice(0,16))}</span></p>
  <div class="line"></div>
  <div class="items">${n}</div>
  ${e.orderNotes?`<div class="line"></div><p class="notes"><strong>Notes:</strong> ${h(e.orderNotes)}</p>`:""}
  <div class="line"></div>
</body>
</html>`},bn=(t={},e={})=>({text:hn(t,e),html:yn(t),print_mode:e.printMode||"escpos"}),oe="paychat_print_agent_settings",_t={enabled:!1,agentUrl:"http://127.0.0.1:8787",token:"",printerName:"",paperSize:"58mm",printMode:"escpos",autoPrintAfterCheckout:!1,customPrintInvoice:!1,hideInvoiceQr:!1},Sn=8e3,At=12e3,In=1,Nn=3,$n=["invoice_url","invoiceUrl","review_url","reviewUrl"],vn=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},$t=(t="")=>String(t??"").replace(/\u20b9\s*/g,"Rs. ").replace(/\b(undefined|null|NaN|Infinity)\b/g,"").replace(/[^\x09\x0A\x0D\x20-\x7E]/g,""),wn=t=>t==="80mm"?"80mm":"58mm",xn=t=>t==="pdf"?"pdf":"escpos",P=(t={})=>({..._t,...t&&typeof t=="object"?t:{},enabled:!!t?.enabled,agentUrl:String(t?.agentUrl||_t.agentUrl).replace(/\/+$/,""),token:String(t?.token||""),printerName:String(t?.printerName||""),paperSize:wn(t?.paperSize),printMode:xn(t?.printMode),autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,customPrintInvoice:!!t?.customPrintInvoice,hideInvoiceQr:!!t?.hideInvoiceQr}),q=()=>typeof localStorage>"u"?{..._t}:P(vn(localStorage.getItem(oe),{})),En=(t={})=>{const e=P({...q(),...t});try{localStorage.setItem(oe,JSON.stringify(e))}catch{}return e},ut=(t,e="PRINT_AGENT_ERROR",n=null)=>{const s=new Error(t);return s.code=e,n&&(s.cause=n),s},Z=(t,e={},n={})=>{const s=P(e),o=new URL(t,`${s.agentUrl}/`),r={token:s.token,size:s.paperSize,printer_name:s.printerName,copies:1,print_mode:s.printMode,...n};return Object.entries(r).forEach(([a,i])=>{i!=null&&i!==""&&o.searchParams.set(a,String(i))}),o.toString()},tt=async(t,e={},n=Sn)=>{const s=new AbortController,o=setTimeout(()=>s.abort(),n);try{const r=await fetch(t,{...e,signal:s.signal}),i=(r.headers.get("content-type")||"").includes("application/json")?await r.json().catch(()=>null):await r.text().catch(()=>"");if(!r.ok)throw ut(i?.message||i?.error||"PayChat Print Agent request failed.","PRINT_AGENT_BAD_RESPONSE");return i}catch(r){throw r?.name==="AbortError"?ut("PayChat Print Agent did not respond in time.","PRINT_AGENT_TIMEOUT",r):r?.code?r:ut("PayChat Print Agent is not running on this device.","PRINT_AGENT_UNAVAILABLE",r)}finally{clearTimeout(o)}},An=(...t)=>{for(const e of t)if(Array.isArray(e)&&e.length)return e;return[]},On=(t={})=>!t||typeof t!="object"?!1:!!(t.product||t.menu_item||t.product_snapshot||t.product_name||t.item_name||t.menu_item_name||t.name||t.title||t.quantity||t.qty||t.price||t.rate||t.unit_price||t.total||t.line_total||t.amount),gt=(t,e=0,n=new Set)=>{if(!t||e>4)return[];if(Array.isArray(t))return t.some(On)?t:[];if(typeof t!="object"||n.has(t))return[];n.add(t);const s=["items","order_items","orderItems","line_items","lineItems","cart","cart_items","cartItems","invoice_items","invoiceItems","bill_items","billItems","details","order_details","orderDetails"];for(const o of s){const r=gt(t[o],e+1,n);if(r.length)return r}for(const o of Object.values(t)){const r=gt(o,e+1,n);if(r.length)return r}return[]},J=(...t)=>{for(const e of t){const n=Number(e);if(Number.isFinite(n))return n}return 0},A=(...t)=>{for(const e of t){const n=$t(e).trim();if(n)return n}return""},ct=(t={})=>{const e=t.invoice||t.invoice_data||{},n=t.data||t.order||{};return An(t.items,t.order_items,t.orderItems,t.line_items,t.lineItems,t.cart,t.cart_items,t.cartItems,t.invoice_items,t.invoiceItems,t.bill_items,t.billItems,t.details,t.order_details,t.orderDetails,e.items,e.order_items,e.line_items,e.invoice_items,e.details,n.items,n.order_items,n.line_items,n.cart_items,n.invoice_items,n.details,gt(t))},rt=(t={})=>A(t.product?.name,t.menu_item?.name,t.product_snapshot?.name,t.product_name,t.item_name,t.menu_item_name,t.name,t.title,t.description,"Item"),V=(t={})=>J(t.quantity,t.qty,t.pivot?.quantity,1)||1,it=(t={})=>{const e=V(t),n=A(t.rate,t.price,t.unit_price,t.unitPrice,t.product?.price);if(n!=="")return Number(n||0);const s=A(t.total,t.line_total,t.amount,t.subtotal);return Number(s||0)/e},ae=(t={})=>{const e=A(t.total,t.line_total,t.amount,t.subtotal);return e!==""?Number(e||0):it(t)*V(t)},kn=(t=[])=>t.map(e=>({...e,product_name:rt(e),name:rt(e),quantity:V(e),qty:V(e),rate:it(e),price:it(e),total:ae(e)})),Tn=(t,e)=>{const n=$t(t);if(n.length<=e)return[n];const s=[];for(let o=0;o<n.length;o+=e)s.push(n.slice(o,o+e));return s},Pn=(t,e)=>{const n=e==="80mm"?48:32;return $t(t).split(/\r?\n/).flatMap(s=>Tn(s,n)).join(`
`)},Cn=(t={},e="58mm")=>{const n=e==="80mm"?48:32,s=ct(t);return s.length?s.map(o=>{const r=rt(o),a=V(o),i=it(o),d=ae(o).toFixed(2),u=`${a} x ${i.toFixed(2)}`,I=Math.max(1,n-u.length-d.length);return`${r}
${u}${" ".repeat(I)}${d}`}).join(`
`):""},qn=(t,e,n)=>{const s=ct(e);return!s.length||s.some(r=>{const a=rt(r);return a&&t.includes(a.slice(0,Math.min(a.length,12)))})?t:`${t}
${Cn(e,n)}`},Ln=(t,e)=>{if(/total/i.test(t))return t;const n=J(e.total,e.grand_total,e.payable_amount,e.totals?.grand_total,ct(e).reduce((s,o)=>{const r=J(o.quantity,o.qty,1)||1,a=J(o.rate,o.price,o.unit_price);return s+J(o.total,o.line_total,o.amount,r*a)},0));return`${t}
TOTAL ${n.toFixed(2)}`},Ot=t=>`\x1BE${t}\x1BE\0`,Dn=(t="",e={})=>{const n=A(e.shopName).replace(/[-_]+/g," ").replace(/\s{2,}/g," ").trim();return String(t||"").split(`
`).map(s=>{const o=s.trim();return o&&(n&&o.toLowerCase()===n.toLowerCase()||/^invoice no\b/i.test(o)||/^total\b/i.test(o))?Ot(s):s}).join(`
`)},Mn=(t={},e={})=>{for(const n of $n){const s=A(t[n],e[n]);if(s)return s}return A(e.invoiceUrl,e.reviewUrl,t.invoice?.url,t.meta?.invoice?.url)},Un=(t={},e={})=>{const s=(Array.isArray(t.payments)?t.payments:[]).find(o=>String(o?.payment_method||o?.method||"").toLowerCase()==="upi"&&A(o.upi_qr_url,o.upiQrUrl,o.upi_qr_string,o.upi_payment_link,o.meta?.upi_qr_url,o.meta?.upi_qr_string,o.meta?.upi_payment_link,o.qr_payload,o.qr));return A(e.upiQr,e.paymentQr,t.upi_qr_url,t.upiQrUrl,t.upi_qr_string,t.upi_payment_link,t.payment?.upi_qr_url,t.payment?.upiQrUrl,t.payment?.upi_qr_string,t.payment?.upi_payment_link,t.payment?.meta?.upi_qr_url,t.payment?.meta?.upi_qr_string,t.payment?.meta?.upi_payment_link,t.qr?.upi_qr_url,t.qr?.upi_qr_string,t.qr?.upi_payment_link,t.receipt?.qr?.upi_qr_url,t.receipt?.qr?.upi_qr_string,t.receipt?.qr?.upi_payment_link,s?.upi_qr_url,s?.upiQrUrl,s?.upi_qr_string,s?.upi_payment_link,s?.meta?.upi_qr_url,s?.meta?.upi_qr_string,s?.meta?.upi_payment_link,s?.qr_payload,s?.qr)},Rn=t=>{try{const e=new URL(t);return["http:","https:","upi:"].includes(e.protocol)}catch{return A(t)!==""}},re=(t={},e={},n=q())=>{const s=P(n),o=s.paperSize,r={...t||{},items:kn(ct(t||{}))},a=Ze(r,e||{}),i={paperSize:o,customPrintInvoice:s.customPrintInvoice,hideInvoiceQr:s.hideInvoiceQr,escposCommands:s.printMode==="escpos"&&o!=="80mm"};let c=Vt(a,i);const d=tn(a,{...i,agentPdf:s.printMode==="pdf"});typeof c!="string"&&(c=String(c??"")),c=qn(c,r,o),c=Ln(c,r),c=Pn(c,o),s.customPrintInvoice&&o!=="80mm"&&(c=Dn(c,a)),c.length>At&&(c=`${c.slice(0,At)}
--- Receipt truncated ---`),c=c.replace(/\n*$/,`
`.repeat(s.customPrintInvoice?Nn:In));const u=Un(t,a),I=Mn(t,a),g=u||I,y={text:c,html:d,print_mode:s.printMode};return!s.hideInvoiceQr&&g&&Rn(g)&&(y.qr={data:g,size:6,error_correction:"M"}),y},zn=async(t=q())=>{const e=P(t);return tt(Z("/health",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"})},Fn=async(t=q())=>{const e=P(t),n=await tt(Z("/printers",e,{size:void 0,printer_name:void 0,copies:void 0,print_mode:void 0}),{method:"GET"});return Array.isArray(n)?n:Array.isArray(n?.printers)?n.printers:Array.isArray(n?.data)?n.data:[]},Qn=async(t=q())=>{const e=P(t);return tt(Z("/test-print",e),{method:"POST"})},Bn=async(t={},e={})=>{const n=P(e.settings||q()),s=re(t,e.context||{},n);return tt(Z("/print",n),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(s)})},jn=async(t={},e={})=>{const n=P(e.settings||q()),s=bn(t,{paperSize:n.paperSize,printMode:n.printMode,escposCommands:n.printMode==="escpos"});return tt(Z("/print",n),{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify(s)})},Ts={getSettings:q,saveSettings:En,checkHealth:zn,getPrinters:Fn,testPrint:Qn,printReceipt:Bn,printKot:jn,buildSafeAgentReceiptPayload:re},Kn={list(t={}){return F.get("/upi-profiles",{params:t})},create(t){return F.post("/upi-profiles",t)},update(t,e){return F.patch(`/upi-profiles/${t}`,e)},deactivate(t){return F.delete(`/upi-profiles/${t}`)},setDefault(t){return F.patch(`/upi-profiles/${t}/default`)}},kt="paychat_lightning_catalog_products",Tt="paychat_lightning_catalog_categories",nt="paychat_lightning_catalog_updated_at",Pt=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},Ct=t=>{const e=t&&t.data?t.data:t;return Array.isArray(e)?e:e&&Array.isArray(e.data)?e.data:e&&e.data&&Array.isArray(e.data.data)?e.data.data:e&&Array.isArray(e.products)?e.products:e&&e.data&&Array.isArray(e.data.products)?e.data.products:e&&Array.isArray(e.categories)?e.categories:[]},Gn=(t={})=>t.category_id||t.categoryId||t.category?.id||t.categories?.[0]?.id||t.product_category_id||t.pivot?.category_id||null,Yn=(t={})=>t.category_name||t.category?.name||t.categories?.[0]?.name||t.category||t.product_category||"",qt=(t={})=>{const e=Gn(t),n=Yn(t);return{...t,id:t.id||t.product_id||t.sku||t.barcode||t.name,name:t.name||t.product_name||t.title||"Item",price:Number(t.price||t.selling_price||t.rate||t.amount||0),category_id:e,category_name:n,category_key:String(e||n||"").toLowerCase(),sku:t.sku||t.code||"",barcode:t.barcode||t.ean||t.upc||""}},Lt=(t={})=>({...t,id:t.id||t.value||t.name,name:t.name||t.description||t.label||"Category",key:String(t.id||t.value||t.name||t.description||t.label||"").toLowerCase()}),Wn=(t=[])=>{const e=new Set;return t.map(n=>({id:n.category_id||n.category_name,name:n.category_name||"Uncategorized",key:String(n.category_id||n.category_name||"").toLowerCase()})).filter(n=>!n.id||e.has(n.key)?!1:(e.add(n.key),!0))},Dt=Oe("catalogCache",{state:()=>({products:[],categories:[],loading:!1,error:"",lastUpdatedAt:localStorage.getItem(nt)||""}),getters:{activeProducts:t=>t.products.filter(e=>e&&e.id&&e.name),hasCachedCatalog:t=>t.products.length>0},actions:{loadCached(){this.products=(Pt(localStorage.getItem(kt),[])||[]).map(qt),this.categories=(Pt(localStorage.getItem(Tt),[])||[]).map(Lt),this.lastUpdatedAt=localStorage.getItem(nt)||""},persist(){localStorage.setItem(kt,JSON.stringify(this.products)),localStorage.setItem(Tt,JSON.stringify(this.categories)),localStorage.setItem(nt,new Date().toISOString()),this.lastUpdatedAt=localStorage.getItem(nt)||""},async refresh(t={}){this.loading=!0,this.error="";try{const e={per_page:500};t.locationId&&(e.location_id=t.locationId);const[n,s]=await Promise.all([ke.list(e),zt.list({per_page:500})]);this.products=Ct(n).map(qt);const o=Ct(s).map(Lt),r=Wn(this.products),a=new Set;this.categories=[...o,...r].filter(i=>{const c=i.key||String(i.id||i.name||"").toLowerCase();return!c||a.has(c)?!1:(a.add(c),!0)}),this.persist()}catch(e){this.error=e?.response?.data?.message||e?.message||"Catalog refresh failed",this.products.length||this.loadCached()}finally{this.loading=!1}},async bootstrap(t={}){this.loadCached(),await this.refresh(t)}}}),lt="paychat_pos_offline_mode_enabled",vt="pos_offline_mode",ie="paychat_offline_mode_cache",ce="paychat_offline_mode_cache_meta",ft="paychat:offline-mode-changed",Jn=720*60*1e3,T=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},L=()=>T(localStorage.getItem(ie),{})||{},Hn=t=>localStorage.setItem(ie,JSON.stringify(t||{})),le=()=>T(localStorage.getItem(ce),{})||{},Vn=t=>localStorage.setItem(ce,JSON.stringify(t||{})),Xn=(t={},e)=>{const n=Date.parse(t?.resources?.[e]?.last_synced_at||"");return Number.isFinite(n)&&Date.now()-n<Jn},st=(t,e="")=>{const n=t?.data?.data||t?.data||t||{};return Array.isArray(n)?n:Array.isArray(n.data)?n.data:e&&Array.isArray(n[e])?n[e]:Array.isArray(n.products)?n.products:Array.isArray(n.categories)?n.categories:Array.isArray(n.tables)?n.tables:Array.isArray(n.dining_tables)?n.dining_tables:[]},Zn=()=>(T(localStorage.getItem("selected_location"),{})||{}).id||localStorage.getItem("location_id")||"",X=()=>{const t=localStorage.getItem(lt);return t!==null?t==="true":localStorage.getItem(vt)==="true"},ts=()=>X()||typeof navigator<"u"&&navigator.onLine===!1,es=t=>(localStorage.setItem(lt,t?"true":"false"),localStorage.removeItem(vt),window.dispatchEvent(new CustomEvent(ft,{detail:{enabled:!!t}})),!!t),ns=t=>{const e=s=>t(s.detail?.enabled??X()),n=s=>{[lt,vt].includes(s.key)&&t(X())};return window.addEventListener(ft,e),window.addEventListener("storage",n),()=>{window.removeEventListener(ft,e),window.removeEventListener("storage",n)}},de=()=>{const t=le(),e=L(),n=t.resources||{},s=[{key:"products",label:"Products",count:e.products?.length||n.products?.count||0},{key:"categories",label:"Categories",count:e.categories?.length||n.categories?.count||0},{key:"diningStructure",label:"Table layout",count:e.diningStructure?.tables?.length||n.diningStructure?.count||0},{key:"upiProfiles",label:"UPI profiles",count:e.upiProfiles?.length||n.upiProfiles?.count||0},{key:"paymentMethods",label:"Payment methods",count:e.paymentMethods?.length||n.paymentMethods?.count||0},{key:"tenantContext",label:"Tenant context",count:n.tenantContext?.count||0},{key:"locations",label:"Locations",count:e.locations?.length||n.locations?.count||0}].map(o=>({...o,status:n[o.key]?.status||"missing",error:n[o.key]?.error||"",last_synced_at:n[o.key]?.last_synced_at||""}));return{enabled:X(),ready:s.every(o=>o.status==="ready"),lastPreparedAt:t.last_prepared_at||"",checklist:s,cache:e}},Mt=(t,e,n)=>{t.resources=t.resources||{},t.resources[e]={...t.resources[e]||{},...n,updated_at:new Date().toISOString()}},ss=async({force:t=!1,locationId:e=Zn()}={})=>{const n=L(),s=le();s.resources=s.resources||{};const o=[],r=async(a,i,c=d=>Array.isArray(d)?d.length:+!!d)=>{if(!(!t&&n[a]&&Xn(s,a)))try{const d=await i();n[a]=d,Mt(s,a,{status:"ready",count:c(d),error:"",last_synced_at:new Date().toISOString()})}catch(d){const u=d?.response?.data?.message||d?.message||`${a} failed to load`;Mt(s,a,{status:"failed",error:u}),o.push({key:a,message:u})}};if(await r("products",async()=>{const a=Dt();return!t&&a.hasCachedCatalog||await a.refresh({locationId:e}),a.products}),await r("categories",async()=>{const a=Dt();if(!t&&a.categories?.length)return a.categories;const i=await zt.list({per_page:500});return st(i,"categories")}),await r("diningStructure",async()=>{if(!e)return{tables:[]};const a=await we.diningStructure({location_id:e}),i=a?.data?.data||a?.data||{};return{...i,tables:i.tables||i.dining_tables||[]}},a=>a?.tables?.length||0),await r("upiProfiles",async()=>{const a=await Kn.list({location_id:e||void 0,include_global:1});return st(a,"profiles")}),await r("paymentMethods",async()=>{const a=await Ee.getMethods();return st(a,"methods")}),await r("locations",async()=>{const a=await Ae.list();return st(a,"locations")}),await r("tenantContext",async()=>({tenant_info:T(localStorage.getItem("tenant_info"),{}),tenant_tax_config:T(localStorage.getItem("tenant_tax_config"),null),tenant_settings:T(localStorage.getItem("tenant_settings"),{}),tenant_slug:localStorage.getItem("tenant_slug"),tenant_id:localStorage.getItem("tenant_id"),tenant_api_key:localStorage.getItem("tenant_api_key")}),a=>+!!(a?.tenant_slug||a?.tenant_info)),s.last_prepared_at=new Date().toISOString(),Hn(n),Vn(s),o.length){const a=new Error(o.map(i=>i.message).join(", "));throw a.resources=o,a}return de()},os=()=>L(),as=()=>{const t=L();return Array.isArray(t.products)?t.products:T(localStorage.getItem("paychat_lightning_catalog_products"),[])||[]},rs=()=>{const t=L();return Array.isArray(t.categories)?t.categories:T(localStorage.getItem("paychat_lightning_catalog_categories"),[])||[]},pe=()=>L().diningStructure||{tables:[]},is=(t=null)=>{const e=pe(),n=e.tables||e.dining_tables||[];return Array.isArray(n)?t?n.filter(s=>!s.location_id||String(s.location_id)===String(t)):n:[]},cs=()=>{const t=L(),e=Array.isArray(t.paymentMethods)?t.paymentMethods:[];return e.length?e:[{type:"cash",name:"Cash",label:"Cash",enabled:!0},{type:"upi",name:"UPI",label:"UPI",enabled:!0}]},ls=()=>{const t=L(),e=Array.isArray(t.upiProfiles)?t.upiProfiles:[];if(e.length)return e;const n=T(localStorage.getItem("tenant_info"),{})||{},s=localStorage.getItem("owner_upi_id")||localStorage.getItem("static_upi_id")||n?.branding?.upi_id||n?.tenant?.upi_id||"";return s?[{id:"offline-default-upi",label:"Default UPI",name:"Default UPI",upi_id:s,is_active:!0,is_default:!0,offline_generated:!0}]:[]},Ps={POS_OFFLINE_MODE_KEY:lt,isOfflineModeEnabled:X,isOfflineRuntime:ts,setOfflineModeEnabled:es,subscribeToOfflineModeChanges:ns,prepareOfflineData:ss,getOfflineReadiness:de,getOfflineCache:os,getCachedProducts:as,getCachedCategories:rs,getCachedDiningStructure:pe,getCachedTables:is,getCachedPaymentMethods:cs,getCachedUpiProfiles:ls},ue="paychat_offline_released_tables",me="paychat:offline-table-released",ds=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},dt=()=>ds(localStorage.getItem(ue),[])||[],_e=t=>{localStorage.setItem(ue,JSON.stringify(t||[]))},N=t=>t==null||t===""?"":String(t),ht=(t=[])=>Array.from(new Set(t.map(N).filter(Boolean))),ps=(t={})=>ht([t.table_id,t.primary_table_id,...Array.isArray(t.linked_table_ids)?t.linked_table_ids:[],t.table_snapshot?.id,t.table_snapshot?.table_id,t.primary_table?.id,...Array.isArray(t.tables)?t.tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.linked_tables)?t.linked_tables.map(e=>e?.id||e?.table_id):[]]),Cs=()=>dt(),qs=(t={})=>{const e=ps(t);if(!e.length)return null;const n=N(t.local_order_id),s={local_order_id:n,order_id:N(t.order_id||t.backend_order_id),table_session_id:N(t.table_session_id),table_ids:e,released_at:new Date().toISOString()},o=dt().filter(r=>n?N(r.local_order_id)!==n:!r.table_ids?.some(a=>e.includes(N(a))));return o.push(s),_e(o),window.dispatchEvent(new CustomEvent(me,{detail:s})),s},us=t=>{const e=N(t);if(!e)return;const n=dt().filter(s=>N(s.local_order_id)!==e);_e(n),window.dispatchEvent(new CustomEvent(me,{detail:{local_order_id:e,cleared:!0}}))},Ls=(t={},e=dt())=>{const n=ht([t.table_id,t.table?.id,t.__gridTable?.id,t.order?.table_id,t.order?.table?.id,ms(t)].flat()),s=N(t.order_id||t.order?.id),o=N(t.table_session_id||t.order?.table_session_id||t.order?.table_session?.id||(t.order?t.id:null));return e.some(r=>{const a=ht(r.table_ids||[]);return!!(n.some(i=>a.includes(i))||s&&N(r.order_id)===s||o&&N(r.table_session_id)===o)})},ms=(t={})=>[...Array.isArray(t.tables)?t.tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.linked_tables)?t.linked_tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.order?.tables)?t.order.tables.map(e=>e?.id||e?.table_id):[],...Array.isArray(t.order?.linked_tables)?t.order.linked_tables.map(e=>e?.id||e?.table_id):[]];let W=null;const _={PENDING_SYNC:"pending_sync",SYNCING:"syncing",SYNCED:"synced",FAILED:"failed"},D=()=>(W||(W=new ve("paychatpos_offline_db"),W.version(1).stores({offlineOrders:"local_order_id, status, created_at, synced_at, backend_order_id"}),W.version(2).stores({offlineOrders:"local_order_id, status, created_at, synced_at, backend_order_id",offlineTableSessions:"local_session_id, status, location_id, primary_table_id, local_order_id, updated_at",offlineTableOrders:"local_order_id, status, location_id, table_session_id, primary_table_id, updated_at",offlineKotBatches:"local_kot_id, local_order_id, status, created_at"})),W),yt=(t,e=new WeakSet)=>{if(t==null||typeof t=="string"||typeof t=="number"||typeof t=="boolean")return t;if(typeof t=="bigint")return Number(t);if(t instanceof Date)return t.toISOString();if(typeof File<"u"&&t instanceof File)return{name:t.name,type:t.type,size:t.size,last_modified:t.lastModified};if(typeof t=="object"&&!e.has(t))return e.add(t),Array.isArray(t)?t.map(n=>yt(n,e)).filter(n=>n!==void 0):Object.entries(t).reduce((n,[s,o])=>{if(typeof o=="function"||typeof o=="symbol")return n;const r=yt(o,e);return r!==void 0&&(n[s]=r),n},{})},Ds=async t=>{const e=D(),n=new Date().toISOString(),s=yt(t),o=await e.offlineOrders.get(s.local_order_id);return o?.status===_.SYNCED?o.payload||s:(await e.offlineOrders.put({...o||{},local_order_id:s.local_order_id,status:_.PENDING_SYNC,created_at:o?.created_at||n,updated_at:n,payload:s,sync_error:null,backend_order_id:o?.backend_order_id||null,synced_at:o?.synced_at||null,backend_response:o?.backend_response||null}),s)},ge=async()=>D().offlineOrders.where("status").anyOf(_.PENDING_SYNC,_.FAILED).toArray(),Ms=async({includeSynced:t=!1}={})=>{const e=D();return(t?await e.offlineOrders.toArray():await e.offlineOrders.where("status").anyOf(_.PENDING_SYNC,_.FAILED,_.SYNCING).toArray()).sort((s,o)=>Date.parse(o.created_at||0)-Date.parse(s.created_at||0))},_s=async t=>D().offlineOrders.get(t),Ut=async t=>D().offlineOrders.update(t,{status:_.SYNCING,sync_error:null,updated_at:new Date().toISOString()}),gs=async(t=15)=>{const e=D(),n=Date.now()-Number(t||15)*60*1e3,s=await e.offlineOrders.where("status").equals(_.SYNCING).toArray();let o=0;for(const r of s){const a=Date.parse(r.updated_at||r.created_at||"");Number.isFinite(a)&&a>n||(await e.offlineOrders.update(r.local_order_id,{status:_.PENDING_SYNC,sync_error:null,updated_at:new Date().toISOString()}),o+=1,console.log("[Offline Sync] stale syncing order recovered",r.local_order_id))}return o},fs=async(t,e)=>{const n=D(),s=e?.data||e||{},o=await n.offlineOrders.get(t),r=s?.side_effects?.table_session||s?.data?.side_effects?.table_session;return(!(o?.payload?.dining_flow==="table_service")||r!=="failed")&&us(t),n.offlineOrders.update(t,{status:_.SYNCED,sync_error:null,backend_order_id:s?.order?.id||s?.data?.order?.id||s?.order_id||null,synced_at:new Date().toISOString(),updated_at:new Date().toISOString(),backend_response:s})},hs=async(t,e)=>D().offlineOrders.update(t,{status:_.FAILED,sync_error:e?.response?.data||e?.message||String(e),updated_at:new Date().toISOString()}),Us=async()=>{await gs();const t=await ge(),e={synced:0,failed:0,total:t.length},n=s=>{const o=s?.response?.status,r=s?.response?.data||s?.data||s||{},a=String(r?.error_code||r?.message||r?.error||s?.message||"").toLowerCase();return o===409&&(a.includes("processing")||a.includes("syncing")||a.includes("locked"))};e.orders=[];for(const s of t){const o=await ys(s.local_order_id,{isFreshProcessingConflict:n});e.orders.push(o),o.status===_.SYNCED&&(e.synced+=1),o.status===_.FAILED&&(e.failed+=1)}return e},ys=async(t,e={})=>{const n=await _s(t);if(!n)throw new Error("Offline order not found");if(n.status===_.SYNCED)return{local_order_id:t,status:_.SYNCED,response:n.backend_response};const s=e.isFreshProcessingConflict||(o=>{const r=o?.response?.status,a=o?.response?.data||o?.data||o||{},i=String(a?.error_code||a?.message||a?.error||o?.message||"").toLowerCase();return r===409&&(i.includes("processing")||i.includes("syncing")||i.includes("locked"))});try{await Ut(t),console.log("[Offline Sync] syncing order",t);const o=localStorage.getItem("tenant_api_key"),r=o?{"X-Tenant-Api-Key":o}:{},a=await F.post("/offline-orders/sync",n.payload,{headers:r});return await fs(t,a),console.log("[Offline Sync] synced order",t),{local_order_id:t,status:_.SYNCED,response:a?.data||a}}catch(o){return s(o)?(console.log("[Offline Sync] backend still processing order",t),await Ut(t),{local_order_id:t,status:_.SYNCING,error:o}):(xe({type:"offline_sync_failure",action:"offline.sync_order",local_order_id:t,backend_message:o?.response?.data?.message||o?.message||String(o)}),await hs(t,o),{local_order_id:t,status:_.FAILED,error:o?.response?.data||o?.message||String(o)})}},Rs=async()=>(await ge()).length,fe="paychat_kitchen_operation_mode",he="paychat_generate_inline_kitchen_token",ye="paychat_inline_kitchen_without_status_management",B={DEDICATED_KDS:"dedicated_kds",INLINE:"inline"},be=Object.values(B),R=()=>typeof window>"u"?null:window.localStorage||null,Rt=t=>{try{const e=R()?.getItem(t);return e?JSON.parse(e):null}catch{return null}},bs=()=>{const t=Rt("tenant_settings")||{},e=Rt("tenant_info")||{},n=t?.kitchen?.operation_mode||t?.raw?.kitchen_operation_mode||e?.settings?.kitchen?.operation_mode||e?.settings?.raw?.kitchen_operation_mode||e?.tenant?.settings?.kitchen?.operation_mode||e?.tenant?.settings?.raw?.kitchen_operation_mode;return be.includes(n)?n:null},Ss=()=>{const e=R()?.getItem(fe);return be.includes(e)?e:bs()||B.DEDICATED_KDS},zs=t=>{const e=t===B.INLINE?B.INLINE:B.DEDICATED_KDS;return R()?.setItem(fe,e),e},Fs=()=>Ss()===B.INLINE,Qs=()=>R()?.getItem(he)==="true",Bs=t=>{const e=!!t;return R()?.setItem(he,e?"true":"false"),e},js=()=>R()?.getItem(ye)==="true",Ks=t=>{const e=!!t;return R()?.setItem(ye,e?"true":"false"),e};export{B as K,me as O,tn as a,ks as b,js as c,Rs as d,Us as e,Dt as f,D as g,Cs as h,Fs as i,Ss as j,Qs as k,Ls as l,qs as m,Ze as n,Ps as o,Ts as p,Ms as q,ys as r,Ds as s,zs as t,Kn as u,Ks as v,Bs as w};
