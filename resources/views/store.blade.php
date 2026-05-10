<!DOCTYPE html>
<html lang="en">
@php

    $branding = $tenant->branding;

    $logo = $branding?->logo
        ?: 'https://dummyimage.com/300x300/e5e7eb/111827&text=' . urlencode($tenant->name);

    $cover = $branding?->cover_image
        ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1600&auto=format&fit=crop';

    $address = $branding?->address ?: 'Partner Store';

    $shopUrl = url('/pos#/self-pos/' . $tenant->api_key);

@endphp
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta
    name="description"
    content="Order online from {{ $tenant->name }} using PayChat POS."
>

<meta
    name="keywords"
    content="{{ $tenant->name }}, online ordering, cafe, restaurant, paychat pos"
>

<meta
    name="robots"
    content="index, follow"
>

<link
    rel="canonical"
    href="{{ request()->url() }}"
>

<!-- Open Graph -->
<meta
    property="og:title"
    content="{{ $tenant->name }} | Order Online"
>

<meta
    property="og:description"
    content="Order online from {{ $tenant->name }} using PayChat POS."
>



<meta
    property="og:url"
    content="{{ request()->url() }}"
>

<meta
    property="og:type"
    content="website"
>

<!-- Twitter -->
<meta
    name="twitter:card"
    content="summary_large_image"
>

<meta
    name="twitter:title"
    content="{{ $tenant->name }}"
>

<meta
    name="twitter:description"
    content="Order online from {{ $tenant->name }}"
>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Restaurant',
    'name' => $tenant->name,
    'image' => null,
    'url' => request()->url(),
    'logo' => $logo,
    'description' => 'Order online from ' . $tenant->name . ' using PayChat POS.',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $address,
    ],
    'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => $avgRating ?: 5,
        'reviewCount' => $totalReviews,
    ],
]) !!}
</script>

    <title>
        {{ $tenant->name }} | Order Online
    </title>

    <meta
        name="description"
        content="Order online from {{ $tenant->name }} using PayChat POS."
    >

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        body {
            font-family: 'Inter', sans-serif;
        }

    </style>

</head>

<body class="bg-[#f6f8fc] text-slate-800">

<!-- HERO -->
<section class="relative overflow-hidden">

    <!-- COVER -->
    <div class="h-[320px] md:h-[420px] relative">

        <img
            src="{{ $cover }}"
            alt="{{ $tenant->name }}"
            class="absolute inset-0 w-full h-full object-cover"
        >

        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/20"></div>

    </div>

    <!-- CONTENT -->
    <div class="max-w-7xl mx-auto px-6 relative">

        <div class="-mt-24 relative z-10">

            <div class="bg-white rounded-[32px] shadow-[0_20px_80px_rgba(15,23,42,0.10)] overflow-hidden">

                <div class="p-8 md:p-12">

                    <div class="flex flex-col lg:flex-row gap-10 justify-between lg:items-center">

                        <!-- LEFT -->
                        <div class="flex gap-6">

                            <!-- LOGO -->
                            <div class="flex-shrink-0">

                                <div class="w-28 h-28 rounded-[28px] bg-white shadow-xl border border-gray-100 p-3">

                                    <img
                                        src="{{ $logo }}"
                                        alt="{{ $tenant->name }}"
                                        class="w-full h-full object-cover rounded-[22px]"
                                    >

                                </div>

                            </div>

                            <!-- INFO -->
                            <div>

                                <!-- LIVE -->
                                <div class="inline-flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-full text-sm font-semibold mb-5">

                                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>

                                    Open for ordering

                                </div>

                                <!-- NAME -->
                                <h1 class="text-4xl md:text-5xl font-bold leading-tight text-slate-900">
                                    {{ $tenant->name }}
                                </h1>

                                <!-- INDUSTRY -->
                                <p class="text-lg text-indigo-600 font-medium mt-3">
                                    {{ $tenant->industry ?? 'Restaurant & Cafe' }}
                                </p>

                                <!-- ADDRESS -->
                                <p class="text-gray-500 leading-relaxed mt-5 max-w-2xl">
                                    {{ $address }}
                                </p>

                                <!-- RATING -->
                                <div class="flex flex-wrap items-center gap-4 mt-6">

                                    <div class="flex items-center gap-2">

                                        <div class="flex text-amber-400 text-lg">
                                            ★ ★ ★ ★ ★
                                        </div>

                                        <span class="font-bold text-slate-800">
                                            {{ $avgRating ?: 'New' }}
                                        </span>

                                    </div>

                                    <div class="text-gray-500">
                                        {{ $totalReviews }} reviews
                                    </div>

                                </div>

                                <!-- TAGS -->
                                <div class="flex flex-wrap gap-3 mt-6">

                                    <span class="bg-gray-100 text-gray-700 text-sm px-4 py-2 rounded-full">
                                        QR Ordering
                                    </span>

                                    <span class="bg-gray-100 text-gray-700 text-sm px-4 py-2 rounded-full">
                                        Live Menu
                                    </span>

                                    <span class="bg-gray-100 text-gray-700 text-sm px-4 py-2 rounded-full">
                                        Fast Checkout
                                    </span>

                                </div>

                            </div>

                        </div>

                        <!-- RIGHT CTA -->
                        <div class="lg:w-[340px]">

                            <div class="bg-gradient-to-br from-indigo-500 via-violet-500 to-purple-600 rounded-[32px] p-8 text-white shadow-2xl">

                                <p class="text-indigo-100 text-sm font-medium">
                                    Online ordering powered by PayChat
                                </p>

                                <h3 class="text-3xl font-bold leading-tight mt-3">
                                    Fresh food.
                                    Fast ordering.
                                </h3>

                                <p class="text-indigo-100 mt-4 leading-relaxed">
                                    Browse menu, place orders and enjoy seamless checkout directly from your phone.
                                </p>

                                <a
                                    href="{{ $shopUrl }}"
                                    class="mt-8 flex items-center justify-center gap-3 bg-white text-indigo-700 hover:bg-indigo-50 py-4 rounded-2xl font-bold text-lg transition-all duration-300"
                                >

                                    Start Ordering →

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- FEATURES -->
<section class="max-w-7xl mx-auto px-6 py-20">

    <div class="grid lg:grid-cols-3 gap-8">

        <div class="bg-white rounded-[28px] p-8 border border-gray-100 shadow-sm">

            <div class="text-4xl mb-5">
                🍽️
            </div>

            <h3 class="text-2xl font-bold">
                Order Online
            </h3>

            <p class="text-gray-500 leading-relaxed mt-4">
                Browse products and place orders directly from your mobile device.
            </p>

        </div>

        <div class="bg-white rounded-[28px] p-8 border border-gray-100 shadow-sm">

            <div class="text-4xl mb-5">
                ⚡
            </div>

            <h3 class="text-2xl font-bold">
                Fast Checkout
            </h3>

            <p class="text-gray-500 leading-relaxed mt-4">
                Quick billing and seamless payment experience powered by PayChat POS.
            </p>

        </div>

        <div class="bg-white rounded-[28px] p-8 border border-gray-100 shadow-sm">

            <div class="text-4xl mb-5">
                📱
            </div>

            <h3 class="text-2xl font-bold">
                Mobile Friendly
            </h3>

            <p class="text-gray-500 leading-relaxed mt-4">
                Optimized beautifully for iPhone, Android, tablets and desktop devices.
            </p>

        </div>

    </div>

