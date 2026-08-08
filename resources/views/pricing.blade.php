@extends('layouts.marketing', [
    'title' => 'PayChat Pricing | POS Software Pricing for Cafes, Restaurants & Salons',
    'description' => 'Explore PayChat pricing for cafes, restaurants, bakeries, QSRs and salons. Start with a ₹2,599 one-time setup package and choose affordable POS plans as your business grows.',
    'keywords' => 'POS software pricing India, cafe POS software, salon billing software, restaurant billing software, POS software for cafes, bakery POS software, restaurant POS pricing India, QR menu for restaurants, inventory software for small business, cloud POS for restaurants, affordable POS software, POS software for small businesses, POS software Nagpur, cafe billing software, multi-counter POS, multi-outlet POS',
    'canonical' => url('/pricing'),
    'softwareApplicationOffers' => false,
])

@php
    $pricing = [
        'launch' => [
            'name' => 'PayChat Launch',
            'label' => 'One-Time Setup',
            'oneTime' => 2599,
            'countersIncluded' => 1,
            'heading' => 'Get your business ready with PayChat',
            'bestFor' => 'Starting or digitising your business.',
            'description' => 'Pay once for installation, menu setup, training, QR setup, and your basic digital presence.',
            'cta' => 'Get PayChat for ₹2,599',
            'href' => url('/start-free-trial'),
            'features' => [
                'PayChat POS installation',
                'Business account setup',
                'Menu/catalog creation',
                'Product and category setup',
                'Basic pricing configuration',
                'Staff/owner training',
                'QR digital menu',
                '1 physical/self-service QR plate',
                'Simple business website',
                'Customer review page',
                'Customer feedback page',
                'Receipt/invoice configuration',
                'Basic reports setup',
                '1 billing counter',
                'Initial onboarding support',
            ],
        ],
        'operate' => [
            'name' => 'PayChat Operate',
            'monthly' => 299,
            'yearly' => 2999,
            'countersIncluded' => 2,
            'heading' => 'Run your daily operations',
            'bestFor' => 'Small businesses doing regular daily billing.',
            'description' => 'Small cafes, restaurants, bakeries, salons, takeaway shops, and local businesses that want a simple daily POS system.',
            'cta' => 'Choose Operate',
            'href' => url('/start-free-trial'),
            'underCta' => 'Best for businesses running 1-2 billing counters',
            'features' => [
                'Everything needed for daily POS billing',
                'Up to 2 billing counters',
                'Inventory tracking',
                'Low-stock alerts',
                'Customer database',
                'Staff accounts',
                'Held orders',
                'Multi-bill support',
                'Offline billing/sync',
                'Invoice history',
                'Basic and detailed reports',
                'Cash / UPI / card payment recording',
                'Order history',
                'WhatsApp invoice sharing where supported',
                'Cloud backup',
                'Standard support',
            ],
        ],
        'grow' => [
            'name' => 'PayChat Grow',
            'monthly' => 599,
            'yearly' => 5999,
            'countersIncluded' => 4,
            'badge' => 'Most Popular',
            'heading' => 'Manage your complete restaurant workflow',
            'bestFor' => 'Busy cafes and restaurants with kitchen operations.',
            'description' => 'Growing cafes, restaurants, QSRs, bakeries, and businesses with kitchen operations.',
            'cta' => 'Choose Grow',
            'href' => url('/start-free-trial'),
            'underCta' => 'Ideal for busy businesses with multiple counters or kitchen operations',
            'features' => [
                'Everything in Operate',
                'Up to 4 billing counters',
                'Kitchen Display System',
                'Kitchen token management',
                'QR ordering',
                'Table ordering',
                'Advanced inventory',
                'Recipe management',
                'Ingredient tracking',
                'Food cost visibility',
                'Customer loyalty',
                'Coupons and promotions',
                'Customer analytics',
                'Advanced reports',
                'Staff permissions',
                'Advanced order management',
                'Bakery/future-order workflows where enabled',
                'Priority support',
            ],
        ],
        'scale' => [
            'name' => 'PayChat Scale',
            'startingMonthly' => 999,
            'heading' => 'For multi-location businesses',
            'bestFor' => 'Growing brands with multiple outlets.',
            'description' => 'Built for restaurant groups, cafes, bakeries, franchises, and businesses managing multiple locations.',
            'cta' => 'Talk to PayChat',
            'href' => url('/contact'),
            'underCta' => 'Pricing depends on outlets, counters, and required integrations.',
            'features' => [
                'Everything in Grow',
                'Multiple outlets',
                'Central dashboard',
                'Branch-wise sales',
                'Branch-wise inventory',
                'Central menu management',
                'Central owner reporting',
                'Location-level staff management',
                'Multi-location analytics',
                'Flexible counter limits',
                'Dedicated onboarding',
                'Custom integrations',
                'Priority support',
                'Future aggregator integrations where available',
            ],
        ],
        'addons' => [
            'extraCounterMonthly' => 149,
            'extraCounterYearly' => 1499,
            'extraOutletStartingMonthly' => 349,
        ],
    ];

    $comparisonRows = [
        ['Feature', 'Launch', 'Operate', 'Grow', 'Scale'],
        ['Initial setup', 'Yes', '-', '-', 'Custom'],
        ['Menu/catalog setup', 'Yes', 'Included', 'Included', 'Included'],
        ['POS billing', 'Setup', 'Yes', 'Yes', 'Yes'],
        ['Counters', '1', '2', '4', 'Flexible'],
        ['Inventory', 'Basic setup', 'Yes', 'Advanced', 'Advanced'],
        ['Customer database', 'Basic', 'Yes', 'Yes', 'Yes'],
        ['Kitchen Display', '-', '-', 'Yes', 'Yes'],
        ['QR ordering', 'QR menu', 'Optional', 'Yes', 'Yes'],
        ['Loyalty', '-', '-', 'Yes', 'Yes'],
        ['Advanced analytics', '-', 'Limited', 'Yes', 'Yes'],
        ['Multiple outlets', '-', 'Add-on', 'Add-on', 'Yes'],
        ['Dedicated support', 'Onboarding', 'Standard', 'Priority', 'Dedicated'],
    ];

    $faqs = [
        ['Is PayChat ₹2,599 a one-time payment?', '₹2,599 is the one-time PayChat Launch setup package. It covers onboarding services such as installation, business setup, menu/catalog creation, training, QR menu setup and initial digital setup. Businesses that want ongoing operational features can choose a monthly or yearly PayChat plan.'],
        ['Do I need to pay monthly after buying PayChat Launch?', 'PayChat Launch is onboarding and setup. Monthly or yearly plans are selected separately when your business wants ongoing operations such as daily billing, inventory, reports, kitchen workflows, loyalty, or multiple counters.'],
        ['Can I use PayChat on multiple billing counters?', 'Yes. Operate includes up to 2 counters and Grow includes up to 4 counters. Additional counters can be added when required.'],
        ['What is considered a billing counter?', 'A phone, tablet or computer being used by a cashier to generate orders or bills at the same outlet is treated as a billing counter.'],
        ['What is the difference between a counter and outlet?', 'A counter is another billing device inside the same business location. An outlet is a separate physical branch with its own stock, staff, sales, reports, kitchens or operating hours.'],
        ['Can PayChat work for cafes?', 'Yes. PayChat works for cafes, coffee shops, QSRs, takeaway businesses and small restaurants that need cafe billing software, QR menus, customers, stock and reports.'],
        ['Can PayChat work for bakeries?', 'Yes. PayChat supports regular bakery POS billing, products, customers, inventory and optional bakery/future-order workflows where enabled.'],
        ['Does PayChat support QR menus?', 'Yes. PayChat Launch includes QR digital menu setup so customers can view your menu from a QR code.'],
        ['Is expensive POS hardware required?', 'PayChat is designed to work with compatible phones, tablets and computers, helping businesses avoid unnecessary proprietary POS hardware.'],
        ['Can I add another outlet later?', 'Yes. Additional outlets can be added without rebuilding the entire account. Pricing depends on counters, features, kitchen setup, central inventory and integrations.'],
        ['Which PayChat plan should I choose?', 'Choose Launch when you are getting started, Operate for simple daily billing, Grow for restaurant and kitchen workflows, and Scale for multi-location operations.'],
    ];

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($faq) => [
            '@type' => 'Question',
            'name' => $faq[0],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq[1],
            ],
        ], $faqs),
    ];

    $productSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => 'PayChat POS',
            'description' => 'POS software, cafe billing software, salon billing software, bakery POS software and cloud POS for Indian local businesses.',
        'brand' => [
            '@type' => 'Brand',
            'name' => 'PayChat',
        ],
        'offers' => [
            '@type' => 'AggregateOffer',
            'priceCurrency' => 'INR',
            'lowPrice' => '2599',
            'offerCount' => '4',
            'offers' => [
                ['@type' => 'Offer', 'name' => 'PayChat Launch', 'price' => '2599', 'priceCurrency' => 'INR', 'description' => 'One-time setup package'],
                ['@type' => 'Offer', 'name' => 'PayChat Operate', 'price' => '299', 'priceCurrency' => 'INR', 'description' => 'Monthly software plan'],
                ['@type' => 'Offer', 'name' => 'PayChat Grow', 'price' => '599', 'priceCurrency' => 'INR', 'description' => 'Monthly software plan'],
                ['@type' => 'Offer', 'name' => 'PayChat Scale', 'price' => '999', 'priceCurrency' => 'INR', 'description' => 'Starting monthly price for multi-location businesses'],
            ],
        ],
    ];
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <style>
        .pricing-card {
            border: 1px solid rgba(29, 29, 31, .1);
            border-radius: 1rem;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 18px 46px rgba(29, 29, 31, .055);
        }
        .pricing-card-featured {
            border-color: rgba(36, 99, 255, .36);
            box-shadow: 0 24px 70px rgba(36, 99, 255, .16);
        }
        .pricing-check {
            display: inline-grid;
            width: 1.3rem;
            height: 1.3rem;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 999px;
            background: rgba(15, 159, 143, .12);
            color: #0f766e;
            font-size: .78rem;
            font-weight: 900;
        }
        .pricing-toggle button[aria-pressed="true"] {
            background: #1d1d1f;
            color: #fff;
            box-shadow: 0 12px 26px rgba(29, 29, 31, .14);
        }
        .pricing-input {
            min-height: 3rem;
            width: 100%;
            border-radius: .8rem;
            border: 1px solid rgba(29, 29, 31, .12);
            background: #fff;
            padding: .75rem .9rem;
            font-weight: 800;
            outline: none;
        }
        .pricing-input:focus {
            border-color: rgba(36, 99, 255, .5);
            box-shadow: 0 0 0 4px rgba(36, 99, 255, .12);
        }
        .pricing-compare-grid {
            display: grid;
            grid-template-columns: minmax(140px, 1.15fr) repeat(4, minmax(0, .8fr));
        }
        @media (max-width: 760px) {
            .pricing-compare-grid {
                display: block;
            }
        }
    </style>
