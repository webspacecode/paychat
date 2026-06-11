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
                    <h1>One clean POS for your daily counter.</h1>
                    <p class="pc-hero-lede">
                        PayChat brings billing, payments, orders, invoices and reports into one simple cloud workspace for cafes, restaurants, salons, bakeries and service businesses.
                    </p>
                    <div class="pc-hero-actions">
                        <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-primary">Start Free Trial</a>
                        <a href="{{ url('/contact') }}" class="pc-button pc-button-secondary">Talk to PayChat</a>
                    </div>
                </div>

                <div class="pc-product-showcase" aria-label="PayChat POS product screenshot">
                    <div class="pc-device-frame">
                        <div class="pc-device-top">
                            <span></span><span></span><span></span>
                            <strong>PayChat POS</strong>
                        </div>
                        <img src="{{ asset('YOUR_POS_SCREENSHOT.png') }}" alt="PayChat POS dashboard screen" loading="eager">
                    </div>
                    <div class="pc-showcase-strip">
                        <span>Billing</span>
                        <span>Orders</span>
                        <span>Payments</span>
                        <span>Reports</span>
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
                <h2>Everything your counter needs, explained clearly.</h2>
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
            <div class="pc-section-head">
                <p class="pc-eyebrow">Stores on PayChat</p>
                <h2>Real business storefronts powered by PayChat.</h2>
                <p class="mt-4 text-lg leading-8 text-ink/62">Browse live tenant store pages connected to the PayChat platform.</p>
            </div>

            <div class="pc-store-grid">
                @forelse($tenants as $tenant)
                    @php
                        $branding = $tenant->branding;
                        $name = $tenant->name ?? 'PayChat Store';
                        $industry = $tenant->industry ?? 'Local business';
                        $address = $branding->address ?? $tenant->address ?? 'Store details available on page';
                        $logo = $branding && $branding->logo && !str_contains($branding->logo, 'dummyimage.com')
                            ? $branding->logo
                            : null;
                        $shopUrl = url('/store/' . \Illuminate\Support\Str::slug($name));
                        $rating = (float) ($tenant->reviews_avg_rating ?? 0);
                        $reviewCount = (int) ($tenant->reviews_count ?? 0);
                    @endphp
                    <article class="pc-store-card">
                        <div class="pc-store-logo">
                            @if($logo)
                                <img src="{{ $logo }}" alt="{{ $name }} logo" loading="lazy">
                            @else
                                <span>{{ strtoupper(substr($name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="pc-store-body">
                            <p class="pc-store-kicker">{{ $industry }}</p>
                            <h3>{{ $name }}</h3>
                            <p>{{ $address }}</p>
                        </div>
                        <div class="pc-store-meta">
                            <span>{{ $reviewCount > 0 ? number_format($rating, 1) . ' rating' : 'New on PayChat' }}</span>
                            <span>{{ $reviewCount }} reviews</span>
                        </div>
                        <a href="{{ $shopUrl }}" class="pc-store-link" target="_blank" rel="noopener noreferrer">Visit store</a>
                    </article>
                @empty
                    <div class="pc-empty-store">
                        <h3>Tenant stores will appear here after onboarding.</h3>
                        <p>No placeholder stores are shown.</p>
                    </div>
                @endforelse
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
            padding: clamp(2rem, 4vw, 3.5rem) 0 clamp(3.5rem, 7vw, 6rem);
        }

        .pc-hero-banner {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: clamp(2.2rem, 5vw, 4rem);
            align-items: center;
            overflow: hidden;
            min-height: auto;
            background:
                radial-gradient(circle at 50% 0%, rgba(36, 99, 255, .1), transparent 30rem),
                linear-gradient(180deg, #ffffff 0%, #f5f5f7 100%);
            border: 1px solid rgba(29, 29, 31, .08);
            border-radius: clamp(1.25rem, 3vw, 2rem);
            padding: clamp(2rem, 6vw, 5.5rem) clamp(1rem, 4vw, 3rem);
            box-shadow: 0 28px 80px rgba(29, 29, 31, .06);
        }

        .pc-hero-banner::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(36, 99, 255, .055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(36, 99, 255, .055) 1px, transparent 1px);
            background-size: 54px 54px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, .55), transparent 72%);
            pointer-events: none;
        }

        .pc-hero-copy,
        .pc-product-showcase {
            position: relative;
            z-index: 1;
        }

        .pc-hero-copy {
            max-width: 58rem;
            text-align: center;
        }

        .pc-hero-copy h1 {
            max-width: 14ch;
            margin-left: auto;
            margin-right: auto;
            margin-top: 1.25rem;
            color: #1d1d1f;
            font-size: clamp(3rem, 7vw, 6.25rem);
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.02;
        }

        .pc-hero-lede {
            max-width: 46rem;
            margin-left: auto;
            margin-right: auto;
            margin-top: 1.5rem;
            color: rgba(29, 29, 31, .66);
            font-size: clamp(1.05rem, 1.5vw, 1.25rem);
            line-height: 1.7;
        }

        .pc-hero-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .8rem;
            margin-top: 2rem;
        }

        .pc-product-showcase {
            width: min(100%, 68rem);
        }

        .pc-device-frame {
            overflow: hidden;
            border: 1px solid rgba(29, 29, 31, .08);
            border-radius: clamp(1rem, 2.5vw, 1.65rem);
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 30px 70px rgba(29, 29, 31, .12);
        }

        .pc-device-frame img {
            width: 100%;
            aspect-ratio: 1536 / 1024;
            object-fit: cover;
            object-position: top center;
        }

        .pc-device-top {
            display: flex;
            align-items: center;
            gap: .45rem;
            border-bottom: 1px solid rgba(29, 29, 31, .08);
            background: rgba(255, 255, 255, .9);
            padding: .85rem 1rem;
        }

        .pc-device-top span {
            width: .68rem;
            height: .68rem;
            border-radius: 999px;
            background: rgba(31, 94, 255, .2);
        }

        .pc-device-top span:first-child {
            background: #1F5EFF;
        }

        .pc-device-top strong {
            margin-left: auto;
            color: rgba(29, 29, 31, .48);
            font-size: .74rem;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .pc-showcase-strip {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .65rem;
            margin-top: 1rem;
        }

        .pc-showcase-strip span {
            border: 1px solid rgba(31, 94, 255, .12);
            border-radius: 999px;
            background: rgba(255, 255, 255, .82);
            padding: .55rem .85rem;
            color: rgba(29, 29, 31, .62);
            font-size: .8rem;
            font-weight: 700;
        }

        .pc-home-card,
        .pc-final-panel {
            border: 1px solid rgba(255, 255, 255, .72);
            background: rgba(255, 255, 255, .7);
            box-shadow:
                0 18px 54px rgba(29, 29, 31, .06),
                inset 0 1px 0 rgba(255, 255, 255, .9);
            backdrop-filter: blur(24px);
        }

        .pc-cloud-strip {
            border-block: 1px solid rgba(31, 94, 255, .1);
            background: rgba(255, 255, 255, .72);
            padding: 1.1rem 0;
            backdrop-filter: blur(20px);
        }

        .pc-cloud-strip p {
            color: rgba(29, 29, 31, .68);
            font-size: clamp(1rem, 1.6vw, 1.25rem);
            font-weight: 600;
            letter-spacing: 0;
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
            color: #1d1d1f;
            font-size: clamp(2.5rem, 5vw, 4.6rem);
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.05;
        }

        .pc-home-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .pc-home-card {
            min-height: 14rem;
            border-color: rgba(31, 94, 255, .12);
            border-radius: 1.25rem;
            padding: 1.35rem;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .pc-home-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 22px 60px rgba(29, 29, 31, .08);
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
            color: #1d1d1f;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0;
        }

        .pc-home-card p,
        .pc-split p,
        .pc-final-panel p {
            margin-top: .8rem;
            color: rgba(29, 29, 31, .62);
            line-height: 1.7;
        }

        .pc-soft-section {
            background: linear-gradient(180deg, #f5f5f7, #ffffff);
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
            color: rgba(29, 29, 31, .76);
            font-weight: 700;
            box-shadow: 0 14px 42px rgba(29, 29, 31, .05);
            backdrop-filter: blur(18px);
        }

        .pc-final-panel {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 2rem;
            border-color: rgba(29, 29, 31, .08);
            border-radius: 1.6rem;
            background:
                radial-gradient(circle at 16% 0%, rgba(36, 99, 255, .1), transparent 24rem),
                rgba(255, 255, 255, .78);
            padding: clamp(1.4rem, 4vw, 3rem);
        }

        .pc-store-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .pc-store-card {
            display: flex;
            min-height: 22rem;
            flex-direction: column;
            border: 1px solid rgba(29, 29, 31, .08);
            border-radius: 1.25rem;
            background: #fff;
            padding: 1.2rem;
            box-shadow: 0 14px 42px rgba(29, 29, 31, .05);
        }

        .pc-store-logo {
            display: flex;
            height: 4.25rem;
            width: 4.25rem;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid rgba(31, 94, 255, .12);
            border-radius: 1rem;
            background: #f6f9ff;
            color: #1F5EFF;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .pc-store-logo img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }

        .pc-store-body {
            margin-top: 1.35rem;
        }

        .pc-store-kicker {
            color: rgba(36, 99, 255, .72);
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .pc-store-body h3 {
            margin-top: .35rem;
            color: #1d1d1f;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0;
        }

        .pc-store-body p {
            margin-top: .65rem;
            color: rgba(29, 29, 31, .58);
            font-size: .94rem;
            line-height: 1.65;
        }

        .pc-store-meta {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: auto;
            border-top: 1px solid rgba(29, 29, 31, .08);
            padding-top: 1rem;
            color: rgba(29, 29, 31, .5);
            font-size: .82rem;
            font-weight: 600;
        }

        .pc-store-link {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
            border-radius: .85rem;
            background: var(--pc-blue);
            padding: .9rem 1rem;
            color: #fff;
            font-size: .9rem;
            font-weight: 700;
            transition: transform .2s ease, background .2s ease;
        }

        .pc-store-link:hover {
            transform: translateY(-1px);
            background: var(--pc-blue-dark);
        }

        .pc-empty-store {
            grid-column: 1 / -1;
            border: 1px solid rgba(31, 94, 255, .1);
            border-radius: 1rem;
            background: #fff;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 18px 50px rgba(8, 17, 31, .06);
        }

        .pc-empty-store h3 {
            color: #1d1d1f;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .pc-empty-store p {
            margin-top: .5rem;
            color: rgba(29, 29, 31, .54);
        }

        @media (max-width: 1024px) {
            .pc-split,
            .pc-final-panel {
                grid-template-columns: 1fr;
            }

            .pc-home-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pc-store-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .pc-hero-copy h1 {
                max-width: 12ch;
                font-size: clamp(2.65rem, 14vw, 4rem);
            }

            .pc-hero-banner {
                padding: 2rem 1rem;
            }

            .pc-home-grid {
                grid-template-columns: 1fr;
            }

            .pc-home-card {
                min-height: 12rem;
            }

            .pc-store-grid {
                grid-template-columns: 1fr;
            }

            .pc-store-card {
                min-height: auto;
            }
        }
    </style>
@endpush
