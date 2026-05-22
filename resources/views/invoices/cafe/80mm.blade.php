<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #111;
            font-family: "Courier New", monospace;
            font-size: 11px;
            line-height: 1.25;
        }

        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 8px 9px;
        }

        .center {
            text-align: center;
        }

        .merchant-logo {
            display: block;
            max-width: 210px;
            max-height: 70px;
            margin: 0 auto 5px;
            object-fit: contain;
        }

        .merchant-name {
            font-size: 16px;
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
            word-break: break-word;
        }

        .muted {
            color: #333;
            font-size: 10px;
        }

        .divider {
            border-top: 1px dashed #111;
            margin: 6px 0;
        }

        .bill-no {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0;
            margin-top: 3px;
            word-break: break-word;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2px 8px;
        }

        .meta-grid .right {
            text-align: right;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
            margin-top: 4px;
        }

        .chip {
            border: 1px solid #111;
            border-radius: 2px;
            padding: 1px 4px;
            font-size: 10px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            border-bottom: 1px dashed #111;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 0;
            text-align: left;
        }

        td {
            padding: 3px 0;
            vertical-align: top;
        }

        .col-item {
            width: 48%;
        }

        .col-qty,
        .col-rate,
        .col-amt {
            text-align: right;
            white-space: nowrap;
        }

        .item-name {
            padding-right: 5px;
            overflow-wrap: anywhere;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 1px 0;
        }

        .row span:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .grand-total {
            font-size: 15px;
            font-weight: 900;
            border-top: 1px dashed #111;
            margin-top: 4px;
            padding-top: 5px;
        }

        .paid {
            font-weight: 800;
        }

        .qr-wrap {
            text-align: center;
            margin-top: 8px;
        }

        .qr-box {
            width: 112px;
            height: 112px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-box svg,
        .qr-box img {
            width: 112px !important;
            height: 112px !important;
            display: block;
        }

        .paychat-logo {
            display: block;
            max-width: 100px;
            max-height: 28px;
            margin: 8px auto 2px;
            object-fit: contain;
        }

        .powered {
            text-align: center;
            font-size: 10px;
            color: #333;
            margin-top: 2px;
        }

        .thanks {
            text-align: center;
            font-weight: 800;
            margin-top: 7px;
        }

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

            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
@php
    $receipt = $receipt ?? [];
    $merchant = $receipt['merchant'] ?? [];
    $platform = $receipt['platform'] ?? [];
    $invoice = $receipt['invoice'] ?? [];
    $dining = $receipt['dining'] ?? [];
    $items = $receipt['items'] ?? [];
    $receiptTotals = $receipt['totals'] ?? [];
    $receiptPayments = collect($receipt['payments'] ?? []);
    $qrData = $receipt['qr'] ?? [];
    $merchantName = $merchant['name'] ?? ($branding->company_name ?? 'Cafe Name');
    $merchantLogo = $merchant['logo_url'] ?? ($logoSrc ?? null);
    $paychatLogo = $platform['paychat_logo_url'] ?? null;
    $kotCodes = $dining['kot_codes'] ?? [];
    $paymentMethods = $receiptPayments
        ->filter(fn ($payment) => ($payment['status'] ?? null) === 'success')
        ->pluck('method')
        ->filter()
        ->unique()
        ->implode(' + ');
@endphp

<div class="receipt">
    <div class="center">
        @if(!empty($merchantLogo))
            <img src="{{ $merchantLogo }}" class="merchant-logo" alt="{{ $merchantName }}">
        @endif

        <div class="merchant-name">{{ $merchantName }}</div>

        @if(!empty($merchant['address']))
            <div class="muted">{{ $merchant['address'] }}</div>
        @endif

        @if(!empty($merchant['phone']))
            <div class="muted">Ph: {{ $merchant['phone'] }}</div>
        @endif

        @if(!empty($merchant['gstin']))
            <div class="muted">GSTIN: {{ $merchant['gstin'] }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <div class="center">
        <div class="muted">BILL / INVOICE NO</div>
        <div class="bill-no">{{ $invoice['invoice_no'] ?? ($order['invoice_no'] ?? '---') }}</div>
        <div class="muted">Order: {{ $invoice['order_no'] ?? ($order['order_no'] ?? '---') }}</div>
    </div>

    <div class="divider"></div>

    <div class="meta-grid">
        <div>{{ $invoice['date_time'] ?? now()->format('d M Y h:i A') }}</div>
        <div class="right">{{ strtoupper(str_replace('_', ' ', $dining['order_type'] ?? ($order['order_type'] ?? ''))) }}</div>

        @if(!empty($dining['table_name']))
            <div>Table: {{ $dining['table_name'] }}</div>
            <div class="right">
                @if(!empty($dining['guest_count']))
                    Guests: {{ $dining['guest_count'] }}
                @endif
            </div>
        @endif
    </div>

    @if(!empty($dining['token_code']) || !empty($kotCodes))
        <div class="chips">
            @if(!empty($dining['token_code']))
                <span class="chip">Token {{ $dining['token_code'] }}</span>
            @endif

            @foreach($kotCodes as $kotCode)
                <span class="chip">KOT {{ $kotCode }}</span>
            @endforeach
        </div>
    @endif

    <div class="divider"></div>

    <table>
        <thead>
        <tr>
            <th class="col-item">Item</th>
            <th class="col-qty">Qty</th>
            <th class="col-rate">Rate</th>
            <th class="col-amt">Amt</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td class="item-name">{{ $item['name'] ?? 'Item' }}</td>
                <td class="col-qty">{{ rtrim(rtrim(number_format($item['qty'] ?? 0, 2), '0'), '.') }}</td>
                <td class="col-rate">{{ number_format($item['rate'] ?? 0, 2) }}</td>
                <td class="col-amt">{{ number_format($item['total'] ?? 0, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="row">
        <span>Subtotal</span>
        <span>Rs {{ number_format($receiptTotals['subtotal'] ?? 0, 2) }}</span>
    </div>

    @if(($receiptTotals['discount'] ?? 0) > 0)
        <div class="row">
            <span>Discount</span>
            <span>- Rs {{ number_format($receiptTotals['discount'] ?? 0, 2) }}</span>
        </div>
    @endif

    @if(($receiptTotals['tax'] ?? 0) > 0)
        <div class="row">
            <span>Tax</span>
            <span>Rs {{ number_format($receiptTotals['tax'] ?? 0, 2) }}</span>
        </div>
    @endif

    @if(($receiptTotals['service_charge'] ?? 0) > 0)
        <div class="row">
            <span>Service Charge</span>
            <span>Rs {{ number_format($receiptTotals['service_charge'] ?? 0, 2) }}</span>
        </div>
    @endif

    @if(($receiptTotals['rounding'] ?? 0) != 0)
        <div class="row">
            <span>Rounding</span>
            <span>Rs {{ number_format($receiptTotals['rounding'] ?? 0, 2) }}</span>
        </div>
    @endif

    <div class="row grand-total">
        <span>Grand Total</span>
        <span>Rs {{ number_format($receiptTotals['grand_total'] ?? 0, 2) }}</span>
    </div>

    <div class="divider"></div>

    <div class="row paid">
        <span>Paid Amount</span>
        <span>Rs {{ number_format($receipt['paid_amount'] ?? 0, 2) }}</span>
    </div>

    @if($paymentMethods)
        <div class="row">
            <span>Paid Via</span>
            <span>{{ $paymentMethods }}</span>
        </div>
    @endif

    @if(!empty($qrData['qr_svg_or_url']))
        <div class="divider"></div>
        <div class="qr-wrap">
            <div class="qr-box">
                {!! $qrData['qr_svg_or_url'] !!}
            </div>
            <div class="muted">Scan for invoice / feedback</div>
            @if(!empty($qrData['invoice_url']))
                <div class="muted">{{ $qrData['invoice_url'] }}</div>
            @endif
        </div>
    @endif

    <div class="thanks">Thank you! Visit again</div>

    <div class="divider"></div>

    <div class="center">
        @if(!empty($paychatLogo))
            <img src="{{ $paychatLogo }}" class="paychat-logo" alt="PayChat">
        @endif
        <div class="powered">Powered by PayChat</div>
    </div>
</div>
</body>
</html>
