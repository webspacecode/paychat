@extends('layouts.marketing', [
    'title' => 'PayChat Pricing - Simple POS Plans',
    'description' => 'PayChat pricing for cafes, restaurants, salons and retail shops. Choose a POS plan based on counters, order volume and operational needs.',
    'canonical' => url('/pricing'),
])

@section('content')
    <section class="border-b border-black/10 bg-paper">
        <div class="pc-container py-14 lg:py-20">
            <div class="max-w-3xl">
                <p class="pc-eyebrow">Pricing</p>
                <h1 class="pc-page-title mt-4 text-ink">Simple plans based on how your counter works.</h1>
                <p class="mt-6 text-lg leading-8 text-ink/64">Start with the workflow you need today. PayChat can support single counters, QR ordering, inventory, tokens and multi-branch operations as your business grows.</p>
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container">
            <div class="grid gap-4 lg:grid-cols-3">
                @foreach([
                    ['Starter', 'For small shops starting with billing.', ['Limited orders', 'Vendor POS', 'Basic reports', 'Guided setup']],
                    ['Growth', 'For busier outlets adding ordering and stock.', ['Higher order limits', 'QR self ordering', 'Inventory management', 'Sales reports']],
                    ['Pro', 'For multi-counter and scaling teams.', ['Unlimited order workflows', 'Multi-branch support', 'Token system', 'Custom feature guidance']],
                ] as [$plan, $summary, $features])
                    <article class="rounded-xl border {{ $plan === 'Growth' ? 'border-ink bg-ink text-white shadow-lift' : 'border-black/10 bg-white/88 text-ink shadow-soft' }} p-6 backdrop-blur lg:p-8">
                        @if($plan === 'Growth')
                            <p class="mb-4 inline-flex rounded-full border border-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white/65">Often a good fit</p>
                        @endif
                        <h2 class="text-2xl font-black">{{ $plan }}</h2>
                        <p class="mt-3 text-sm leading-6 {{ $plan === 'Growth' ? 'text-white/64' : 'text-ink/62' }}">{{ $summary }}</p>
                        <ul class="mt-6 space-y-3 text-sm font-semibold {{ $plan === 'Growth' ? 'text-white/78' : 'text-ink/68' }}">
                            @foreach($features as $feature)
                                <li class="flex gap-3"><span class="{{ $plan === 'Growth' ? 'text-white' : 'text-primary' }}">✓</span>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ url('/contact') }}" class="pc-button mt-8 w-full {{ $plan === 'Growth' ? 'bg-white text-ink hover:bg-paper' : 'bg-ink text-white hover:bg-black' }}">Contact Sales</a>
                    </article>
                @endforeach
            </div>
            <p class="mt-8 text-center text-sm leading-6 text-ink/55">Pricing is confirmed after understanding counters, order volume and required modules. No fake calculator, just a clear conversation.</p>
        </div>
    </section>
@endsection
