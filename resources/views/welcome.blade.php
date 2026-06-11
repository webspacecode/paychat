@extends('layouts.marketing', [
    'title' => 'PayChat - Cloud Native POS for Cafes, Restaurants and Salons',
    'description' => 'PayChat is a cloud native POS for cafes, restaurants, salons, bakeries, retail and service businesses with billing, UPI payments, QR invoices, order flow and reports.',
    'canonical' => url('/'),
])

@section('content')
    <section class="pc-home-hero">
        <div class="pc-container">
            <div class="pc-hero-banner">
                <div class="pc-hero-copy">
                    <p class="pc-eyebrow">
                        <span class="pc-dot"></span>
                        Cloud native POS for growing counters
                    </p>
                    <h1>Premium POS that feels calm at rush hour.</h1>
                    <p class="pc-hero-lede">
                        PayChat brings billing, payments, order flow, invoices and reports into one smooth workspace for cafes, restaurants, salons and service businesses.
                    </p>
                    <div class="pc-hero-actions">
                        <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-primary">Start Free Trial</a>
                        <a href="{{ url('/contact') }}" class="pc-button pc-button-secondary">Book Founder Demo</a>
                    </div>
                </div>

                <div class="pc-hero-board" aria-label="PayChat billing preview">
                    <div class="pc-glass-card pc-pos-card">
                        <div class="pc-card-top">
                            <span></span><span></span><span></span>
                            <strong>PayChat POS</strong>
                        </div>
                        <div class="pc-bill-total pc-workspace-total">
                            <span>Counter workspace</span>
                            <strong>Billing</strong>
                        </div>
                        <div class="pc-bill-list">
                            @foreach([
                                ['Order entry', 'Fast counter flow'],
                                ['Payment modes', 'UPI, cash and records'],
                                ['Customer invoice', 'QR-ready sharing'],
                            ] as [$item, $meta])
                                <div>
                                    <span>{{ $item }}</span>
                                    <strong>{{ $meta }}</strong>
                                </div>
                            @endforeach
                        </div>
                        <div class="pc-payment-row">
                            <span>Cash payment</span>
                            <strong>Confirmed</strong>
                        </div>
                    </div>

                    <div class="pc-floating-card pc-floating-one">
                        <span>Owner view</span>
                        <strong>Daily reports</strong>
                    </div>
                    <div class="pc-floating-card pc-floating-two">
                        <span>Service flow</span>
                        <strong>Orders, tokens, tables</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pc-cloud-strip">
        <div class="pc-container">
            <p>Built for cafes, restaurants, salons, bakeries, retail counters and service teams that need smooth daily control.</p>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container">
            <div class="pc-section-head">
                <p class="pc-eyebrow">The operating layer</p>
                <h2>Everything the counter needs, without the heavy software feeling.</h2>
            </div>

            <div class="pc-home-grid">
                @foreach([
                    ['Counter Billing', 'Create bills quickly for walk-ins, table orders, services and repeat customers.'],
                    ['Payment Clarity', 'Track UPI, cash and other payment records without confusion at closing time.'],
                    ['Paperless Invoices', 'Share clean invoices through QR-friendly customer flows.'],
                    ['Owner Reports', 'See sales, payments and order movement without spreadsheet work.'],
                ] as [$title, $body])
                    <article class="pc-home-card">
                        <span></span>
                        <h3>{{ $title }}</h3>
                        <p>{{ $body }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pc-section pc-soft-section">
        <div class="pc-container">
            <div class="pc-split">
                <div>
                    <p class="pc-eyebrow">Startup simple, premium by design</p>
                    <h2>A focused product, built close to real business counters.</h2>
                    <p>
                        PayChat is young and founder-led, with a practical focus: make the daily sale, order and payment flow feel reliable for teams that do not have time for complicated systems.
                    </p>
                </div>
                <div class="pc-stack">
                    @foreach(['Cafes', 'Salons', 'Bakeries', 'Restaurants', 'Retail', 'Service business'] as $business)
                        <div>{{ $business }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container">
            <div class="pc-final-panel">
                <div>
                    <p class="pc-eyebrow">Start clean</p>
                    <h2>Try the workflow with your real menu or services.</h2>
                    <p>We help you map PayChat to your billing style before you commit.</p>
                </div>
                <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-primary">Start Free Trial</a>
            </div>
        </div>
    </section>
@endsection

@push('head')
    <style>
        .pc-home-hero {
            padding: clamp(2rem, 4vw, 3.5rem) 0 clamp(3rem, 6vw, 5rem);
        }

        .pc-hero-banner {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(22rem, .8fr);
            gap: clamp(2rem, 5vw, 4rem);
            align-items: center;
            overflow: hidden;
            min-height: clamp(36rem, 70vh, 46rem);
            border: 1px solid rgba(31, 94, 255, .12);
            border-radius: clamp(1.4rem, 3vw, 2.4rem);
            background:
                radial-gradient(circle at 20% 12%, rgba(255, 255, 255, .95), transparent 22rem),
                radial-gradient(circle at 74% 30%, rgba(31, 94, 255, .18), transparent 24rem),
                linear-gradient(135deg, #ffffff 0%, #eef5ff 48%, #dfeeff 100%);
            padding: clamp(1.4rem, 5vw, 4rem);
            box-shadow: 0 34px 110px rgba(31, 94, 255, .14);
        }

        .pc-hero-banner::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(31, 94, 255, .08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(31, 94, 255, .08) 1px, transparent 1px);
            background-size: 54px 54px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, .55), transparent 72%);
            pointer-events: none;
        }

        .pc-hero-copy,
        .pc-hero-board {
            position: relative;
            z-index: 1;
        }

        .pc-hero-copy h1 {
            max-width: 12ch;
            margin-top: 1.25rem;
            color: #111827;
            font-size: clamp(3.6rem, 7.4vw, 6.7rem);
            font-weight: 900;
            letter-spacing: -.065em;
            line-height: .88;
        }

        .pc-hero-lede {
            max-width: 43rem;
            margin-top: 1.5rem;
            color: rgba(17, 24, 39, .66);
            font-size: clamp(1.05rem, 1.5vw, 1.25rem);
            line-height: 1.7;
        }

        .pc-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .8rem;
            margin-top: 2rem;
        }

        .pc-hero-board {
            min-height: 31rem;
        }

        .pc-glass-card,
        .pc-floating-card,
        .pc-home-card,
        .pc-final-panel {
            border: 1px solid rgba(255, 255, 255, .72);
            background: rgba(255, 255, 255, .7);
            box-shadow:
                0 24px 80px rgba(31, 94, 255, .13),
                inset 0 1px 0 rgba(255, 255, 255, .85);
            backdrop-filter: blur(24px);
        }

        .pc-pos-card {
            overflow: hidden;
            border-radius: 1.55rem;
        }

        .pc-card-top {
            display: flex;
            align-items: center;
            gap: .45rem;
            border-bottom: 1px solid rgba(31, 94, 255, .1);
            padding: 1rem;
        }

        .pc-card-top span {
            width: .68rem;
            height: .68rem;
            border-radius: 999px;
            background: rgba(31, 94, 255, .2);
        }

        .pc-card-top span:first-child {
            background: #1F5EFF;
        }

        .pc-card-top strong {
            margin-left: auto;
            color: rgba(17, 24, 39, .48);
            font-size: .75rem;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .pc-bill-total {
            margin: 1rem;
            border-radius: 1.2rem;
            background: #1F5EFF;
            padding: 1.35rem;
            color: white;
        }

        .pc-bill-total span,
        .pc-floating-card span,
        .pc-payment-row span {
            display: block;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .68;
        }

        .pc-bill-total strong {
            display: block;
            margin-top: .35rem;
            font-size: clamp(2.4rem, 5vw, 4.2rem);
            font-weight: 900;
            letter-spacing: -.055em;
            line-height: .9;
        }

        .pc-bill-list {
            display: grid;
            gap: .65rem;
            padding: 0 1rem 1rem;
        }

        .pc-bill-list div,
        .pc-payment-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border: 1px solid rgba(31, 94, 255, .1);
            border-radius: 1rem;
            background: rgba(255, 255, 255, .68);
            padding: .95rem 1rem;
            color: rgba(17, 24, 39, .72);
            font-size: .94rem;
            font-weight: 750;
        }

        .pc-bill-list strong,
        .pc-payment-row strong,
        .pc-floating-card strong {
            color: #111827;
            font-weight: 900;
        }

        .pc-payment-row {
            margin: 0 1rem 1rem;
            background: rgba(31, 94, 255, .07);
        }

        .pc-floating-card {
            position: absolute;
            border-radius: 1.1rem;
            padding: 1rem 1.15rem;
        }

        .pc-floating-one {
            right: -1rem;
            top: 3rem;
        }

        .pc-floating-two {
            bottom: 1.2rem;
            left: -1.4rem;
        }

        .pc-cloud-strip {
            border-block: 1px solid rgba(31, 94, 255, .1);
            background: rgba(255, 255, 255, .72);
            padding: 1.1rem 0;
            backdrop-filter: blur(20px);
        }

        .pc-cloud-strip p {
            color: rgba(17, 24, 39, .68);
            font-size: clamp(1rem, 1.6vw, 1.25rem);
            font-weight: 800;
            letter-spacing: -.01em;
            text-align: center;
        }

        .pc-section-head {
            max-width: 48rem;
            margin-bottom: 2rem;
        }

        .pc-section-head h2,
        .pc-split h2,
        .pc-final-panel h2 {
            margin-top: 1rem;
            color: #111827;
            font-size: clamp(2.5rem, 5vw, 4.6rem);
            font-weight: 900;
            letter-spacing: -.055em;
            line-height: .95;
        }

        .pc-home-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .pc-home-card {
            min-height: 15rem;
            border-color: rgba(31, 94, 255, .12);
            border-radius: 1.25rem;
            padding: 1.35rem;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .pc-home-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 30px 90px rgba(31, 94, 255, .14);
        }

        .pc-home-card > span {
            display: block;
            width: 2.2rem;
            height: .35rem;
            border-radius: 999px;
            background: #1F5EFF;
        }

        .pc-home-card h3 {
            margin-top: 3.2rem;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: -.03em;
        }

        .pc-home-card p,
        .pc-split p,
        .pc-final-panel p {
            margin-top: .8rem;
            color: rgba(17, 24, 39, .62);
            line-height: 1.7;
        }

        .pc-soft-section {
            background: linear-gradient(180deg, #f6f9ff, #ffffff);
        }

        .pc-split {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(20rem, .7fr);
            align-items: center;
            gap: clamp(2rem, 5vw, 4rem);
        }

        .pc-stack {
            display: grid;
            gap: .8rem;
        }

        .pc-stack div {
            border: 1px solid rgba(31, 94, 255, .12);
            border-radius: 1rem;
            background: rgba(255, 255, 255, .76);
            padding: 1rem 1.1rem;
            color: rgba(17, 24, 39, .76);
            font-weight: 900;
            box-shadow: 0 18px 60px rgba(31, 94, 255, .07);
            backdrop-filter: blur(18px);
        }

        .pc-final-panel {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 2rem;
            border-color: rgba(31, 94, 255, .12);
            border-radius: 1.6rem;
            background:
                radial-gradient(circle at 16% 0%, rgba(31, 94, 255, .14), transparent 24rem),
                rgba(255, 255, 255, .78);
            padding: clamp(1.4rem, 4vw, 3rem);
        }

        @media (max-width: 1024px) {
            .pc-hero-banner,
            .pc-split,
            .pc-final-panel {
                grid-template-columns: 1fr;
            }

            .pc-home-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pc-floating-one {
                right: 1rem;
            }

            .pc-floating-two {
                left: 1rem;
            }
        }

        @media (max-width: 640px) {
            .pc-hero-banner {
                min-height: auto;
            }

            .pc-hero-copy h1 {
                max-width: 10ch;
            }

            .pc-hero-board {
                min-height: 29rem;
            }

            .pc-home-grid {
                grid-template-columns: 1fr;
            }

            .pc-home-card {
                min-height: 12rem;
            }
        }
    </style>
@endpush
