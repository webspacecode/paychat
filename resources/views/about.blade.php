@extends('layouts.marketing', [
    'title' => 'About PayChat - Simple POS for Indian Businesses',
    'description' => 'PayChat is built to simplify billing, orders, invoices, tokens and reports for Indian cafes, salons, restaurants and local shops.',
    'canonical' => url('/about'),
])

@section('content')
    <section class="border-b border-black/10 bg-paper">
        <div class="pc-container py-14 lg:py-20">
            <div class="max-w-3xl">
                <p class="pc-eyebrow">About PayChat</p>
                <h1 class="pc-page-title mt-4 text-ink">A practical POS built for everyday business.</h1>
                <p class="mt-6 text-lg leading-8 text-ink/64">PayChat is built to simplify how businesses operate. From billing and orders to customer invoices and reporting, the goal is to keep daily work clear for staff and owners.</p>
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <h2 class="pc-section-title text-ink">Founder-led, close to the counter.</h2>
            </div>
            <div class="space-y-6 text-lg leading-8 text-ink/64">
                <p>PayChat focuses on small and medium businesses that need useful software without expensive complexity.</p>
                <p>The product is shaped around real workflows: fast checkout, order queues, QR ordering, paperless invoices, stock visibility and simple reports.</p>
                <p>We are working with early customers to keep the system practical, reliable and easy for teams to adopt.</p>
            </div>
        </div>
    </section>

    <section class="border-y border-black/10 bg-paper py-16">
        <div class="pc-container grid gap-3 md:grid-cols-3">
            @foreach([
                ['Simple to start', 'Guided setup for your business type and counter flow.'],
                ['Built for India', 'UPI, GST-ready billing needs and local operating habits in mind.'],
                ['Serious about billing', 'Designed for real sales, staff workflows and owner visibility.'],
            ] as [$title, $body])
                <article class="pc-card rounded-lg p-6">
                    <h2 class="text-xl font-black text-ink">{{ $title }}</h2>
                    <p class="mt-3 text-sm leading-6 text-ink/62">{{ $body }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
