@extends('layouts.marketing', [
    'title' => 'About PayChat - Simple POS for Indian Businesses',
    'description' => 'PayChat is built to simplify billing, orders, invoices, tokens and reports for Indian cafes, salons, restaurants and local shops.',
    'canonical' => url('/about'),
])

@section('content')
    <section class="pc-surface-hero">
        <div class="pc-container py-14 lg:py-20">
            <div class="max-w-3xl">
                <p class="pc-eyebrow">About PayChat</p>
                <h1 class="pc-page-title mt-4 text-ink">A young POS product with serious taste.</h1>
                <p class="mt-6 text-lg leading-8 text-ink/64">PayChat is built to simplify billing, orders, invoices and reports for teams that need software to feel fast, calm and dependable at the counter.</p>
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <h2 class="pc-section-title text-ink">Founder-led, early, and intentionally practical.</h2>
            </div>
            <div class="space-y-6 text-lg leading-8 text-ink/64">
                <p>PayChat focuses on small and medium businesses that need useful software without enterprise complexity.</p>
                <p>The product is shaped around real workflows: fast checkout, order queues, QR ordering, paperless invoices, stock visibility and simple reports.</p>
                <p>We work closely with early customers so the product stays practical, reliable and easy for teams to adopt.</p>
            </div>
        </div>
    </section>

    <section class="border-y border-black/10 bg-[#f4faf8] py-16">
        <div class="pc-container grid gap-3 md:grid-cols-3">
            @foreach([
                ['Simple to start', 'Guided setup for your business type and counter flow.'],
                ['Built for India', 'UPI, GST-ready billing needs and local operating habits in mind.'],
                ['Serious about billing', 'Designed for real sales, staff workflows and owner visibility.'],
            ] as [$title, $body])
                <article class="pc-soft-card p-6">
                    <div class="mb-5 h-2 w-12 rounded-full bg-primary"></div>
                    <h2 class="text-xl font-black text-ink">{{ $title }}</h2>
                    <p class="mt-3 text-sm leading-6 text-ink/62">{{ $body }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
