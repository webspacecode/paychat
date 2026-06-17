import{b as $,g as O}from"./index-D-u9MWYA.js";import{o as nt}from"./vendor-qKbVCTru.js";const Kt={list(t={}){return $.get("/upi-profiles",{params:t})},create(t){return $.post("/upi-profiles",t)},update(t,e){return $.patch(`/upi-profiles/${t}`,e)},deactivate(t){return $.delete(`/upi-profiles/${t}`)},setDefault(t){return $.patch(`/upi-profiles/${t}/default`)}},rt="/color-paychat-logo-main.svg",R={"58mm":{width:"58mm",columns:32,fontSize:"10px",titleSize:"13px",logoMaxWidth:"136px",paychatLogoWidth:"72px",qrSize:"86px",padding:"6px"},"80mm":{width:"80mm",columns:48,fontSize:"12px",titleSize:"16px",logoMaxWidth:"210px",paychatLogoWidth:"96px",qrSize:"112px",padding:"8px"}},ot=(t,e={})=>{if(!t||t==="null")return e;try{return JSON.parse(t)}catch{return e}},V=t=>{try{return typeof localStorage>"u"?"":localStorage.getItem(t)||""}catch{return""}},it=()=>ot(V("tenant_info"),{}),c=t=>String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"),x=t=>c(t).replace(/`/g,"&#096;"),E=t=>String(t??"").replace(/[\u20b9]/g,"Rs.").replace(/[^\x20-\x7E\n]/g,"").trim(),p=t=>Number(t||0).toFixed(2),N=t=>{const e=Number(t||0);return Number.isInteger(e)?String(e):e.toFixed(2)},j=(t=new Date)=>{const e=t?new Date(t):new Date;return Number.isNaN(e.getTime())?new Date().toLocaleString("en-IN"):e.toLocaleString("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"})},F=(t="80mm")=>R[t]||R["80mm"],u=(...t)=>{for(const e of t)if(e!=null&&e!=="")return e;return""},_=(...t)=>{for(const e of t){if(e==null||e==="")continue;const r=Number(e);if(Number.isFinite(r))return r}return 0},st=(t={})=>Array.isArray(t.items)?t.items:[],at=(t={})=>u(t.product?.name,t.product_name,t.name,t.title,"Item"),U=(t={})=>_(t.quantity,t.qty,1)||1,Q=(t={})=>{const e=U(t),r=u(t.rate,t.price,t.unit_price);if(r!=="")return Number(r||0);const n=u(t.total,t.line_total,t.amount);return Number(n||0)/e},ct=(t={})=>{const e=u(t.total,t.line_total,t.amount);return e!==""?Number(e||0):Q(t)*U(t)},lt=(t={})=>{const e=Array.isArray(t.payments)?t.payments[0]:null;return u(t.payment_method,t.payment_mode,t.payment?.method,e?.payment_method,e?.method)},q=(...t)=>{const e=[];return t.flat().forEach(r=>{if(!r)return;if(typeof r=="string"||typeof r=="number"){e.push(String(r));return}const n=u(r.code,r.kot_code,r.batch_code,r.token_code,r.id);n&&e.push(String(n))}),[...new Set(e)]},ut=(t,e)=>{if(!t)return"";const r=String(t);return r.trim().startsWith("<svg")||r.trim().startsWith("<img")?`<div class="qr-embed">${r}</div>`:/^(data:image\/|https?:\/\/|\/)/i.test(r)?`<img class="qr-image" src="${x(r)}" alt="Invoice QR" />`:`<div class="qr-url">${c(r)}</div>`},dt=(t={},e={})=>{if(!t||typeof t!="object")throw new Error("Order data is required");const r=it(),n=O(),o=r?.tenant||{},i=r?.branding||{},a=t.location||{},l=st(t).map(h=>({name:at(h),qty:U(h),rate:Q(h),total:ct(h)})),s=_(t.subtotal,t.totals?.subtotal,l.reduce((h,T)=>h+T.rate*T.qty,0)),m=_(t.discount?.amount,t.discount,t.discount_amount,t.totals?.discount,t.totals?.discount_total),d=_(t.tax,t.tax_amount,t.total_tax,t.totals?.tax,t.totals?.tax_total,t.totals?.total_tax,t.tax_summary?.total_tax),b=_(t.total,t.grand_total,t.totals?.grand_total,s+d-m);return{shopName:u(e.shopName,a.tenant?.name,t.tenant?.name,i.company_name,o.name,V("tenant_slug"),"PayChat POS"),shopPhone:u(e.shopPhone,a.phone,i.phone,o.phone),shopAddress:u(e.shopAddress,a.address,i.address,o.address),shopLogoUrl:u(e.shopLogoUrl,a.logo,a.tenant?.logo,t.tenant?.logo,i.logo,o.logo),locationName:u(a.name,t.location_name),paychatLogoUrl:u(e.paychatLogoUrl,t.paychat_logo_url,rt),invoiceNo:u(e.invoiceNo,t.invoice_no,t.invoiceNo,t.invoice?.number,t.invoice?.invoice_no,t.invoice?.offline_invoice_number,t.offline_invoice_number,t.local_invoice_no),orderNo:u(t.order_no,t.orderNo,t.local_order_id,t.id),dateTime:u(t.created_at,t.completed_at,t.updated_at,t.offline_created_at,new Date),orderType:u(t.order_type,t.orderType,t.delivery_channel_label,t.delivery_channel),tableName:u(t.table?.name,t.table?.code,t.table_name,t.table_no,t.table_number),guestCount:u(t.guest_count,t.guestCount,t.table_session?.guest_count),tokenNo:u(t.token?.token_code,t.token_code,t.token?.offline_token_number,t.offline_token_number),kotCodes:q(t.kot_codes,t.kotTokens,t.kot_tokens,t.kitchen_batches,t.batches),batchCodes:q(t.batch_codes,t.batchCodes),items:l,subtotal:s,discount:m,tax:d,grandTotal:b,paidAmount:_(t.paid_amount,t.payment?.amount,t.totals?.paid_amount,b),paymentMethod:lt(t),invoiceUrl:u(e.invoiceUrl,t.invoice_url,t.invoiceUrl,t.invoice?.url),invoiceQr:u(e.invoiceQr,t.invoice_qr,t.invoiceQr,t.qr),reviewQr:u(e.reviewQr,t.review_qr,t.reviewQr),notes:u(t.print_note,t.note),simpleBilling:n.simpleBilling,billingLabel:n.billingLabel}},Ht=(t,e={})=>{const r=e.paperSize||"80mm",n=F(r),o=r==="58mm",i=Array.isArray(t.items)?t.items:[],a=Array.isArray(t.kotCodes)?t.kotCodes:[],l=Array.isArray(t.batchCodes)?t.batchCodes:[],s=O(),d=!(t.simpleBilling??s.simpleBilling),b=t.billingLabel||s.billingLabel||"Order",h=ut(t.invoiceQr||t.reviewQr,n.qrSize),T=!h&&t.invoiceUrl?`<div class="qr-url">${c(t.invoiceUrl)}</div>`:"";return`<!doctype html>
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
      max-height: ${o?"54px":"74px"};
      object-fit: contain;
      margin: 0 auto 4px;
    }
    .paychat-logo {
      display: block;
      max-width: ${n.paychatLogoWidth};
      max-height: ${o?"20px":"26px"};
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
      ${t.shopLogoUrl?`<img class="shop-logo" src="${x(t.shopLogoUrl)}" alt="${x(t.shopName)}" />`:""}
      <div class="title">${c(t.shopName)}</div>
      ${t.locationName?`<div class="muted">${c(t.locationName)}</div>`:""}
      ${t.shopAddress?`<div class="muted">${c(t.shopAddress)}</div>`:""}
      ${t.shopPhone?`<div class="muted">Phone: ${c(t.shopPhone)}</div>`:""}
    </div>
    <div class="line"></div>
    ${t.invoiceNo?`<div class="bill-no">BILL NO: ${c(t.invoiceNo)}</div>`:""}
    <table>
	      ${t.orderNo?`<tr><td>${c(b)}</td><td class="right">${c(t.orderNo)}</td></tr>`:""}
      <tr><td>Date</td><td class="right">${c(j(t.dateTime))}</td></tr>
      ${t.orderType?`<tr><td>Type</td><td class="right">${c(t.orderType)}</td></tr>`:""}
	      ${d&&t.tableName?`<tr><td>Table</td><td class="right">${c(t.tableName)}</td></tr>`:""}
	      ${d&&t.guestCount?`<tr><td>Guests</td><td class="right">${c(t.guestCount)}</td></tr>`:""}
	      ${d&&t.tokenNo?`<tr><td>Token</td><td class="right">${c(t.tokenNo)}</td></tr>`:""}
	      ${d&&a.length?`<tr><td>KOT</td><td class="right">${c(a.join(", "))}</td></tr>`:""}
	      ${d&&l.length?`<tr><td>Batch</td><td class="right">${c(l.join(", "))}</td></tr>`:""}
    </table>
    <div class="line"></div>
    ${o?`
      <div>
        ${i.length?i.map(f=>`
          <div class="item-block">
            <div class="item-name">${c(f.name)}</div>
            <div class="item-meta">
              <span>${c(N(f.qty))} x ${c(p(f.rate))}</span>
              <strong>${c(p(f.total))}</strong>
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
          ${i.length?i.map(f=>`
            <tr>
              <td class="item-name">${c(f.name)}</td>
              <td class="right">${c(N(f.qty))}</td>
              <td class="right">${c(p(f.rate))}</td>
              <td class="right">${c(p(f.total))}</td>
            </tr>
          `).join(""):'<tr><td colspan="4" class="center">No items</td></tr>'}
        </tbody>
      </table>
    `}
    <div class="line"></div>
    <div class="total-row"><span>Subtotal</span><span>${c(p(t.subtotal))}</span></div>
    ${t.discount?`<div class="total-row"><span>Discount</span><span>-${c(p(t.discount))}</span></div>`:""}
    ${t.tax?`<div class="total-row"><span>Tax/GST</span><span>${c(p(t.tax))}</span></div>`:""}
    <div class="total-row grand"><span>TOTAL</span><span>${c(p(t.grandTotal))}</span></div>
    ${t.paidAmount?`<div class="total-row"><span>Paid</span><span>${c(p(t.paidAmount))}</span></div>`:""}
    ${t.paymentMethod?`<div class="total-row"><span>Payment</span><span>${c(t.paymentMethod)}</span></div>`:""}
    ${h||T?`
      <div class="line"></div>
      <div class="qr-wrap">
        <div class="muted">Scan QR for invoice/review</div>
        ${h||T}
      </div>
    `:""}
    <div class="line"></div>
    <div class="center">Thank you</div>
    <div class="center muted">
      ${t.paychatLogoUrl?`<img class="paychat-logo" src="${x(t.paychatLogoUrl)}" alt="PayChat" />`:""}
      Powered by PayChat
    </div>
  </div>
</body>
</html>`},S=(t,e="-")=>`${e.repeat(t)}
`,y=(t,e)=>{const r=E(t).slice(0,e),n=Math.max(0,Math.floor((e-r.length)/2));return`${" ".repeat(n)}${r}
`},g=(t,e,r)=>{const n=E(e),o=Math.max(1,r-n.length-1),i=E(t).slice(0,o),a=Math.max(1,r-i.length-n.length);return`${i}${" ".repeat(a)}${n}
`},z=(t,e)=>{const r=E(t).split(/\s+/).filter(Boolean).flatMap(i=>i.length<=e?[i]:i.match(new RegExp(`.{1,${e}}`,"g"))||[i]),n=[];let o="";return r.forEach(i=>{if(!o){o=i;return}(o+" "+i).length<=e?o+=` ${i}`:(n.push(o),o=i.slice(0,e))}),o&&n.push(o),n.length?n:[""]},pt=(t,e)=>{const r=z(t.name,e),n=`${N(t.qty)} x ${p(t.rate)}`;return[...r.map(o=>`${o}
`),g(n,p(t.total),e)].join("")},mt=(t,e)=>{const i=e-5-9-10,a=z(t.name,i),l=`${a[0].padEnd(i)}${N(t.qty).padStart(5)}${p(t.rate).padStart(9)}${p(t.total).padStart(10)}
`,s=a.slice(1).map(m=>`${m}
`).join("");return l+s},ht=(t,e={})=>{const r=e.paperSize||"80mm",{columns:n}=F(r),o=r==="58mm",i=Array.isArray(t.items)?t.items:[],a=Array.isArray(t.kotCodes)?t.kotCodes:[],l=Array.isArray(t.batchCodes)?t.batchCodes:[],s=O(),m=t.simpleBilling??s.simpleBilling,d=t.billingLabel||s.billingLabel||"Order",b=o?"":`${"Item".padEnd(n-24)}${"Qty".padStart(5)}${"Rate".padStart(9)}${"Amt".padStart(10)}
`;return[y(t.shopName,n),t.locationName?y(t.locationName,n):"",t.shopPhone?y(`Phone: ${t.shopPhone}`,n):"",t.shopAddress?y(t.shopAddress,n):"",S(n),t.invoiceNo?y(`BILL NO: ${t.invoiceNo}`,n):"",t.orderNo?g(d,t.orderNo,n):"",g("Date",j(t.dateTime),n),t.orderType?g("Type",t.orderType,n):"",!m&&t.tableName?g("Table",t.tableName,n):"",!m&&t.guestCount?g("Guests",t.guestCount,n):"",!m&&t.tokenNo?g("Token",t.tokenNo,n):"",!m&&a.length?g("KOT",a.join(","),n):"",!m&&l.length?g("Batch",l.join(","),n):"",S(n),b,b?S(n):"",i.length?i.map(h=>o?pt(h,n):mt(h,n)).join(""):y("No items",n),S(n),g("Subtotal",p(t.subtotal),n),t.discount?g("Discount",`-${p(t.discount)}`,n):"",t.tax?g("Tax/GST",p(t.tax),n):"",S(n),g("TOTAL",p(t.grandTotal),n),t.paidAmount?g("Paid",p(t.paidAmount),n):"",t.paymentMethod?g("Payment",t.paymentMethod,n):"",t.invoiceUrl?`${S(n)}${y("Invoice/review link",n)}${z(t.invoiceUrl,n).map(h=>`${E(h)}
`).join("")}`:"",S(n),y("Thank you",n),y("Powered by PayChat",n)].join("")},gt=ht,G="paychat_printer_settings",K={printerType:"browser",paperSize:"80mm",reuseAuthorizedPrinter:!0,autoPrintAfterCheckout:!1,autoPrintKot:!1,usbVendorId:null,usbProductId:null,authorizedAt:""},bt=(t,e=null)=>{try{return t?JSON.parse(t):e}catch{return e}},ft=t=>t==="58mm"?"58mm":"80mm",yt=t=>t==="bluetooth-rawbt"||t==="usb-serial"||t==="browser"?t:"browser",H=(t={})=>({...K,...t&&typeof t=="object"?t:{},printerType:yt(t?.printerType),paperSize:ft(t?.paperSize),reuseAuthorizedPrinter:t?.reuseAuthorizedPrinter!==!1,autoPrintAfterCheckout:!!t?.autoPrintAfterCheckout,autoPrintKot:!!t?.autoPrintKot,usbVendorId:Number.isFinite(Number(t?.usbVendorId))?Number(t.usbVendorId):null,usbProductId:Number.isFinite(Number(t?.usbProductId))?Number(t.usbProductId):null,authorizedAt:typeof t?.authorizedAt=="string"?t.authorizedAt:""}),k=()=>typeof localStorage>"u"?{...K}:H(bt(localStorage.getItem(G),{})),J=(t={})=>{const e=H({...k(),...t});try{localStorage.setItem(G,JSON.stringify(e))}catch{}return e},Jt=()=>J({usbVendorId:null,usbProductId:null,authorizedAt:""}),St=()=>{const t=k();return t.usbVendorId===null&&t.usbProductId===null?null:{usbVendorId:t.usbVendorId,usbProductId:t.usbProductId}},Y=(t={},e=St())=>{if(!e)return!0;const r=e.usbVendorId===null||Number(t.usbVendorId)===e.usbVendorId,n=e.usbProductId===null||Number(t.usbProductId)===e.usbProductId;return r&&n},vt=27,wt=29,_t=10,W=[9600,19200,38400,57600,115200],Pt={dataBits:8,stopBits:1,parity:"none",flowControl:"none"},Tt="USB serial print failed. Please confirm correct printer COM port in Windows Device Manager. COM1 may not be the actual thermal printer.",$t=8e3,Et=12e3,xt=5e3,It=new TextEncoder,B=(...t)=>Uint8Array.from(t),Nt=(t="")=>It.encode(String(t)),w=(t,e)=>{const r=new Error(e);return r.code=t,r},L=(t,e,r,n)=>{let o;const i=new Promise((a,l)=>{o=setTimeout(()=>{l(w(r,n))},e)});return Promise.race([t,i]).finally(()=>{clearTimeout(o)})},Ct=(t=0)=>new Promise(e=>setTimeout(e,t)),X=()=>typeof navigator<"u"&&"serial"in navigator,Z=()=>typeof window>"u"||window.isSecureContext===!0,tt=()=>{if(!Z())throw w("ESC_POS_INSECURE_CONTEXT","Serial printing requires HTTPS or localhost. Open POS from a secure URL, then try ESC/POS print again.");if(!X())throw w("ESC_POS_UNSUPPORTED","ESC/POS printing is not supported on this browser/device. Try HTML print or use Android ESC Printer utility.")},kt=t=>{const e=t.reduce((o,i)=>o+i.length,0),r=new Uint8Array(e);let n=0;return t.forEach(o=>{r.set(o,n),n+=o.length}),r},Lt=()=>B(vt,64),At=(t=1)=>B(...Array.from({length:Math.max(0,t)},()=>_t)),Ot=()=>B(wt,86,0),P=t=>{try{return typeof t?.getInfo=="function"?t.getInfo():{}}catch{return{}}},Ut=t=>{const e=P(t);return J({printerType:"usb-serial",reuseAuthorizedPrinter:!0,usbVendorId:Number.isFinite(Number(e.usbVendorId))?Number(e.usbVendorId):null,usbProductId:Number.isFinite(Number(e.usbProductId))?Number(e.usbProductId):null,authorizedAt:new Date().toISOString()}),e},zt=(t={})=>t.usbVendorId!==void 0||t.usbProductId!==void 0,v=(t,e,r={})=>{t&&console.log(`[USB Serial ESC/POS] ${e}`,r)},Bt=(t={},e={})=>{const r=dt(t,e.context||{});return gt(r,e)},Rt=(t={},e={})=>{const r=Bt(t,e),n=[Lt(),Nt(r),At(3)];return e.cut!==!1&&n.push(Ot()),kt(n)},qt=async()=>{tt();try{const t=await navigator.serial.requestPort();return Ut(t),t}catch(t){const e=String(t?.message||"").toLowerCase();throw e.includes("no compatible")||e.includes("no port found")||e.includes("no device")?w("ESC_POS_NO_SERIAL_DEVICE","USB printer was not detected as a serial printer. Try HTML print or use a printer/driver that supports serial ESC/POS."):w("ESC_POS_SELECTION_CANCELLED","Printer selection cancelled.")}},et=async()=>{if(tt(),typeof navigator.serial.getPorts!="function")return[];try{const t=await navigator.serial.getPorts();return Array.isArray(t)?t:[]}catch{return[]}},Wt=async()=>{if(!k().reuseAuthorizedPrinter)return null;const e=await et();return e.length&&(e.find(n=>Y(P(n)))||e[0])||null},Mt=async(t={})=>{if(!t.forceRequestPort){const e=await Wt();if(e)return e}return qt()},M=async(t,e,r={})=>{const n=r.baudRate||9600,o=!!r.logUsbSerial;v(o,"baud rate tried",{baudRate:n});try{await L(t.open({baudRate:n,...Pt}),$t,"ESC_POS_CONNECTION_FAILED","Printer did not open in time. Check printer power, pairing, or USB/serial connection.")}catch(s){throw v(o,"error reason",{baudRate:n,stage:"open",reason:s?.message||String(s)}),s}let i,a=null,l=!1;try{if(i=t.writable?.getWriter(),!i)throw new Error("Serial port writable stream is not available.");v(o,"bytes length",{baudRate:n,length:e.length}),await L(i.write(e),Et,"ESC_POS_WRITE_TIMEOUT","Printer did not accept receipt data in time. Check printer power and connection."),l=!0,await Ct(150)}catch(s){v(o,"error reason",{baudRate:n,stage:"write",reason:s?.message||String(s)}),a=s}finally{if(i)try{i.releaseLock()}catch(s){v(o,"error reason",{baudRate:n,stage:"release",reason:s?.message||String(s)}),a=a||s}try{await L(t.close(),xt,"ESC_POS_CLOSE_TIMEOUT","Printer received the receipt, but the serial connection did not close cleanly.")}catch(s){if(v(o,"error reason",{baudRate:n,stage:"close",reason:s?.message||String(s)}),!l)throw s;console.warn("[ESC/POS] Receipt data was written, but serial close failed.",s)}}if(a)throw a},Dt=async(t,e,r={})=>{const n=P(t);if(!zt(n))try{await M(t,e,r);return}catch(s){throw s?.code?s:w("ESC_POS_CONNECTION_FAILED","Printer connection failed. Check printer power, pairing, or USB/bridge connection.")}v(!0,"selected port info",n);let i=null;const a=r.baudRate?[r.baudRate,...W.filter(s=>s!==r.baudRate)]:W;for(const s of a)try{await M(t,e,{...r,baudRate:s,logUsbSerial:!0});return}catch(m){i=m}const l=w("ESC_POS_USB_SERIAL_FAILED",Tt);throw l.cause=i,l},Vt=async(t,e={})=>{const r=await Mt(e),n=Rt(t,e);await Dt(r,n,e)},Yt=async()=>{if(!Z())return{supported:!1,authorized:!1,status:"insecure"};if(!X())return{supported:!1,authorized:!1,status:"unsupported"};const t=await et(),e=t.find(r=>Y(P(r)));return{supported:!0,authorized:!!(e||t[0]),status:e||t[0]?"authorized":"not-authorized",portCount:t.length,portInfo:e?P(e):t[0]?P(t[0]):{}}},Xt=async(t={})=>{await Vt({id:"USB-TEST",order_no:"USB-TEST",order_type:"Printer Setup",payment_method:"test",total:0,paid_amount:0,items:[{product_name:"PayChat USB Printer Test",name:"PayChat USB Printer Test",quantity:1,price:0,total:0}]},{paperSize:t.paperSize||k().paperSize,type:"invoice",forceRequestPort:!0})},C="paychat_pos_wake_lock_enabled",D=()=>{try{return localStorage.getItem(C)==="true"}catch{return!1}},Zt=t=>{try{return t?(localStorage.setItem(C,"true"),!0):(localStorage.removeItem(C),!1)}catch{return!1}},jt=()=>typeof navigator>"u"?{supported:!1,reason:"browser_unavailable"}:"wakeLock"in navigator?typeof window<"u"&&window.isSecureContext===!1?{supported:!1,reason:"insecure_context"}:{supported:!0,reason:"supported"}:{supported:!1,reason:"unsupported_browser"},te=()=>{let t=null,e=!1,r=!1,n=0;const o=async()=>{try{await t?.release?.()}catch(d){console.warn("POS wake lock release failed:",d)}finally{t=null}},i=()=>{const d=jt();return d.supported?!0:(r||(console.warn(`POS wake lock unavailable: ${d.reason}`),r=!0),!1)},a=async()=>{const d=Date.now();if(!(e||t||!D()||!i()||document.visibilityState!=="visible")&&!(d-n<750)){n=d;try{t=await navigator.wakeLock.request("screen"),t.addEventListener?.("release",()=>{t=null})}catch(b){console.warn("POS wake lock failed:",b)}}},l=()=>{a()},s=()=>{document.visibilityState==="visible"?a():o()},m=d=>{d.key===C&&(D()?a():o())};return document.addEventListener("visibilitychange",s),document.addEventListener("pointerdown",l,{passive:!0}),document.addEventListener("touchstart",l,{passive:!0}),document.addEventListener("click",l,{passive:!0}),window.addEventListener("storage",m),a(),()=>{e=!0,document.removeEventListener("visibilitychange",s),document.removeEventListener("pointerdown",l),document.removeEventListener("touchstart",l),document.removeEventListener("click",l),window.removeEventListener("storage",m),o()}},Ft="paychat-pos",I="cache",A=nt(Ft,1,{upgrade(t){t.createObjectStore(I)}}),ee={async set(t,e){await(await A).put(I,e,t)},async get(t){return await(await A).get(I,t)},async clear(){await(await A).clear(I)}};export{Vt as a,Ht as b,k as c,Yt as d,J as e,Jt as f,D as g,Zt as h,jt as i,Xt as j,dt as n,ee as p,te as s,Kt as u};