</section>

<!-- REVIEWS -->
<section class="max-w-7xl mx-auto px-6 pb-24">

    <div class="flex items-center justify-between mb-10">

        <div>

            <h2 class="text-4xl font-bold text-slate-900">
                Customer Reviews
            </h2>

            <p class="text-gray-500 mt-3">
                Real reviews from verified PayChat orders.
            </p>

        </div>

        <div class="hidden md:flex items-center gap-3 bg-white px-6 py-4 rounded-2xl border border-gray-100 shadow-sm">

            <div class="text-3xl font-bold text-slate-900">
                {{ $avgRating ?: '0.0' }}
            </div>

            <div>

                <div class="text-amber-400">
                    ★★★★★
                </div>

                <p class="text-sm text-gray-500">
                    {{ $totalReviews }} reviews
                </p>

            </div>

        </div>

    </div>

    @if($reviews->count())

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">

            @foreach($reviews as $review)

                <div class="bg-white rounded-[28px] border border-gray-100 p-6 shadow-sm hover:shadow-xl transition-all duration-300">

                    <!-- TOP -->
                    <div class="flex items-center justify-between">

                        <div class="flex items-center gap-3">

                            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg">

                                {{ strtoupper(substr($tenant->name, 0, 1)) }}

                            </div>

                            <div>

                                <h4 class="font-bold text-slate-900">
                                    Verified Customer
                                </h4>

                                <p class="text-sm text-gray-500">
                                    {{ $review->created_at->diffForHumans() }}
                                </p>

                            </div>

                        </div>

                        <div class="bg-green-50 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                            Verified
                        </div>

                    </div>

                    <!-- RATING -->
                    <div class="flex items-center gap-1 text-amber-400 text-lg mt-5">

                        @for($i = 1; $i <= 5; $i++)

                            @if($i <= $review->rating)

                                ★

                            @else

                                <span class="text-gray-200">★</span>

                            @endif

                        @endfor

                    </div>

                    <!-- REVIEW -->
                    <p class="text-gray-600 leading-relaxed mt-5">

                        {{ $review->review_text ?: 'Great experience with fast ordering and smooth checkout.' }}

                    </p>

                </div>

            @endforeach

        </div>

        <!-- PAGINATION -->
        <div class="mt-12">

            {{ $reviews->links() }}

        </div>

    @else

        <div class="bg-white border border-dashed border-gray-200 rounded-[32px] p-16 text-center">

            <div class="text-6xl mb-5">
                ⭐
            </div>

            <h3 class="text-3xl font-bold text-slate-900">
                No Reviews Yet
            </h3>

            <p class="text-gray-500 mt-4 max-w-xl mx-auto">
                Be the first customer to place an order and leave a review for {{ $tenant->name }}.
            </p>

        </div>

    @endif

</section>

<!-- FOOTER -->
<footer class="border-t border-gray-200 py-10 bg-white">

    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">

        <p class="text-gray-500 text-sm">
            Powered by PayChat POS
        </p>

        <a
            href="https://paychat.shop"
            class="text-indigo-600 font-semibold"
        >
            paychat.shop
        </a>

    </div>

</footer>

<!-- MOBILE CTA -->
<div class="fixed bottom-4 left-4 right-4 md:hidden z-50">

    <a
        href="{{ $shopUrl }}"
        class="flex items-center justify-center gap-3 bg-indigo-600 text-white py-4 rounded-2xl shadow-2xl font-bold text-lg"
    >

        Start Ordering →

    </a>

</div>

</body>
</html>