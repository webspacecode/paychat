<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PayChat POS Guide - watch short training videos for billing, takeaway, delivery, dine-in tables, and daily operations.">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="PayChat POS Guide">
    <meta property="og:description" content="PayChat POS training videos for operators and cashiers.">
    <meta property="og:image" content="https://paychat.shop/og-banner.jpg">
    <meta property="og:url" content="https://paychat.shop/guide">
    <title>PayChat POS Guide</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#6366f1',
                        'primary-dark': '#4f46e5',
                        'secondary': '#f59e0b',
                        'accent': '#10b981',
                        'navy': '#1e293b',
                        'cream': '#fef7ed'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }

        .gradient-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .shadow-custom {
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.1);
        }

        .shadow-custom-lg {
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.15);
        }
    </style>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
</head>

<body class="font-sans bg-gray-50 text-navy">
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-20">
                <a href="{{ url('/') }}" class="flex items-center space-x-3">
                    <img src="{{ asset('color-paychat-logo-main.svg') }}" alt="PayChat Logo" class="h-10 lg:h-12 w-auto">
                </a>

                <div class="hidden lg:flex items-center space-x-8">
                    <a href="{{ url('/features') }}" class="text-navy hover:text-primary font-medium">Features</a>
                    <a href="{{ url('/pricing') }}" class="text-navy hover:text-primary font-medium">Pricing</a>
                    <a href="{{ url('/guide') }}" class="text-primary font-semibold">Guide</a>
                    <a href="{{ url('/about') }}" class="text-navy hover:text-primary font-medium">About</a>
                    <a href="{{ url('/contact') }}" class="text-navy hover:text-primary font-medium">Contact</a>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="{{ url('/contact') }}" class="hidden sm:inline-flex bg-primary hover:bg-primary-dark text-white px-5 py-3 rounded-xl font-semibold transition-all duration-300">
                        Request Demo
                    </a>

                    <button onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-md text-navy hover:bg-gray-100" aria-label="Open navigation menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="mobileMenu" class="lg:hidden hidden border-t border-gray-200 py-4">
                <div class="flex flex-col space-y-3">
                    <a href="{{ url('/features') }}" class="text-navy hover:text-primary font-medium py-2">Features</a>
                    <a href="{{ url('/pricing') }}" class="text-navy hover:text-primary font-medium py-2">Pricing</a>
                    <a href="{{ url('/guide') }}" class="text-primary font-semibold py-2">Guide</a>
                    <a href="{{ url('/about') }}" class="text-navy hover:text-primary font-medium py-2">About</a>
                    <a href="{{ url('/contact') }}" class="text-navy hover:text-primary font-medium py-2">Contact</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <section class="gradient-primary text-white py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-4xl">
                    <div class="inline-flex items-center bg-white/10 px-4 py-2 rounded-full mb-6">
                        <span class="text-sm font-semibold">PayChat Training Library</span>
                    </div>

                    <h1 class="text-4xl lg:text-6xl font-bold leading-tight mb-6">
                        PayChat POS Guide
                    </h1>

                    <p class="text-xl lg:text-2xl text-blue-100 mb-6 leading-relaxed">
                        Learn how to use PayChat POS for billing, takeaway, delivery, dine-in tables, and daily operations.
                    </p>

                    <p class="text-lg text-blue-100 max-w-3xl leading-relaxed">
                        Use these short training videos to quickly understand the main PayChat POS flows. Share this page with your cashier/operator during onboarding.
                    </p>
                </div>
            </div>
        </section>

        @php
            $videos = [
                [
                    'title' => 'Basic Of PayChat POS',
                    'description' => 'Learn the basic layout of PayChat POS, how to navigate the system, and understand the main billing workflow.',
                    'embed' => 'https://www.youtube.com/embed/sBLSAcn7Nhc',
                    'watch' => 'https://youtu.be/sBLSAcn7Nhc',
                ],
                [
                    'title' => 'Manage Multiple Dine-In Tables',
                    'description' => 'Learn how to manage multiple running dine-in tables, switch between tables, view table details, and continue billing.',
                    'embed' => 'https://www.youtube.com/embed/uwNvLzIB4KY',
                    'watch' => 'https://youtu.be/uwNvLzIB4KY',
                ],
                [
                    'title' => 'How to Place Delivery Order',
                    'description' => 'Learn how to create a delivery order, add customer/order details, complete payment, and generate invoice.',
                    'embed' => 'https://www.youtube.com/embed/g8K2EG4IMTs',
                    'watch' => 'https://youtu.be/g8K2EG4IMTs',
                ],
                [
                    'title' => 'Place a Dine-In Order',
                    'description' => 'Learn how to place a dine-in order, assign table, add items, send to kitchen if needed, and proceed to final billing.',
                    'embed' => 'https://www.youtube.com/embed/HysW5SoROlI',
                    'watch' => 'https://youtu.be/HysW5SoROlI',
                ],
                [
                    'title' => 'How to Place Takeaway Order',
                    'description' => 'Learn how to quickly place a takeaway order, add items, collect payment, print bill, and share invoice.',
                    'embed' => 'https://www.youtube.com/embed/u9NQP0Tmleg',
                    'watch' => 'https://youtu.be/u9NQP0Tmleg',
                ],
            ];
        @endphp

        <section class="py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 lg:grid-cols-2">
                    @foreach ($videos as $video)
                        <article class="bg-white rounded-2xl shadow-custom hover:shadow-custom-lg transition-shadow duration-300 overflow-hidden border border-gray-100">
                            <div class="aspect-video bg-navy">
                                <iframe
                                    class="w-full h-full"
                                    src="{{ $video['embed'] }}"
                                    title="{{ $video['title'] }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen>
                                </iframe>
                            </div>

                            <div class="p-6 lg:p-7">
                                <h2 class="text-xl lg:text-2xl font-bold text-navy mb-3">
                                    {{ $video['title'] }}
                                </h2>

                                <p class="text-gray-600 leading-relaxed mb-5">
                                    {{ $video['description'] }}
                                </p>

                                <a href="{{ $video['watch'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-primary hover:text-primary-dark font-semibold">
                                    Open on YouTube
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H8M17 7v9"/>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="pb-16 lg:pb-24">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="gradient-primary text-white rounded-2xl shadow-custom-lg px-6 py-10 lg:px-12 lg:py-12 text-center">
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">Need help getting started?</h2>
                    <p class="text-blue-100 text-lg mb-8">
                        Contact PayChat support or request a demo.
                    </p>

                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ url('/') }}" class="bg-white text-indigo-700 hover:bg-gray-100 px-8 py-4 rounded-xl font-semibold text-center">
                            Back to Home
                        </a>
                        <a href="{{ url('/pos/login') }}" class="bg-navy hover:bg-slate-800 text-white px-8 py-4 rounded-xl font-semibold text-center">
                            Open PayChat Login
                        </a>
                        <a href="{{ url('/contact') }}" class="bg-green-500 hover:bg-green-600 text-white px-8 py-4 rounded-xl font-semibold text-center">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-navy text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-6">
                        <img src="{{ asset('color-paychat-logo-main.svg') }}" alt="PayChat Logo" class="h-12 w-auto filter brightness-0 invert">
                        <div>
                            <p class="text-blue-200 text-sm">Launched Beta 0.1</p>
                        </div>
                    </div>
                    <p class="text-blue-200 mb-6 max-w-md">
                        Revolutionary POS & business management platform. Simplify operations, reduce paper, manage queues smartly, and engage customers through our mobile app-all from one dashboard.
                    </p>

                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors" aria-label="PayChat social link">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors" aria-label="PayChat business link">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="{{ url('/contact') }}" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors" aria-label="Contact PayChat">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ url('/features') }}" class="text-blue-200 hover:text-white transition-colors">Features</a></li>
                        <li><a href="{{ url('/pricing') }}" class="text-blue-200 hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="{{ url('/guide') }}" class="text-blue-200 hover:text-white transition-colors">Guide</a></li>
                        <li><a href="{{ url('/pos/login') }}" class="text-blue-200 hover:text-white transition-colors">Shop Login</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ url('/') }}" class="text-blue-200 hover:text-white transition-colors">Home</a></li>
                        <li><a href="{{ url('/about') }}" class="text-blue-200 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="{{ url('/contact') }}" class="text-blue-200 hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-blue-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-blue-200 text-sm">&copy; 2026 PayChat. All rights reserved by Webspace Studio Pvt Ltd.</p>

                <div class="flex flex-wrap justify-center items-center gap-4 mt-4 md:mt-0 text-sm">
                    <a href="{{ url('/') }}" class="text-blue-200 hover:text-white transition-colors">Home</a>
                    <a href="{{ url('/guide') }}" class="text-blue-200 hover:text-white transition-colors">Guide</a>
                    <a href="{{ url('/contact') }}" class="text-blue-200 hover:text-white transition-colors">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>
