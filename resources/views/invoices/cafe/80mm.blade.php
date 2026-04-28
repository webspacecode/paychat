<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            font-family: monospace;
        }

        .receipt {
            width: 80mm;
            margin: auto;
            padding: 8px;
            color: #111;
        }

        /* HEADER */
        .header {
            text-align: center;
        }

        .logo {
            max-height: 50px;
            margin-bottom: 5px;
        }

        .company {
            font-size: 16px;
            font-weight: bold;
        }

        .meta {
            font-size: 11px;
            color: #555;
        }

        /* DIVIDER */
        .divider {
            border-top: 1px dashed #999;
            margin: 6px 0;
        }

        /* ITEMS */
        .item {
            font-size: 12px;
            margin-bottom: 4px;
        }

        .item-name {
            font-weight: 500;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
        }

        /* TOTALS */
        .row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .total {
            font-size: 15px;
            font-weight: bold;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            font-size: 11px;
            margin-top: 10px;
        }

        .thanks {
            margin-top: 5px;
            font-weight: bold;
        }

        /* PRINT SETTINGS */
        @media print {
            body {
                margin: 0;
            }

            .receipt {
                width: 80mm;
            }

            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
        .qr-box {
            width: 90px;
            height: 90px;
            margin: 10px auto;
            display: block;
            position: relative;
        }

        .qr-box svg,
        .qr-box img {
            width: 90px !important;
            height: 90px !important;
            display: block;
        }

        .qr-text {
            text-align: center;
            font-size: 11px;
            margin-top: 6px;
            clear: both;
        }

        .logo-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .invoice-link {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 16px;
            background: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 20px;
            font-size: 14px;
        }

        .invoice-link:hover {
            background: #333;
        }
    </style>
</head>

<body>

<div class="receipt">

    <!-- HEADER -->
    <div class="header">
        @if($branding && $branding->logo)
        <div class="logo-wrap">
            <img src="{{ $branding->logo }}" class="logo">
        </div>
        @endif

        <div class="company" style="color:{{ $branding->primary_color ?? '#000' }}">
            {{ $branding->company_name ?? 'Cafe Name' }}
        </div>

        <div class="meta">
            {{ $branding->phone ?? '' }} <br>
            Order: {{ $order['order_no'] ?? '---' }} <br>
            {{ now()->format('d M Y h:i A') }}
        </div>
    </div>

    <div class="divider"></div>

    <!-- ITEMS -->
    @foreach($order['items'] ?? [] as $item)
        <div class="item">
            <div class="item-name">
                {{ $item['product_name'] }}
            </div>
            <div class="item-row">
                <span>{{ $item['quantity'] }} x ₹{{ $item['price'] }}</span>
                <span>₹{{ $item['quantity'] * $item['price'] }}</span>
            </div>
        </div>
    @endforeach

    <div class="divider"></div>

    <!-- TOTALS -->
    <div class="row">
        <span>Subtotal</span>
        <span>₹{{ number_format($totals['subtotal'] ?? 0, 2) }}</span>
    </div>

    <div class="row">
        <span>GST</span>
        <span>₹{{ number_format($totals['gst'] ?? 0, 2) }}</span>
    </div>
    <div class="divider"></div>

    <div class="row total">
        <span>Total</span>
        <span>₹{{ number_format($totals['total'] ?? 0, 2) }}</span>
    </div>

    <div class="divider"></div>

    <!-- FOOTER -->
    <div class="footer">
        @if(!empty($order['payments'][0]['payment_method']))
            <div class="row">
                <span>Paid via</span>
                <span>{{ strtoupper($order['payments'][0]['payment_method']) }}</span>
            </div>
        @endif
        @if(isset($qr))
            <div class="qr-box">
                {!! $qr !!}
            </div>

            <div class="qr-text">Scan for invoice</div>
            <div style="text-align:center; margin-top:8px;">
                <a href="{!! $url !!}" target="_blank" style="font-size:12px; text-decoration:underline; color:#000;">
                    View Invoice
                </a>
            </div>

        @endif

        <div class="thanks">☕ Thank you! Visit again</div>
        <!-- <div class="thanks">
            <a href="{{ $url }}" target="_blank" class="invoice-link">
                🧾 Get your invoice
            </a>
        </div> -->

    </div>

</div>

</body>
</html>