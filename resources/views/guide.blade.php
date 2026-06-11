@extends('layouts.marketing', [
    'title' => 'PayChat POS Guide - Training Videos',
    'description' => 'Watch PayChat POS training videos for billing, takeaway, delivery, dine-in tables and daily operations.',
    'canonical' => url('/guide'),
])

@section('content')
    @php
        $videos = [
            ['Basic Of PayChat POS', 'Learn the basic layout, navigation and main billing workflow.', 'https://www.youtube.com/embed/sBLSAcn7Nhc', 'https://youtu.be/sBLSAcn7Nhc'],
            ['Manage Multiple Dine-In Tables', 'Manage running dine-in tables, switch between tables and continue billing.', 'https://www.youtube.com/embed/uwNvLzIB4KY', 'https://youtu.be/uwNvLzIB4KY'],
            ['How to Place Delivery Order', 'Create delivery orders, add customer details, complete payment and generate invoice.', 'https://www.youtube.com/embed/g8K2EG4IMTs', 'https://youtu.be/g8K2EG4IMTs'],
            ['Place a Dine-In Order', 'Assign table, add items, send to kitchen if needed and proceed to final billing.', 'https://www.youtube.com/embed/HysW5SoROlI', 'https://youtu.be/HysW5SoROlI'],
            ['How to Place Takeaway Order', 'Quickly place takeaway orders, collect payment, print bill and share invoice.', 'https://www.youtube.com/embed/u9NQP0Tmleg', 'https://youtu.be/u9NQP0Tmleg'],
        ];
    @endphp

    <section class="border-b border-black/10 bg-paper">
        <div class="pc-container py-14 lg:py-20">
            <div class="max-w-3xl">
                <p class="pc-eyebrow">Training library</p>
                <h1 class="pc-page-title mt-4 text-ink">PayChat POS guide.</h1>
                <p class="mt-6 text-lg leading-8 text-ink/64">Short training videos for operators and cashiers covering billing, takeaway, delivery, dine-in tables and daily operations.</p>
            </div>
        </div>
    </section>

    <section class="pc-section bg-white">
        <div class="pc-container grid gap-6 lg:grid-cols-2">
            @foreach ($videos as [$title, $description, $embed, $watch])
                <article class="overflow-hidden rounded-[1.6rem] border border-black/10 bg-white shadow-soft">
                    <div class="aspect-video bg-[#eef5ff]">
                        <iframe class="h-full w-full" src="{{ $embed }}" title="{{ $title }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                    <div class="p-6">
                        <h2 class="text-xl font-black text-ink">{{ $title }}</h2>
                        <p class="mt-3 text-sm leading-6 text-ink/62">{{ $description }}</p>
                        <a href="{{ $watch }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex text-sm font-bold text-primary hover:text-ink">Open on YouTube</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="pb-20">
        <div class="pc-container">
            <div class="rounded-[2rem] border border-primary/10 bg-white/75 p-6 text-ink shadow-lift backdrop-blur lg:p-10">
                <h2 class="pc-section-title">Need help getting started?</h2>
                <p class="mt-4 max-w-2xl text-lg leading-8 text-ink/64">Contact PayChat support or request a demo for guided setup.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-primary">Start Free Trial</a>
                    <a href="{{ url('/login') }}" class="pc-button pc-button-secondary">Open Login</a>
                </div>
            </div>
        </div>
    </section>
@endsection