@endpush

@section('content')
<main id="pricing-page">
    <section class="pc-surface-hero">
        <div class="pc-container grid gap-10 py-12 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-center lg:py-20">
            <div class="max-w-4xl">
                <p class="pc-eyebrow">Simple Pricing for Cafes, Restaurants & Salons</p>
                <h1 class="pc-page-title mt-5 text-ink">Simple POS Pricing for Indian Local Businesses</h1>
                <p class="mt-5 max-w-3xl text-lg leading-8 text-ink/68">
                    Start PayChat for <strong class="text-ink">₹2,599</strong>. Get your POS installed, menu digitised, QR menu ready, staff trained, and your business online with one simple setup package.
                    Then choose an optional operations plan as your business grows.
                </p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-primary">Get Started</a>
                    <a href="#plans" class="pc-button pc-button-secondary">Compare Plans</a>
                </div>
                <div class="mt-6 grid gap-2 text-sm font-bold text-ink/68 sm:grid-cols-2 lg:grid-cols-3">
                    <span>Built for Indian local businesses</span>
                    <span>No expensive hardware required</span>
                    <span>Add counters when you grow</span>
                    <span>Clear monthly and yearly pricing</span>
                    <span>Onboarding and training available</span>
                    <span>Works on compatible phones, tablets and computers</span>
                </div>
            </div>

            <aside class="pricing-card p-5 lg:p-6" aria-label="PayChat Launch price summary">
                <p class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black uppercase text-blue-700">One-Time Setup</p>
                <h2 class="mt-4 text-2xl font-black">Start PayChat for</h2>
                <div class="mt-3 text-5xl font-black tracking-normal">₹2,599</div>
                <p class="mt-3 text-sm font-bold leading-6 text-ink/62">
                    This is the Launch setup package, not lifetime SaaS access.
                </p>
                <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-primary mt-6 w-full">Start with PayChat Launch</a>
            </aside>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container">
            <div class="mx-auto max-w-3xl text-center">
                <p class="pc-eyebrow">How PayChat pricing works</p>
                <h2 class="pc-section-title mt-4">Start simple. Pay for more only when your business needs more.</h2>
            </div>
            <div class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach([
                    ['Launch', '₹2,599 one-time', 'We set up your business.'],
                    ['Operate', 'Choose a software plan', 'Pick the plan that matches your daily operations.'],
                    ['Grow', 'Add when needed', 'Add counters, outlets or advanced tools only when required.'],
                ] as $index => [$step, $price, $copy])
                    <article class="pricing-card p-5">
                        <div class="grid h-10 w-10 place-items-center rounded-full bg-blue-50 text-sm font-black text-blue-700">{{ $index + 1 }}</div>
                        <h3 class="mt-4 text-xl font-black">{{ $step }}</h3>
                        <p class="mt-2 text-sm font-black text-ink">{{ $price }}</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-ink/62">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
            <p class="mt-6 text-center text-base font-black text-ink">You don't have to pay for features your business doesn't use.</p>
        </div>
    </section>

    <section id="plans" class="pc-section bg-[#f7faf9]">
        <div class="pc-container">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="pc-eyebrow">Plans</p>
                    <h2 class="pc-section-title mt-4">Choose setup first, then operations.</h2>
                    <p class="mt-4 text-base font-semibold leading-7 text-ink/64">PayChat Launch is a one-time onboarding package. Operate, Grow and Scale are software plans for ongoing billing, stock, customers, reports, counters, kitchen and outlet workflows.</p>
                </div>
                <div class="pricing-toggle inline-flex rounded-full border border-ink/10 bg-white p-1 shadow-soft" aria-label="Billing period">
                    <button type="button" class="min-h-[44px] rounded-full px-5 text-sm font-black" data-billing-toggle="monthly" aria-pressed="true">Monthly</button>
                    <button type="button" class="min-h-[44px] rounded-full px-5 text-sm font-black text-ink/62" data-billing-toggle="yearly" aria-pressed="false">Yearly - Save More</button>
                </div>
            </div>

            <div class="mt-8 grid gap-4 lg:grid-cols-4">
                <article class="pricing-card flex flex-col p-5">
                    <p class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-ink/60">{{ $pricing['launch']['label'] }}</p>
                    <h3 class="mt-4 text-2xl font-black">{{ $pricing['launch']['name'] }}</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-ink/62">{{ $pricing['launch']['heading'] }}</p>
                    <div class="mt-5">
                        <div class="text-4xl font-black">₹{{ number_format($pricing['launch']['oneTime']) }}</div>
                        <div class="mt-1 text-sm font-black text-ink/55">one-time</div>
                    </div>
                    <p class="mt-4 text-sm"><strong>Best for:</strong> {{ $pricing['launch']['bestFor'] }}</p>
                    <ul class="mt-5 grow space-y-3 text-sm font-semibold leading-6 text-ink/68">
                        @foreach($pricing['launch']['features'] as $feature)
                            <li class="flex gap-3"><span class="pricing-check" aria-hidden="true">✓</span><span>{{ $feature }}</span></li>
                        @endforeach
                    </ul>
                    <a href="{{ $pricing['launch']['href'] }}" class="pc-button pc-button-secondary mt-7 w-full">{{ $pricing['launch']['cta'] }}</a>
                    <p class="mt-3 text-xs font-bold leading-5 text-ink/52">One-time onboarding charge. Software plans are available separately for businesses that want ongoing operational features.</p>
                </article>

                @foreach(['operate', 'grow'] as $key)
                    @php($plan = $pricing[$key])
                    <article class="pricing-card {{ $key === 'grow' ? 'pricing-card-featured' : '' }} flex flex-col p-5" data-plan-card="{{ $key }}" data-monthly="{{ $plan['monthly'] }}" data-yearly="{{ $plan['yearly'] }}">
                        @if(!empty($plan['badge']))
                            <p class="inline-flex w-fit rounded-full bg-blue-600 px-3 py-1 text-xs font-black uppercase text-white">{{ $plan['badge'] }}</p>
                        @elseif($key === 'operate')
                            <p class="inline-flex w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-black uppercase text-emerald-700">Affordable daily POS</p>
                        @endif
                        <h3 class="mt-4 text-2xl font-black">{{ $plan['name'] }}</h3>
                        <p class="mt-2 text-sm font-semibold leading-6 text-ink/62">{{ $plan['heading'] }}</p>
                        <div class="mt-5">
                            <div class="text-4xl font-black" data-plan-price>₹{{ number_format($plan['monthly']) }}<span class="text-lg font-black text-ink/50">/month</span></div>
                            <div class="mt-1 text-sm font-black text-blue-700" data-plan-equivalent>{{ $key === 'operate' ? 'Save with yearly billing' : 'Most value for restaurants' }}</div>
                        </div>
                        <p class="mt-4 text-sm"><strong>Best for:</strong> {{ $plan['bestFor'] }}</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-ink/60">{{ $plan['description'] }}</p>
                        <ul class="mt-5 grow space-y-3 text-sm font-semibold leading-6 text-ink/68">
                            @foreach($plan['features'] as $feature)
                                <li class="flex gap-3"><span class="pricing-check" aria-hidden="true">✓</span><span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>
                        <a href="{{ $plan['href'] }}" class="pc-button pc-button-primary mt-7 w-full">{{ $plan['cta'] }}</a>
                        <p class="mt-3 text-xs font-bold leading-5 text-ink/52">{{ $plan['underCta'] }}</p>
                    </article>
                @endforeach

                <article class="pricing-card flex flex-col p-5">
                    <p class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-ink/60">Multi-location</p>
                    <h3 class="mt-4 text-2xl font-black">{{ $pricing['scale']['name'] }}</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-ink/62">{{ $pricing['scale']['heading'] }}</p>
                    <div class="mt-5">
                        <div class="text-3xl font-black">Starting from ₹{{ number_format($pricing['scale']['startingMonthly']) }}<span class="text-lg font-black text-ink/50">/month</span></div>
                    </div>
                    <p class="mt-4 text-sm"><strong>Best for:</strong> {{ $pricing['scale']['bestFor'] }}</p>
                    <p class="mt-2 text-sm font-semibold leading-6 text-ink/60">{{ $pricing['scale']['description'] }}</p>
                    <ul class="mt-5 grow space-y-3 text-sm font-semibold leading-6 text-ink/68">
                        @foreach($pricing['scale']['features'] as $feature)
                            <li class="flex gap-3"><span class="pricing-check" aria-hidden="true">✓</span><span>{{ $feature }}</span></li>
                        @endforeach
                    </ul>
                    <a href="{{ $pricing['scale']['href'] }}" class="pc-button pc-button-secondary mt-7 w-full">{{ $pricing['scale']['cta'] }}</a>
                    <p class="mt-3 text-xs font-bold leading-5 text-ink/52">{{ $pricing['scale']['underCta'] }}</p>
                </article>
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container grid gap-6 lg:grid-cols-[.9fr_1.1fr] lg:items-start">
            <div>
                <p class="pc-eyebrow">Counters</p>
                <h2 class="pc-section-title mt-4">Need more billing counters?</h2>
                <p class="mt-4 text-base font-semibold leading-7 text-ink/64">A counter is another billing device or cashier operating inside the same outlet.</p>
                <div class="mt-5 rounded-2xl bg-slate-50 p-5 text-sm font-bold leading-7 text-ink/70">
                    <p class="font-black text-ink">Cafe ABC Dharampeth may use:</p>
                    <p>Counter 1 - Main billing desk</p>
                    <p>Counter 2 - Takeaway counter</p>
                    <p>Counter 3 - Tablet/mobile billing</p>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <article class="pricing-card p-5">
                    <h3 class="text-lg font-black">Operate includes</h3>
                    <div class="mt-3 text-4xl font-black">2</div>
                    <p class="mt-1 text-sm font-bold text-ink/60">billing counters</p>
                </article>
                <article class="pricing-card p-5">
                    <h3 class="text-lg font-black">Grow includes</h3>
                    <div class="mt-3 text-4xl font-black">4</div>
                    <p class="mt-1 text-sm font-bold text-ink/60">billing counters</p>
                </article>
                <article class="pricing-card p-5">
                    <h3 class="text-lg font-black">Extra counter</h3>
                    <div class="mt-3 text-3xl font-black">₹{{ number_format($pricing['addons']['extraCounterMonthly']) }}<span class="text-base text-ink/50">/month</span></div>
                    <p class="mt-1 text-sm font-bold text-ink/60">or ₹{{ number_format($pricing['addons']['extraCounterYearly']) }}/year</p>
                </article>
            </div>
            <div class="lg:col-span-2 rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm font-bold leading-7 text-blue-900">
                If adding counters makes another plan more affordable, PayChat should recommend the better plan automatically. Example: Operate at ₹299/month plus one additional counter at ₹149/month totals ₹448/month. Grow may be better value at ₹599/month if you also need kitchen, loyalty, or advanced restaurant features.
            </div>
        </div>
    </section>

    <section class="pc-section bg-[#f7faf9]">
        <div class="pc-container">
            <div class="mx-auto max-w-3xl text-center">
                <p class="pc-eyebrow">Counter vs Outlet</p>
                <h2 class="pc-section-title mt-4">Additional counters cost less because they work under the same location.</h2>
            </div>
            <div class="mt-8 grid gap-4 lg:grid-cols-2">
                <article class="pricing-card p-6">
                    <h3 class="text-2xl font-black">Counter</h3>
                    <p class="mt-3 text-sm font-semibold leading-6 text-ink/64">Another billing device operating at the same business location.</p>
                    <div class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm font-bold leading-7">
                        <p>Cafe ABC, Dharampeth</p>
                        <p>Counter 1</p>
                        <p>Counter 2</p>
                        <p>Counter 3</p>
                    </div>
                    <p class="mt-5 text-sm font-black">They share the same outlet, business, menu, usually the same inventory, and the same reports.</p>
                </article>
                <article class="pricing-card p-6">
                    <h3 class="text-2xl font-black">Outlet</h3>
                    <p class="mt-3 text-sm font-semibold leading-6 text-ink/64">A separate physical business location.</p>
                    <div class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm font-bold leading-7">
                        <p>Cafe ABC - Dharampeth</p>
                        <p>Cafe ABC - Manish Nagar</p>
                        <p>Cafe ABC - Wardha Road</p>
                    </div>
                    <p class="mt-5 text-sm font-black">Different outlets may have different stock, staff, sales, reports, kitchens and operating hours.</p>
                </article>
            </div>
            <div class="mt-5 pricing-card p-5">
                <h3 class="text-xl font-black">Additional outlet pricing</h3>
                <p class="mt-2 text-base font-semibold leading-7 text-ink/64">Operate and Grow customers can add another outlet starting from <strong>₹{{ number_format($pricing['addons']['extraOutletStartingMonthly']) }}/month per additional outlet</strong>. Pricing may depend on counters, features enabled, kitchen setup, central inventory requirements and integrations. Large multi-location businesses should choose PayChat Scale.</p>
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container grid gap-8 lg:grid-cols-[.85fr_1.15fr] lg:items-start">
            <div>
                <p class="pc-eyebrow">Calculator</p>
                <h2 class="pc-section-title mt-4">Estimate your PayChat cost</h2>
                <p class="mt-4 text-base font-semibold leading-7 text-ink/64">Use this simple estimator for monthly planning. It does not ask for personal information and does not require login.</p>
            </div>
            <section class="pricing-card p-5 lg:p-6" data-pricing-calculator
                data-operate-monthly="{{ $pricing['operate']['monthly'] }}"
                data-grow-monthly="{{ $pricing['grow']['monthly'] }}"
                data-scale-monthly="{{ $pricing['scale']['startingMonthly'] }}"
                data-operate-counters="{{ $pricing['operate']['countersIncluded'] }}"
                data-grow-counters="{{ $pricing['grow']['countersIncluded'] }}"
                data-extra-counter="{{ $pricing['addons']['extraCounterMonthly'] }}"
                data-extra-outlet="{{ $pricing['addons']['extraOutletStartingMonthly'] }}">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="text-sm font-black">
                        Number of outlets
                        <input class="pricing-input mt-2" min="1" max="20" type="number" value="1" data-calc-outlets>
                    </label>
                    <label class="text-sm font-black">
                        Number of billing counters
                        <input class="pricing-input mt-2" min="1" max="50" type="number" value="2" data-calc-counters>
                    </label>
                    <label class="text-sm font-black md:col-span-2">
                        Business type
                        <select class="pricing-input mt-2" data-calc-business>
                            @foreach(['Cafe', 'Restaurant', 'QSR', 'Bakery', 'Takeaway', 'Other'] as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-black md:col-span-2">
                        Base plan
                        <select class="pricing-input mt-2" data-calc-plan>
                            <option value="operate">Operate - ₹{{ number_format($pricing['operate']['monthly']) }}/month</option>
                            <option value="grow">Grow - ₹{{ number_format($pricing['grow']['monthly']) }}/month</option>
                            <option value="scale">Scale - starting ₹{{ number_format($pricing['scale']['startingMonthly']) }}/month</option>
                        </select>
                    </label>
                </div>
                <div class="mt-5 rounded-2xl bg-slate-50 p-5">
                    <p class="text-sm font-black uppercase text-ink/45">Estimated monthly cost</p>
                    <div class="mt-2 text-4xl font-black" data-calc-total>₹299/month</div>
                    <p class="mt-3 text-sm font-bold leading-6 text-ink/62" data-calc-breakdown>Operate ₹299 + 0 extra counters + 0 additional outlets.</p>
                    <p class="mt-3 rounded-xl bg-blue-50 px-4 py-3 text-sm font-black leading-6 text-blue-800" data-calc-recommendation>Start with Operate for simple daily billing.</p>
                </div>
            </section>
        </div>
    </section>

    <section class="pc-section bg-[#f7faf9]">
        <div class="pc-container">
            <div class="mx-auto max-w-3xl text-center">
                <p class="pc-eyebrow">Feature Comparison</p>
                <h2 class="pc-section-title mt-4">Compare only what matters.</h2>
            </div>
            <div class="mt-8 hidden overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-soft md:block">
                @foreach($comparisonRows as $rowIndex => $row)
                    <div class="pricing-compare-grid {{ $rowIndex === 0 ? 'bg-slate-950 text-white' : 'border-t border-ink/10' }}">
                        @foreach($row as $cellIndex => $cell)
                            <div class="px-4 py-4 text-sm {{ $rowIndex === 0 || $cellIndex === 0 ? 'font-black' : 'font-bold text-ink/66' }}">{{ $cell }}</div>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="mt-8 grid gap-4 md:hidden">
                @foreach(['Launch', 'Operate', 'Grow', 'Scale'] as $planIndex => $planName)
                    <article class="pricing-card p-5">
                        <h3 class="text-xl font-black">{{ $planName }}</h3>
                        <ul class="mt-4 space-y-2 text-sm font-bold leading-6 text-ink/66">
                            @foreach(array_slice($comparisonRows, 1) as $row)
                                <li><strong class="text-ink">{{ $row[0] }}:</strong> {{ $row[$planIndex + 1] }}</li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container">
            <div class="mx-auto max-w-3xl text-center">
                <p class="pc-eyebrow">Business Types</p>
                <h2 class="pc-section-title mt-4">One PayChat platform for different local businesses</h2>
            </div>
            <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['Cafe POS', 'Simple billing, customer management, inventory and QR menus for cafes and coffee shops.'],
                    ['Restaurant POS', 'Manage billing, kitchen orders, tables, inventory and reporting.'],
                    ['Bakery POS', 'Manage daily billing, products, customers and optional advance/future orders.'],
                    ['Salon POS', 'Manage service billing, customer records, staff and simple reports for salons.'],
                    ['QSR POS', 'Handle fast billing, takeaway orders, kitchen tokens and multiple counters.'],
                ] as [$title, $copy])
                    <article class="pricing-card p-5">
                        <h3 class="text-xl font-black">{{ $title }}</h3>
                        <p class="mt-3 text-sm font-semibold leading-6 text-ink/64">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pc-section bg-[#f7faf9]">
        <div class="pc-container">
            <div class="mx-auto max-w-3xl text-center">
                <p class="pc-eyebrow">FAQ</p>
                <h2 class="pc-section-title mt-4">Pricing questions from real business owners</h2>
            </div>
            <div class="mx-auto mt-8 max-w-4xl space-y-3">
                @foreach($faqs as [$question, $answer])
                    <details class="pricing-card p-5">
                        <summary class="cursor-pointer text-base font-black">{{ $question }}</summary>
                        <p class="mt-3 text-sm font-semibold leading-7 text-ink/64">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container">
            <div class="pricing-card p-6 text-center lg:p-10">
                <p class="pc-eyebrow">Need help choosing?</p>
                <h2 class="pc-section-title mt-4">Not sure which plan you need?</h2>
                <p class="mx-auto mt-4 max-w-2xl text-base font-semibold leading-7 text-ink/64">Tell us how many counters and outlets you operate and we'll help you choose the simplest PayChat setup for your business.</p>
                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ url('/contact') }}" class="pc-button pc-button-primary">Talk to PayChat</a>
                    <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-secondary">Start with PayChat Launch</a>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    (() => {
        const formatInr = (value) => `₹${Number(value || 0).toLocaleString('en-IN')}`
        const yearlyEquivalent = (value) => Math.round(Number(value || 0) / 12)
        let billingMode = 'monthly'

        const updateBilling = () => {
            document.querySelectorAll('[data-billing-toggle]').forEach((button) => {
                const pressed = button.dataset.billingToggle === billingMode
                button.setAttribute('aria-pressed', pressed ? 'true' : 'false')
                button.classList.toggle('text-ink/62', !pressed)
            })

            document.querySelectorAll('[data-plan-card]').forEach((card) => {
                const priceTarget = card.querySelector('[data-plan-price]')
                const equivalentTarget = card.querySelector('[data-plan-equivalent]')
                const monthly = Number(card.dataset.monthly || 0)
                const yearly = Number(card.dataset.yearly || 0)
                if (!priceTarget || !monthly) return

                if (billingMode === 'yearly') {
                    priceTarget.innerHTML = `${formatInr(yearly)}<span class="text-lg font-black text-ink/50">/year</span>`
                    if (equivalentTarget) equivalentTarget.textContent = `≈ ${formatInr(yearlyEquivalent(yearly))}/month when paid yearly`
                    return
                }

                priceTarget.innerHTML = `${formatInr(monthly)}<span class="text-lg font-black text-ink/50">/month</span>`
                if (equivalentTarget) equivalentTarget.textContent = card.dataset.planCard === 'operate' ? 'Save with yearly billing' : 'Most value for restaurants'
            })
        }

        document.querySelectorAll('[data-billing-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                billingMode = button.dataset.billingToggle || 'monthly'
                updateBilling()
            })
        })

        const calculator = document.querySelector('[data-pricing-calculator]')
        if (calculator) {
            const planInput = calculator.querySelector('[data-calc-plan]')
            const outletsInput = calculator.querySelector('[data-calc-outlets]')
            const countersInput = calculator.querySelector('[data-calc-counters]')
            const totalTarget = calculator.querySelector('[data-calc-total]')
            const breakdownTarget = calculator.querySelector('[data-calc-breakdown]')
            const recommendationTarget = calculator.querySelector('[data-calc-recommendation]')
            const config = {
                operateMonthly: Number(calculator.dataset.operateMonthly || 299),
                growMonthly: Number(calculator.dataset.growMonthly || 599),
                scaleMonthly: Number(calculator.dataset.scaleMonthly || 999),
                operateCounters: Number(calculator.dataset.operateCounters || 2),
                growCounters: Number(calculator.dataset.growCounters || 4),
                extraCounter: Number(calculator.dataset.extraCounter || 149),
                extraOutlet: Number(calculator.dataset.extraOutlet || 349)
            }

            const updateCalculator = () => {
                const plan = planInput.value
                const outlets = Math.max(1, Number(outletsInput.value || 1))
                const counters = Math.max(1, Number(countersInput.value || 1))
                const base = plan === 'grow' ? config.growMonthly : plan === 'scale' ? config.scaleMonthly : config.operateMonthly
                const includedCounters = plan === 'grow' ? config.growCounters : plan === 'scale' ? counters : config.operateCounters
                const extraCounters = Math.max(0, counters - includedCounters)
                const extraOutlets = Math.max(0, outlets - 1)
                const extraCounterCost = plan === 'scale' ? 0 : extraCounters * config.extraCounter
                const extraOutletCost = plan === 'scale' ? 0 : extraOutlets * config.extraOutlet
                const total = base + extraCounterCost + extraOutletCost
                const planName = plan.charAt(0).toUpperCase() + plan.slice(1)

                totalTarget.textContent = `${formatInr(total)}/month`
                breakdownTarget.textContent = `${planName} ${formatInr(base)} + ${extraCounters} extra counter${extraCounters === 1 ? '' : 's'} (${formatInr(extraCounterCost)}) + ${extraOutlets} additional outlet${extraOutlets === 1 ? '' : 's'} (${formatInr(extraOutletCost)}).`

                let recommendation = plan === 'operate'
                    ? 'Start with Operate for simple daily billing.'
                    : plan === 'grow'
                        ? 'Grow gives better value for kitchen, loyalty, advanced restaurant features or multiple counters.'
                        : 'Recommended: PayChat Scale for multi-location or custom operations.'

                if (outlets > 1 || counters > 6) {
                    recommendation = 'Recommended: PayChat Scale for multiple outlets, many counters, or central reporting.'
                } else if (plan === 'operate' && counters > config.operateCounters) {
                    recommendation = `Grow may be better value at ${formatInr(config.growMonthly)}/month if you also need kitchen, loyalty, or advanced restaurant features.`
                }

                recommendationTarget.textContent = recommendation
            }

            ;[planInput, outletsInput, countersInput, calculator.querySelector('[data-calc-business]')].forEach((input) => {
                input?.addEventListener('input', updateCalculator)
                input?.addEventListener('change', updateCalculator)
            })
            updateCalculator()
        }

        updateBilling()
    })()
</script>
@endpush
