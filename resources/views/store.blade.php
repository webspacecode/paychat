<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $tenant->name }} | Order Online
    </title>

    <meta
        name="description"
        content="Order online from {{ $tenant->name }} using PayChat POS."
    >

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>

        body {
            font-family: 'Inter', sans-serif;
        }

    </style>

</head>

<body class="bg-[#f7f8fc] text-slate-800">

@php

    $branding = $tenant->branding;

    $logo = $branding?->logo
        ?: 'https://dummyimage.com/200x200/ddd/000&text=' . urlencode($tenant->name);

    $cover = $branding?->cover_image
        ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1400&auto=format&fit=crop';

    $address = $branding?->address ?: 'Partner Store';

    $shopUrl = url('/pos#/self-pos/' . $tenant->api_key);

@endphp

<!-- HERO -->
<section class="relative overflow-hidden">

    <!-- COVER -->
    <div class="h-[320px] md:h-[420px] relative">

        <img
            src="{{ $cover }}"
            class="absolute inset-0 w-full h-full object-cover"
        >

        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/20"></div>

    </div>

    <!-- CONTENT -->
    <div class="max-w-6xl mx-auto px-6 relative">

        <div class="-mt-24 relative z-10">

            <div class="bg-white rounded-[32px] shadow-[0_20px_80px_rgba(15,23,42,0.10)] overflow-hidden">

                <div class="p-8 md:p-12">

                    <div class="flex flex-col lg:flex-row gap-10 lg:items-center justify-between">

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

                                <div class="inline-flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-full text-sm font-semibold mb-5">

                                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>

                                    Open for online ordering

                                </div>

                                <h1 class="text-4xl md:text-5xl font-bold leading-tight text-slate-900">
                                    {{ $tenant->name }}
                                </h1>

                                <p class="text-lg text-indigo-600 font-medium mt-3">
                                    {{ $tenant->industry ?? 'Restaurant & Cafe' }}
                                </p>

                                <p class="text-gray-500 leading-relaxed mt-5 max-w-2xl">
                                    {{ $address }}
                                </p>

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

                        <!-- RIGHT -->
                        <div class="lg:w-[320px]">

                            <div class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-[28px] p-8 text-white">

                                <p class="text-indigo-100 text-sm font-medium">
                                    Start ordering online
                                </p>

                                <h3 class="text-3xl font-bold leading-tight mt-3">
                                    Fresh food,
                                    delivered fast.
                                </h3>

                                <p class="text-indigo-100 mt-4 leading-relaxed">
                                    Browse the menu, place your order,
                                    and enjoy seamless checkout.
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

<!-- ABOUT -->
<section class="max-w-6xl mx-auto px-6 py-20">

    <div class="grid lg:grid-cols-3 gap-8">

        <div class="bg-white rounded-[28px] p-8 shadow-sm border border-gray-100">

            <div class="text-4xl mb-5">
                🍽️
            </div>

            <h3 class="text-2xl font-bold">
                Order Online
            </h3>

            <p class="text-gray-500 leading-relaxed mt-4">
                Browse products and place orders directly from your phone.
            </p>

        </div>

        <div class="bg-white rounded-[28px] p-8 shadow-sm border border-gray-100">

            <div class="text-4xl mb-5">
                ⚡
            </div>

            <h3 class="text-2xl font-bold">
                Fast Checkout
            </h3>

            <p class="text-gray-500 leading-relaxed mt-4">
                Smooth billing and quick payment experience powered by PayChat.
            </p>

        </div>

        <div class="bg-white rounded-[28px] p-8 shadow-sm border border-gray-100">

            <div class="text-4xl mb-5">
                📱
            </div>

            <h3 class="text-2xl font-bold">
                Mobile Friendly
            </h3>

            <p class="text-gray-500 leading-relaxed mt-4">
                Works beautifully across iPhone, Android, tablets and laptops.
            </p>

        </div>

    </div>

</section>

<!-- FOOTER -->
<footer class="border-t border-gray-200 py-10">

    <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">

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

</body>
</html>