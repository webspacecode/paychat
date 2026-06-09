@extends('layouts.marketing', [
    'title' => 'PayChat - Fast Billing for Cafes, Salons and Shops',
    'description' => 'PayChat is a simple POS for everyday business with billing, orders, tokens, paperless invoices, UPI and cash tracking, reports and QR ordering.',
    'canonical' => url('/'),
])

@section('content')
    <section class="relative overflow-hidden border-b border-black/10 bg-transparent">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-80 bg-[linear-gradient(180deg,rgba(255,255,255,0.82),rgba(255,255,255,0)),radial-gradient(circle_at_24%_8%,rgba(52,87,213,0.13),transparent_28rem),radial-gradient(circle_at_88%_12%,rgba(184,145,79,0.10),transparent_25rem)]"></div>
        <div class="pc-container relative grid gap-10 py-11 sm:py-14 md:gap-12 lg:grid-cols-[0.92fr_1.08fr] lg:items-center lg:py-16 xl:py-20">
            <div class="max-w-2xl">
                <p class="pc-eyebrow">
                    <span class="h-2 w-2 rounded-full bg-primary"></span>
                    POS for busy Indian counters
                </p>

                <h1 class="pc-title mt-6 max-w-3xl text-ink">
                    Fast billing for cafes, salons and local shops.
                </h1>

                <p class="mt-5 max-w-xl text-base leading-7 text-ink/64 sm:text-lg sm:leading-8">
                    Billing, orders, tokens and customer invoices in one clean system. Built for real counters, not just dashboards.
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-primary">
                        Book Free Demo
                    </a>
                    <a href="https://wa.me/919834969229?text=Hi%20PayChat,%20I%20want%20to%20know%20more" target="_blank" rel="noopener noreferrer" class="pc-button pc-button-secondary">
                        Chat on WhatsApp
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach([
                        ['Fast billing', 'Fewer clicks at the counter'],
                        ['Paperless invoices', 'Share bills digitally'],
                        ['Token queue', 'Keep pickup flow clear'],
                        ['Cafe / Salon / Retail ready', 'Works for everyday shops'],
                    ] as [$title, $label])
                        <div class="flex min-h-[4.5rem] items-start gap-3 rounded-lg border border-black/10 bg-white/68 p-4 shadow-[0_10px_30px_rgba(9,13,24,0.04)] backdrop-blur">
                            <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-primary/15 bg-primary/[0.08]">
                                <span class="h-2 w-2 rounded-full bg-primary"></span>
                            </span>
                            <div>
                                <p class="text-sm font-extrabold leading-5 text-ink">{{ $title }}</p>
                                <p class="mt-1 text-sm leading-5 text-ink/54">{{ $label }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-3 rounded-3xl bg-primary/[0.08] blur-3xl"></div>
                <div class="relative overflow-hidden rounded-2xl border border-black/10 bg-white/82 p-2 shadow-[0_26px_90px_rgba(9,13,24,0.12)] backdrop-blur">
                    <div class="flex items-center justify-between border-b border-black/10 px-3 py-2.5">
                        <div class="flex gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-ink/16"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-ink/16"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-ink/16"></span>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-wide text-ink/36">Counter preview</p>
                    </div>
                    <div class="grid gap-3 p-2 lg:grid-cols-[minmax(0,1fr)_13.5rem]">
                        <div class="overflow-hidden rounded-xl border border-black/10 bg-[#f7f7f2]">
                            <img src="{{ asset('YOUR_POS_SCREENSHOT.png') }}" alt="PayChat POS interface preview" class="aspect-[4/3] w-full object-cover object-left-top" loading="eager">
                        </div>
                        <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-1">
                            @foreach([
                                ['Payments', 'UPI and cash tracked'],
                                ['Queue', 'Tokens stay visible'],
                                ['Invoice', 'Digital sharing ready'],
                            ] as [$label, $value])
                                <div class="rounded-lg border border-black/10 bg-white p-4">
                                    <p class="text-[0.68rem] font-extrabold uppercase tracking-wide text-ink/38">{{ $label }}</p>
                                    <p class="mt-2 text-sm font-bold leading-5 text-ink/82">{{ $value }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-black/10 bg-white/72 p-4 backdrop-blur">
                        <p class="text-[0.68rem] font-extrabold uppercase tracking-wide text-ink/42">Best for</p>
                        <p class="mt-1 text-sm font-bold leading-5 text-ink/82">cafes, restaurants, retail and salons</p>
                    </div>
                    <div class="rounded-lg border border-black/10 bg-white/72 p-4 backdrop-blur">
                        <p class="text-[0.68rem] font-extrabold uppercase tracking-wide text-ink/42">Setup</p>
                        <p class="mt-1 text-sm font-bold leading-5 text-ink/82">guided onboarding included</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-black/10 bg-white/72 py-10">
        <div class="pc-container grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['Fast billing', 'Create bills with fewer clicks and clear payment tracking.'],
                ['Paperless invoice', 'Share digital invoices and print only when needed.'],
                ['Token/KDS', 'Keep kitchen and pickup queues easier to manage.'],
                ['For local teams', 'Useful for cafes, salons, restaurants and retail shops.'],
            ] as [$title, $body])
                <article class="rounded-lg border border-black/10 bg-white p-5 shadow-[0_10px_30px_rgba(9,13,24,0.035)]">
                    <h2 class="text-base font-extrabold text-ink">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-6 text-ink/62">{{ $body }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="pc-section bg-paper">
        <div class="pc-container">
            <div class="mx-auto max-w-3xl text-center">
                <p class="pc-eyebrow">Business types</p>
                <h2 class="pc-section-title mt-4 text-ink">Simple POS for everyday service.</h2>
                <p class="mt-5 text-lg leading-8 text-ink/64">Whether you sell coffee, meals, products or appointments, PayChat keeps billing and orders clear for your staff.</p>
            </div>

            <div class="mt-12 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['Cafes', 'Quick billing, tokens, QR ordering and customer-friendly invoices.'],
                    ['Restaurants', 'Table service, KOT, order queues and final billing.'],
                    ['Salons', 'Simple billing, customer records and repeat-visit friendly operations.'],
                    ['Retail shops', 'Product catalog, payments, customers and sales reports.'],
                ] as [$title, $body])
                    <article class="pc-card rounded-lg p-6 transition hover:-translate-y-1">
                        <h3 class="text-2xl font-black text-ink">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-ink/62">{{ $body }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="features" class="border-y border-black/10 bg-white py-20">
        <div class="pc-container grid gap-10 lg:grid-cols-[0.85fr_1.15fr]">
            <div>
                <p class="pc-eyebrow">Core features</p>
                <h2 class="pc-section-title mt-4 text-ink">One clean system for billing, orders and reports.</h2>
                <p class="mt-5 text-lg leading-8 text-ink/64">PayChat connects your front counter, payment collection, queue flow and owner reporting without visual clutter.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach([
                    ['POS billing', 'Fast checkout for dine-in, takeaway and retail sales.'],
                    ['UPI/cash tracking', 'Record payment modes clearly for daily closing.'],
                    ['Token/order queue', 'Keep service and pickup status visible.'],
                    ['Paperless invoice', 'Share invoices digitally with customers.'],
                    ['Reports', 'Review sales and product movement.'],
                    ['Inventory support', 'Manage catalog and stock workflows where enabled.'],
                ] as [$title, $body])
                    <article class="pc-card rounded-lg p-6">
                        <div class="mb-5 h-1.5 w-10 rounded-full bg-primary/80"></div>
                        <h3 class="text-xl font-black text-ink">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-ink/62">{{ $body }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-b border-black/10 bg-ink py-20 text-white">
        <div class="pc-container grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.18em] text-white/50">How it works</p>
                <h2 class="pc-section-title mt-4">From setup to first bill.</h2>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-white/64">We help map the software to your real counter flow, then your team can start billing with a clearer daily process.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach([
                    ['01', 'Setup business', 'Add outlet details, staff access and billing basics.'],
                    ['02', 'Add products/menu', 'Create your catalog or menu for faster checkout.'],
                    ['03', 'Start billing', 'Take orders, collect payments and close bills.'],
                    ['04', 'Share invoice', 'Send invoices and manage order or token status.'],
                ] as [$number, $title, $body])
                    <article class="rounded-lg border border-white/15 bg-white/[0.07] p-6 shadow-soft">
                        <p class="text-4xl font-black text-white/35">{{ $number }}</p>
                        <h3 class="mt-5 text-xl font-black">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-white/62">{{ $body }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-b border-black/10 bg-paper py-20">
        <div class="pc-container">
            <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                <div>
                    <p class="pc-eyebrow">Live network</p>
                    <h2 class="pc-section-title mt-4 text-ink">Live Partner Businesses</h2>
                    <p class="mt-4 text-lg leading-8 text-ink/64">Discover shops using PayChat in the early partner network.</p>
                </div>
                <div class="lg:justify-self-end">
                    <label for="partnerSearch" class="sr-only">Search partner shops</label>
                    <input id="partnerSearch" placeholder="Search shops..." class="w-full rounded-md border border-black/10 bg-white px-4 py-4 text-sm font-medium outline-none transition placeholder:text-ink/35 focus:border-ink lg:w-96">
                </div>
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @forelse($tenants as $tenant)
                    @php
                        $branding = $tenant->branding;
                        $name = $tenant->name ?? 'Unnamed Business';
                        $industry = $tenant->industry ?? 'Business';
                        $address = $branding->address ?? 'Partner Store';
                        $logo = $branding && $branding->logo ? $branding->logo : null;
                        $shopUrl = url('/store/' . $tenant->slug);
                        $avg = round($tenant->reviews_avg_rating ?? 0);
                    @endphp

                    <article class="partner-card overflow-hidden rounded-lg border border-black/10 bg-white shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift">
                        <div class="border-b border-black/10 bg-ink p-5 text-white">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-md bg-white p-1.5 text-xl font-black text-ink shadow-soft">
                                    @if($logo)
                                        <img src="{{ $logo }}" alt="{{ $name }} logo" class="h-full w-full rounded object-cover" loading="lazy">
                                    @else
                                        {{ strtoupper(substr($name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold">
                                    <span class="h-2 w-2 rounded-full bg-white"></span>
                                    Live
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <h3 class="text-xl font-black leading-tight text-ink">{{ $name }}</h3>
                            <p class="mt-1 text-sm font-bold text-primary">{{ $industry }}</p>
                            <p class="mt-4 min-h-[44px] text-sm leading-6 text-ink/58">{{ $address }}</p>

                            <div class="mt-5 flex items-center justify-between border-y border-black/10 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="text-sm tracking-tight text-amber-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $avg)
                                                ★
                                            @else
                                                <span class="text-black/15">★</span>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-sm font-black text-ink">{{ $tenant->reviews_avg_rating ?: 'New' }}</span>
                                </div>
                                <div class="text-sm font-medium text-ink/42">{{ $tenant->reviews_count ?? 0 }} reviews</div>
                            </div>

                            <a href="{{ $shopUrl }}" target="_blank" rel="noopener noreferrer" class="mt-6 flex items-center justify-between rounded-md bg-ink px-5 py-4 text-white transition hover:bg-black">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-white/45">Powered by PayChat</p>
                                    <p class="mt-1 font-bold">Visit Store</p>
                                </div>
                                <span class="text-xl font-black">→</span>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-lg border border-black/10 bg-white p-10 text-center">
                        <h3 class="text-xl font-black text-ink">No partner businesses yet</h3>
                        <p class="mt-2 text-ink/55">PayChat is onboarding early merchants. Stay tuned.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="pc-container">
            <div class="overflow-hidden rounded-xl border border-black/10 bg-ink p-6 text-white shadow-lift lg:p-10">
                <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-white/45">Founder-led setup</p>
                        <h2 class="pc-section-title mt-3 max-w-3xl">Bring PayChat to your counter this week.</h2>
                        <p class="mt-4 max-w-2xl text-lg leading-8 text-white/64">Get a clear walkthrough for billing, QR orders, kitchen flow, invoices and reporting.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                        <a href="{{ url('/start-free-trial') }}" class="pc-button bg-white text-ink hover:bg-paper">Book Free Demo</a>
                        <a href="{{ url('/contact') }}" class="pc-button border border-white/15 text-white hover:bg-white/10">Contact PayChat</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('head')
    @verbatim
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "@id": "https://paychat.shop/#organization",
                "name": "PayChat",
                "url": "https://paychat.shop",
                "logo": "https://paychat.shop/color-paychat-logo-main.svg"
            },
            {
                "@type": "SoftwareApplication",
                "name": "PayChat POS",
                "applicationCategory": "BusinessApplication",
                "operatingSystem": "Web",
                "description": "POS, billing, QR ordering, token management, invoices and reporting software for Indian businesses.",
                "url": "https://paychat.shop",
                "publisher": {
                    "@id": "https://paychat.shop/#organization"
                }
            }
        ]
    }
    </script>
    @endverbatim
@endpush

@push('scripts')
    <script>
        const partnerSearch = document.getElementById('partnerSearch');
        if (partnerSearch) {
            partnerSearch.addEventListener('input', function () {
                const query = this.value.toLowerCase();
                document.querySelectorAll('.partner-card').forEach((card) => {
                    card.style.display = card.textContent.toLowerCase().includes(query) ? '' : 'none';
                });
            });
        }
    </script>
@endpush
