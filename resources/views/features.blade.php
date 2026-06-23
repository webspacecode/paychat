@extends('layouts.marketing', [
    'title' => 'POS Billing Features: KOT, QR, Inventory & Reports | PayChat',
    'description' => 'Explore PayChat POS features for cafe, restaurant, salon, bakery and retail billing: KOT, QR ordering, UPI, tokens, invoices, inventory, customers and sales reports.',
    'keywords' => 'POS billing features, KOT software, QR ordering, UPI billing, inventory POS, cafe token system, salon billing, restaurant order management, PayChat features',
    'canonical' => url('/features'),
])

@section('content')
    <section class="pc-surface-hero">
        <div class="pc-container py-14 lg:py-20">
            <div class="max-w-3xl">
                <p class="pc-eyebrow">Features</p>
                <h1 class="pc-page-title mt-4 text-ink">Clean POS tools for busy counters.</h1>
                <p class="mt-6 text-lg leading-8 text-ink/64">PayChat keeps billing, orders, tokens, customer invoices and reports in one practical system for cafes, salons, restaurants and local shops.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="https://paychat.shop/pos/#/register" class="pc-button pc-button-primary">Start Free Trial</a>
                    <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-secondary">Talk to PayChat</a>
                </div>
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container">
            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['POS Billing', 'Create bills quickly, apply payments and keep every order traceable.'],
                    ['UPI & Cash Tracking', 'Record payment modes clearly for smoother end-of-day checks.'],
                    ['Token & Order Queue', 'Keep pickup and service queues visible for staff and customers.'],
                    ['Paperless Invoice', 'Share clean customer invoices digitally and reduce printing where possible.'],
                    ['Reports', 'Review daily sales, payment mix and product movement without manual sheets.'],
                    ['QR Ordering', 'Let customers scan, browse and place orders when that workflow fits your business.'],
                    ['Inventory', 'Manage products, stock and catalog updates from the same dashboard.'],
                    ['Dine-In Flow', 'Support table orders, KOT batches and final billing for restaurants.'],
                    ['Customer Records', 'Keep useful customer details for repeat visits and invoice history.'],
                ] as [$title, $body])
                    <article class="pc-soft-card p-6">
                        <div class="mb-5 h-2 w-12 rounded-full bg-primary"></div>
                        <h2 class="text-xl font-black text-ink">{{ $title }}</h2>
                        <p class="mt-3 text-sm leading-6 text-ink/62">{{ $body }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-y border-primary/10 bg-[#f4faf8] py-16 text-ink">
        <div class="pc-container grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.18em] text-primary/65">Daily workflows</p>
                <h2 class="pc-section-title mt-4">Built for real counters, not just dashboards.</h2>
                <p class="mt-5 text-lg leading-8 text-ink/64">Staff get simple screens for billing and orders. Owners get reports and control without adding separate tools for every small workflow.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach(['Setup business', 'Add products or menu', 'Start billing', 'Share invoice and manage orders'] as $step)
                    <div class="pc-soft-card p-6">
                        <p class="text-sm font-black uppercase tracking-wide text-primary/55">Step</p>
                        <h3 class="mt-3 text-xl font-black">{{ $step }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pc-section bg-paper">
        <div class="pc-container">
            <div class="pc-soft-card p-6 lg:p-10">
                <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <h2 class="pc-section-title text-ink">See the right PayChat setup for your business.</h2>
                        <p class="mt-4 max-w-2xl text-lg leading-8 text-ink/62">A short founder-led walkthrough is usually the easiest way to map billing, QR ordering, tokens and reports to your current counter.</p>
                    </div>
                    <a href="https://paychat.shop/pos/#/register" class="pc-button pc-button-primary">Start Free Trial</a>
                </div>
            </div>
        </div>
    </section>
@endsection
