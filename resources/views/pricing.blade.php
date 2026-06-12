@extends('layouts.marketing', [
    'title' => 'PayChat Pricing - Simple POS Plans',
    'description' => 'PayChat pricing for cafes, restaurants, salons and retail shops. Choose a POS plan based on counters, order volume and operational needs.',
    'canonical' => url('/pricing'),
])

@section('content')
    <section class="pc-surface-hero">
        <div class="pc-container py-14 lg:py-20">
            <div class="max-w-3xl">
                <p class="pc-eyebrow">Pricing</p>
                <h1 class="pc-page-title mt-4 text-ink">Simple pricing, matched to your counter.</h1>
                <p class="mt-6 text-lg leading-8 text-ink/64">PayChat is still founder-led, so we confirm the right plan after understanding your outlet, counters and workflows. Clear pricing follows a real fit check.</p>
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container">
            <div class="grid gap-4 lg:grid-cols-3">
                @foreach([
                    ['Starter', 'For a single outlet that needs clean billing first.', ['Counter POS setup', 'Payment tracking', 'Basic reports', 'Guided onboarding']],
                    ['Growth', 'For busier outlets adding ordering and stock control.', ['QR or table ordering', 'Inventory workflow', 'Invoices and tokens', 'Sales reports']],
                    ['Pro', 'For teams with multiple counters or more complex service flow.', ['Multi-counter workflow', 'KOT and table flow', 'Customer records', 'Custom setup guidance']],
                ] as [$plan, $summary, $features])
                    <article class="pc-soft-card {{ $plan === 'Growth' ? 'border-primary bg-[#f4faf8] text-ink shadow-lift' : 'border-primary/10 bg-white/90 text-ink shadow-soft' }} p-6 backdrop-blur lg:p-8">
                        @if($plan === 'Growth')
                            <p class="mb-4 inline-flex rounded-full border border-primary/15 bg-white/70 px-3 py-1 text-xs font-bold uppercase tracking-wide text-primary/70">Often a good fit</p>
                        @endif
                        <h2 class="text-2xl font-black">{{ $plan }}</h2>
                        <p class="mt-3 text-sm leading-6 text-ink/62">{{ $summary }}</p>
                        <ul class="mt-6 space-y-3 text-sm font-semibold text-ink/68">
                            @foreach($features as $feature)
                                <li class="flex gap-3"><span class="text-primary">✓</span>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ url('/contact') }}" class="pc-button mt-8 w-full pc-button-primary">Contact Sales</a>
                    </article>
                @endforeach
            </div>
            <p class="mt-8 text-center text-sm leading-6 text-ink/55">Pricing is shared after a short fit check so the plan reflects your real counter, menu, service flow and modules.</p>
        </div>
    </section>
@endsection
