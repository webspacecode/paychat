<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $brandName }} Loyalty Reward</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #f8fafc;
            color: #0f172a;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .wrap {
            width: min(100%, 560px);
            margin: 0 auto;
            padding: 24px 16px 36px;
        }
        .header {
            padding: 18px 0 16px;
            text-align: center;
        }
        .brand {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 0;
        }
        .sub {
            margin-top: 6px;
            color: #64748b;
            font-size: 14px;
            font-weight: 700;
        }
        .panel {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .summary {
            display: grid;
            gap: 12px;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            font-size: 14px;
        }
        .label {
            color: #64748b;
            font-weight: 800;
        }
        .value {
            text-align: right;
            font-weight: 900;
        }
        .points {
            padding: 20px;
            text-align: center;
            background: #fffbeb;
            border-bottom: 1px solid #fde68a;
        }
        .points strong {
            display: block;
            font-size: 44px;
            line-height: 1;
        }
        .points span {
            display: block;
            margin-top: 6px;
            color: #92400e;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .qr {
            padding: 22px 20px;
            text-align: center;
        }
        .qr-box {
            display: inline-grid;
            place-items: center;
            width: 304px;
            max-width: 100%;
            aspect-ratio: 1;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #fff;
        }
        .qr-box svg {
            width: 280px;
            max-width: 92%;
            height: auto;
        }
        .qr-fallback {
            padding: 14px;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            word-break: break-all;
        }
        .tiers {
            display: grid;
            gap: 10px;
            padding: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .tier {
            border: 1px solid #dbeafe;
            border-radius: 12px;
            background: #eff6ff;
            padding: 12px;
        }
        .tier-title {
            font-size: 14px;
            font-weight: 900;
        }
        .tier-text {
            margin-top: 4px;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 800;
        }
        .footer {
            margin-top: 16px;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 800;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <header class="header">
            <div class="brand">{{ $brandName }}</div>
            <div class="sub">Loyalty reward pass</div>
        </header>

        <section class="panel">
            <div class="summary">
                <div class="row">
                    <span class="label">Customer</span>
                    <span class="value">{{ $reward['customer']['name'] ?: 'Guest' }}</span>
                </div>
                <div class="row">
                    <span class="label">Phone</span>
                    <span class="value">{{ $reward['customer']['masked_phone'] ?: 'Not available' }}</span>
                </div>
            </div>

            <div class="points">
                <strong>{{ $reward['loyalty_balance'] }}</strong>
                <span>Reward Points</span>
            </div>

            <div class="qr">
                <div class="qr-box">
                    @if($qrSvg)
                        {!! $qrSvg !!}
                    @else
                        <div class="qr-fallback">{{ $reward['qr_payload'] }}</div>
                    @endif
                </div>
            </div>

            <div class="tiers">
                @forelse($reward['reward_tiers'] as $tier)
                    <div class="tier">
                        <div class="tier-title">{{ $tier['label'] }} · {{ $tier['points_required'] }} pts</div>
                        <div class="tier-text">{{ $tier['reward_text'] }}</div>
                    </div>
                @empty
                    <div class="tier">
                        <div class="tier-title">No reward currently available</div>
                        <div class="tier-text">Please collect more points and check again.</div>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="footer">Show this QR to the biller for reward validation.</div>
    </main>
</body>
</html>
