<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PayChat - Revolutionary POS & Business Management Platform. Unified solution for real-time orders, inventory management, and payments.">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="PayChat POS">
    <meta property="og:description" content="POS & QR Ordering Platform">
    <meta property="og:image" content="https://paychat.shop/og-banner.jpg">
    <meta property="og:url" content="https://paychat.shop">
    <title>PayChat - Revolutionary POS & Business Management Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
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
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom animations */
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .fade-in-up.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        /* Gradient backgrounds */
        .gradient-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }
        
        .gradient-secondary {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        }
        
        /* Custom shadows */
        .shadow-custom {
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.1);
        }
        
        .shadow-custom-lg {
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.15);
        }
        
        /* Coming Soon Badge */
        .coming-soon-badge {
            background: linear-gradient(45deg, #f59e0b, #f97316);
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from { box-shadow: 0 0 10px rgba(245, 158, 11, 0.5); }
            to { box-shadow: 0 0 20px rgba(249, 115, 22, 0.8); }
        }
        
        /* App-focused styling */
        .app-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @verbatim
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "PayChat Platform",
    "url": "https://paychat.shop",
    "logo": "https://paychat.shop/favicon-32x32.png"
    }
    </script>
    @endverbatim
</head>

<body class="font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-20">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center space-x-3">
                    <img src="color-paychat-logo-main.svg" alt="PayChat Logo" class="h-10 lg:h-12 w-auto">
                    <div class="flex items-center space-x-2">
                        <!-- <span class="coming-soon-badge text-white text-xs px-3 py-1 rounded-full font-semibold">
                            Early Customers Only
                        </span>                     -->
                    </div>
                </a>
                
                <!-- Desktop Navigation -->
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="{{ url('/features') }}" class="text-navy hover:text-primary font-medium">Features</a>
                    <a href="{{ url('/pricing') }}" class="text-navy hover:text-primary font-medium">Pricing</a>
                    <a href="{{ url('/about') }}" class="text-navy hover:text-primary font-medium">About</a>
                    <a href="{{ url('/contact') }}" class="text-navy hover:text-primary font-medium">Contact</a>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex items-center space-x-3">
                   
                  
                    <!-- <a href="login.html" class="bg-accent hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 text-sm lg:text-base">
                        Shop Login
                    </a> -->
                    
                    <!-- Mobile Menu Button -->
                    <button onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-md text-navy hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <button onclick="openDemoModal()" 
                            class="bg-white text-indigo-700 hover:bg-gray-100 px-8 py-4 rounded-xl font-semibold text-center">
                            Book Free Demo
                        </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div id="mobileMenu" class="lg:hidden hidden border-t border-gray-200 py-4">
                <div class="flex flex-col space-y-3">
                    <a href="{{ url('/features') }}">Features</a>
                    <a href="{{ url('/pricing') }}">Pricing</a>
                    <a href="{{ url('/about') }}">About</a>
                    <a href="{{ url('/contact') }}">Contact</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-primary text-white py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid lg:grid-cols-2 gap-12 items-center">

                <!-- LEFT -->
                <div>

                    <div class="inline-flex items-center bg-white/10 px-4 py-2 rounded-full mb-6">
                        <span class="text-sm font-semibold">
                            Free 30-Day Trial • Setup Support Included
                        </span>
                    </div>

                    <h1 class="text-4xl lg:text-6xl font-bold leading-tight mb-6">
                        Smart POS for
                        <span class="text-secondary">
                            Cafes, Restaurants & Retail Stores
                        </span>
                    </h1>

                    <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                        Billing, KOT, QR ordering, inventory, token system,
                        reports, online orders and customer management —
                        all in one easy-to-use POS platform.
                    </p>

                    <!-- CTA -->
                    <div class="flex flex-col sm:flex-row gap-4">

                        <button onclick="openDemoModal()" 
                            class="bg-white text-indigo-700 hover:bg-gray-100 px-8 py-4 rounded-xl font-semibold text-center">
                            Book Free Demo
                        </button>

                        <a href="https://wa.me/919834969229?text=Hi%20PayChat,%20I%20want%20to%20know%20more"
                        target="_blank"
                        class="bg-green-500 hover:bg-green-600 px-8 py-4 rounded-xl font-semibold text-center">
                            Chat on WhatsApp
                        </a>

                    </div>

                    <!-- TRUST -->
                    <div class="mt-8 flex flex-wrap gap-6 text-sm text-blue-100">

                        <div>✓ No setup fees</div>
                        <div>✓ Runs on laptop/tablet</div>
                        <div>✓ Free onboarding</div>
                        <div>✓ GST-ready billing</div>

                    </div>

                </div>

                <!-- RIGHT -->
                <div class="relative">

                    <div class="bg-white rounded-3xl shadow-2xl p-6">

                        <img
                            src="YOUR_POS_SCREENSHOT.png"
                            class="rounded-2xl w-full"
                        >

                    </div>

                </div>

            </div>

        </div>
    </section>
    <section class="py-16 bg-white">

        <div class="max-w-6xl mx-auto px-6">

            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-navy mb-4">
                    Built for Growing Businesses
                </h2>

                <p class="text-gray-600 text-lg">
                    PayChat works best for businesses handling fast orders,
                    walk-in customers and daily billing.
                </p>
            </div>

            <div class="grid md:grid-cols-4 gap-6">

                <div class="bg-gray-50 rounded-2xl p-6 text-center">
                    ☕
                    <h3 class="font-bold mt-4">Cafes</h3>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 text-center">
                    🍴
                    <h3 class="font-bold mt-4">Restaurants</h3>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 text-center">
                    🛍️
                    <h3 class="font-bold mt-4">Retail Stores</h3>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 text-center">
                    💇
                    <h3 class="font-bold mt-4">Salons</h3>
                </div>

            </div>

        </div>

    </section>
    <!-- Demo Video Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-4xl mx-auto text-center px-6">

            <h2 class="text-3xl font-bold text-navy mb-6">See PayChat in Action</h2>
            <p class="text-gray-600 mb-10">
            Watch how PayChat handles orders, payments, and inventory in real-time.
            </p>

            <div class="rounded-2xl overflow-hidden shadow-lg">
            <iframe 
                class="w-full h-64 md:h-96"
                src="https://www.youtube.com/embed/n6fcyBb1LZk"
                title="PayChat Demo"
                frameborder="0"
                allowfullscreen>
            </iframe>
            </div>

        </div>
    </section>
    

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Bottom Highlight -->
            <div class="mt-16 bg-indigo-50 border border-indigo-100 rounded-3xl p-10 text-center">

                <h3 class="text-3xl font-bold text-navy mb-4">
                    Built For Indian Businesses
                </h3>

                <p class="text-lg text-gray-600 max-w-4xl mx-auto mb-8">
                    PayChat is focused on helping small and medium businesses operate faster,
                    reduce manual work and avoid expensive enterprise software costs.
                </p>

                <div class="flex flex-wrap justify-center gap-4">
                    <span class="bg-white px-5 py-3 rounded-xl shadow text-sm font-semibold">
                        Cafés
                    </span>

                    <span class="bg-white px-5 py-3 rounded-xl shadow text-sm font-semibold">
                        Restaurants
                    </span>

                    <span class="bg-white px-5 py-3 rounded-xl shadow text-sm font-semibold">
                        Retail Shops
                    </span>

                    <span class="bg-white px-5 py-3 rounded-xl shadow text-sm font-semibold">
                        Salons
                    </span>

                    <span class="bg-white px-5 py-3 rounded-xl shadow text-sm font-semibold">
                        Clinics
                    </span>

                    <span class="bg-white px-5 py-3 rounded-xl shadow text-sm font-semibold">
                        Service Businesses
                    </span>
                </div>

            </div>

        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">

            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-navy">Live Partner Businesses</h2>
                <p class="text-gray-600">Discover shops using PayChat (early pilot network)</p>
            </div>

            <!-- Search -->
            <div class="max-w-md mx-auto mb-8">
                <input 
                    placeholder="Search shops..." 
                    class="w-full border p-4 rounded-xl"
                >
            </div>

            <!-- Tenant Grid -->
            <div class="grid md:grid-cols-3 gap-6">

                @forelse($tenants as $tenant)

                    @php
                        $branding = $tenant->branding;

                        $name = $tenant->name ?? 'Unnamed Business';

                        $industry = $tenant->industry ?? 'Business';

                        $address = $branding->address ?? 'Partner Store';

                        $logo = $branding && $branding->logo
                            ? $branding->logo
                            : 'https://dummyimage.com/200x60/ddd/000&text=' . urlencode($name);

                        $shopUrl = url('/store/' . $tenant->slug);
                    @endphp

                    <div class="group bg-white rounded-[28px] border border-gray-100 overflow-hidden shadow-[0_10px_40px_rgba(99,102,241,0.08)] hover:shadow-[0_20px_60px_rgba(99,102,241,0.14)] hover:-translate-y-1 transition-all duration-500">

                        <!-- TOP -->
                        <div class="relative h-24 bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-500">

                            <!-- Glow -->
                            <div class="absolute inset-0 bg-white/5"></div>

                            <!-- Live -->
                            <div class="absolute top-4 right-4">

                                <div class="flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full">

                                    <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>

                                    <span class="text-white text-xs font-semibold">
                                        Live
                                    </span>

                                </div>

                            </div>

                            <!-- Logo -->
                            <div class="absolute -bottom-7 left-6">

                                <div class="w-14 h-14 rounded-2xl bg-white shadow-lg p-1.5">

                                    <img
                                        src="{{ $logo }}"
                                        alt="{{ $name }} logo"
                                        class="w-full h-full object-cover rounded-xl"
                                        loading="lazy"
                                    >

                                </div>

                            </div>

                        </div>

                        <!-- BODY -->
                        <div class="pt-10 px-6 pb-6">

                            <!-- NAME -->
                            <div>

                                <h3 class="text-xl font-bold text-slate-900 leading-tight">
                                    {{ $name }}
                                </h3>

                                <p class="text-sm text-indigo-600 font-medium mt-1">
                                    {{ $industry }}
                                </p>

                            </div>

                            <!-- ADDRESS -->
                            <p class="text-gray-500 text-sm leading-relaxed mt-4 min-h-[44px]">
                                {{ $address }}
                            </p>

                            <!-- REVIEW SECTION -->
                            <div class="mt-5 flex items-center justify-between">

                                <!-- LEFT -->
                                <div class="flex items-center gap-3">

                                    <!-- Stars -->
                                    <div class="flex items-center text-amber-400 text-sm tracking-tight">

                                        @php
                                            $avg = round($tenant->reviews_avg_rating ?? 0);
                                        @endphp

                                        @for($i = 1; $i <= 5; $i++)

                                            @if($i <= $avg)
                                                ★
                                            @else
                                                <span class="text-gray-200">★</span>
                                            @endif

                                        @endfor

                                    </div>

                                    <!-- Rating -->
                                    <div class="flex items-center gap-1">

                                        <span class="text-sm font-bold text-slate-800">

                                            {{ $tenant->reviews_avg_rating ?: 'New' }}

                                        </span>

                                        <span class="text-xs text-gray-400">

                                            / 5

                                        </span>

                                    </div>

                                </div>

                                <!-- COUNT -->
                                <div class="text-sm text-gray-400">

                                    {{ $tenant->reviews_count ?? 0 }} reviews

                                </div>

                            </div>

                            <!-- OPTIONAL REVIEW PREVIEW -->
                            <div class="mt-4 bg-gray-50 rounded-2xl p-4">

                                <p class="text-sm text-gray-600 italic leading-relaxed line-clamp-2">
                                    “Fast service, smooth ordering experience and clean UI.”
                                </p>

                            </div>

                            <!-- FEATURES -->
                            <div class="flex flex-wrap gap-2 mt-5">

                                <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1 rounded-full">
                                    QR Ordering
                                </span>

                                <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1 rounded-full">
                                    POS
                                </span>

                                <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1 rounded-full">
                                    Billing
                                </span>

                            </div>

                            <!-- CTA -->
                            <a
                                href="{{ $shopUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-6 flex items-center justify-between bg-indigo-50 hover:bg-indigo-600 px-5 py-4 rounded-2xl transition-all duration-300 group/button"
                            >

                                <div>

                                    <p class="text-xs text-indigo-500 group-hover/button:text-indigo-100">
                                        Powered by PayChat
                                    </p>

                                    <p class="font-semibold text-slate-800 group-hover/button:text-white mt-1">
                                        Visit Store
                                    </p>

                                </div>

                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-indigo-600 font-bold transition-all duration-300 group-hover/button:translate-x-1">

                                    →

                                </div>

                            </a>

                        </div>

                    </div>

                @empty

                    <div class="col-span-full text-center py-10">

                        <h3 class="text-xl font-semibold text-gray-700">
                            No partner businesses yet
                        </h3>

                        <p class="text-gray-500 mt-2">
                            PayChat is onboarding early merchants. Stay tuned!
                        </p>

                    </div>

                @endforelse

            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="bg-navy text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-6">
                        <img src="color-paychat-logo-main.svg" alt="PayChat Logo" class="h-12 w-auto filter brightness-0 invert">
                        <div>
                            <p class="text-blue-200 text-sm">Launched Beta 0.1</p>
                        </div>
                    </div>
                    <p class="text-blue-200 mb-6 max-w-md">Revolutionary POS & business management platform. Simplify operations, reduce paper, manage queues smartly, and engage customers through our mobile app—all from one dashboard.</p>
                    
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-blue-200 hover:text-white transition-colors">Features</a></li>
                        <li><a href="#benefits" class="text-blue-200 hover:text-white transition-colors">Why PayChat</a></li>
                        <li><a href="#scanner" class="text-blue-200 hover:text-white transition-colors">QR Scanner</a></li>
                        <li><a href="login.html" class="text-blue-200 hover:text-white transition-colors">Shop Login</a></li>
                    </ul>
                </div>
                
                <!-- Company -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-blue-200 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white transition-colors">Careers</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white transition-colors">Contact</a></li>
                        <li><a href="#" class="text-blue-200 hover:text-white transition-colors">Press Kit</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-blue-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-blue-200 text-sm">© 2026 PayChat. All rights reserved by Webspace Studio Pvt Ltd.</p>
                
                <div class="flex flex-wrap justify-center items-center gap-4 mt-4 text-sm text-gray-500">
                    <button onclick="openModal('privacyModal')" class="hover:text-primary transition">
                        Privacy Policy
                    </button>

                    <span>•</span>

                    <button onclick="openModal('termsModal')" class="hover:text-primary transition">
                        Terms of Service
                    </button>
                </div>
            </div>
        </div>
    </footer>

    <!-- Waitlist Success Modal -->
    <div id="waitlistModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full text-center transform scale-95 transition-transform duration-300">
            <div class="w-16 h-16 bg-accent rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-navy mb-4">Welcome to PayChat!</h3>
            <p class="text-gray-600 mb-6">You're now on our exclusive waitlist. We'll notify you as soon as beta testing begins.</p>
            <div class="bg-cream rounded-xl p-4 mb-8">
                <p class="text-sm text-gray-500 mb-1">Your Position:</p>
                <p class="text-2xl font-bold text-primary" id="waitlistPosition">#50</p>
                <p class="text-sm text-gray-500 mt-2">Expected Beta Access: November 2025</p>
            </div>
            <button onclick="closeWaitlistModal()" class="w-full gradient-secondary text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transition-all duration-300">
                Got it!
            </button>
        </div>
    </div>

<script>

async function submitDemo() {

    const button = event.target;

    const originalText = button.innerHTML;

    const payload = {

        name: document.getElementById('demoName').value,

        email: document.getElementById('demoEmail').value,

        phone: document.getElementById('demoPhone').value,

        business_name: document.getElementById('demoBusiness').value,

        business_type: document.getElementById('demoBusinessType').value,

        counters: document.getElementById('demoCounters').value,

        preferred_demo_time: document.getElementById('demoDate').value,

    };

    // Validation
    if (
        !payload.name ||
        !payload.phone ||
        !payload.business_name ||
        !payload.preferred_demo_time
    ) {

        alert("Please fill all required fields");

        return;
    }

    try {

        button.disabled = true;

        button.innerHTML = `
            <div class="flex items-center justify-center gap-2">
                <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                Scheduling...
            </div>
        `;

        const response = await fetch('/api/demo-leads', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },

            body: JSON.stringify(payload)

        });

        const result = await response.json();

        if (!response.ok) {

            throw new Error(result.message || 'Something went wrong');

        }

        // Success UI
        document.getElementById('demoModal').innerHTML = `

            <div class="h-screen w-full bg-white flex flex-col items-center justify-center text-center p-10">

                <div class="w-24 h-24 rounded-full bg-green-100 flex items-center justify-center mb-8">
                    <span class="text-5xl">✓</span>
                </div>

                <h2 class="text-4xl font-bold text-navy mb-4">
                    Demo Scheduled
                </h2>

                <p class="text-gray-600 text-lg max-w-md leading-relaxed">
                    Our team will contact you shortly to confirm your demo timing.
                </p>

                <button
                    onclick="closeDemoModal()"
                    class="mt-10 bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-semibold"
                >
                    Close
                </button>

            </div>

        `;

    } catch (error) {

        console.error(error);

        alert(error.message || 'Failed to submit demo request');

        button.disabled = false;

        button.innerHTML = originalText;

    }
}

function openDemoModal() {

    document
        .getElementById('demoModal')
        .classList.remove('hidden');

    document
        .getElementById('demoModal')
        .classList.add('flex');
}

function closeDemoModal() {

    document
        .getElementById('demoModal')
        .classList.add('hidden');

    document
        .getElementById('demoModal')
        .classList.remove('flex');

    location.reload();
}

// Set minimum datetime
document.addEventListener("DOMContentLoaded", () => {

    const now = new Date();

    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

    const input = document.getElementById('demoDate');

    if (input) {

        input.min = now.toISOString().slice(0,16);

    }
});

</script>

<script>

// Set min date = now
document.addEventListener("DOMContentLoaded", () => {
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

  document.getElementById('demoDate').min = now.toISOString().slice(0,16);
}); 


        let waitlistCount = 10;
        let detectedLink = null;

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }




        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.querySelector('.bg-white').classList.add('scale-95');
            modal.querySelector('.bg-white').classList.remove('scale-100');
        }

        function confirmRedirect() {
            if (detectedLink) {
                closeSuccessModal();
                setTimeout(() => {
                    if (detectedLink.startsWith('http')) {
                        window.open(detectedLink, '_blank');
                    } else {
                        window.location.href = detectedLink;
                    }
                }, 300);
            }
        }

        // Waitlist functionality
        function joinWaitlist() {
            const email = document.getElementById('waitlistEmail');
            if (email) {
                email.focus();
                email.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function submitWaitlist() {
            const email = document.getElementById('waitlistEmail').value;
            if (!email || !email.includes('@')) {
                alert('Please enter a valid email address');
                return;
            }
            
            processWaitlistSubmission();
        }

        function submitEnhancedWaitlist() {
            const businessName = document.getElementById('businessName').value;
            const businessEmail = document.getElementById('businessEmail').value;
            const businessType = document.getElementById('businessType').value;
            
            if (!businessName || !businessEmail || !businessEmail.includes('@') || !businessType) {
                alert('Please fill in all fields');
                return;
            }
            
            processWaitlistSubmission();
        }

        function processWaitlistSubmission() {
            const button = event.target;
            const originalText = button.textContent;
            button.innerHTML = '<div class="flex items-center justify-center"><div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>Joining...</div>';
            button.disabled = true;
            
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                
                // Update waitlist count
                waitlistCount++;
                document.getElementById('waitlistCount').textContent = waitlistCount + '+';
                
                // Show success modal
                document.getElementById('waitlistPosition').textContent = '#' + waitlistCount;
                const modal = document.getElementById('waitlistModal');
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.querySelector('.bg-white').classList.remove('scale-95');
                modal.querySelector('.bg-white').classList.add('scale-100');
                
                // Clear forms
                const emailInput = document.getElementById('waitlistEmail');
                const businessNameInput = document.getElementById('businessName');
                const businessEmailInput = document.getElementById('businessEmail');
                const businessTypeInput = document.getElementById('businessType');
                
                if (emailInput) emailInput.value = '';
                if (businessNameInput) businessNameInput.value = '';
                if (businessEmailInput) businessEmailInput.value = '';
                if (businessTypeInput) businessTypeInput.value = '';
            }, 2000);
        }

        function closeWaitlistModal() {
            const modal = document.getElementById('waitlistModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.querySelector('.bg-white').classList.add('scale-95');
            modal.querySelector('.bg-white').classList.remove('scale-100');
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
                
                // Close mobile menu if open
                const mobileMenu = document.getElementById('mobileMenu');
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            });
        });

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);

        // Observe all fade-in-up elements
        document.querySelectorAll('.fade-in-up').forEach(el => {
            observer.observe(el);
        });

        

        // Update waitlist count periodically (simulate real-time updates)
        setInterval(() => {
            if (Math.random() < 0.3) { // 30% chance every 10 seconds
                waitlistCount++;
                document.getElementById('waitlistCount').textContent = waitlistCount + '+';
            }
        }, 10000);

        function openEarlyBirdForm() {
            document.getElementById('earlyBirdModal').classList.remove('hidden');
        }

        function closeEarlyBirdForm() {
            document.getElementById('earlyBirdModal').classList.add('hidden');
        }
</script>

<script>
let step = 1;

function openEarlyBirdForm() {
  document.getElementById('earlyBirdModal').classList.remove('hidden');
  step = 1;
  updateUI();
}

function closeEarlyBirdForm() {
  document.getElementById('earlyBirdModal').classList.add('hidden');
}

function nextStep() {
  if (step < 3) {
    document.getElementById('step' + step).classList.add('hidden');
    step++;
    document.getElementById('step' + step).classList.remove('hidden');
    updateUI();
  }
}

function updateUI() {
  document.getElementById('stepCount').innerText = step;

  let progress = (step / 3) * 100;
  document.getElementById('progressBar').style.width = progress + '%';
}

function submitForm() {
  const data = {
    businessName: document.getElementById('businessName').value,
    ownerName: document.getElementById('ownerName').value,
    contactNumber: document.getElementById('contactNumber').value
  };

  console.log("Submitted:", data);

  document.querySelectorAll('.step').forEach(s => s.classList.add('hidden'));
  document.getElementById('success').classList.remove('hidden');

  document.getElementById('stepCount').innerText = "Done";
  document.getElementById('progressBar').style.width = "100%";
}
</script>

<!-- Early Bird Typeform Style Modal -->
<div id="earlyBirdModal" class="fixed inset-0 bg-white z-50 hidden flex items-center justify-center">

  <div class="w-full max-w-2xl px-6 text-center">

    <!-- Progress -->
    <div class="mb-10">
      <div class="h-1 bg-gray-200 rounded-full overflow-hidden">
        <div id="progressBar" class="h-full bg-indigo-500 w-1/3 transition-all duration-300"></div>
      </div>
      <p class="text-sm text-gray-400 mt-2">Step <span id="stepCount">1</span> of 3</p>
    </div>

    <!-- STEP 1 -->
    <div id="step1" class="step">
      <h1 class="text-3xl font-bold mb-6">What is your Business Name?</h1>
      <input id="businessName" class="w-full border p-4 rounded-xl text-lg" placeholder="e.g. Cafe Mocha">

      <button onclick="nextStep()" class="mt-8 bg-indigo-600 text-white px-8 py-4 rounded-xl text-lg">
        Next →
      </button>
    </div>

    <!-- STEP 2 -->
    <div id="step2" class="step hidden">
      <h1 class="text-3xl font-bold mb-6">Owner Name?</h1>
      <input id="ownerName" class="w-full border p-4 rounded-xl text-lg" placeholder="e.g. Archit">

      <button onclick="nextStep()" class="mt-8 bg-indigo-600 text-white px-8 py-4 rounded-xl text-lg">
        Next →
      </button>
    </div>

    <!-- STEP 3 -->
    <div id="step3" class="step hidden">
      <h1 class="text-3xl font-bold mb-6">Contact Number?</h1>
      <input id="contactNumber" class="w-full border p-4 rounded-xl text-lg" placeholder="+91 XXXXX XXXXX">

      <button onclick="submitForm()" class="mt-8 bg-green-600 text-white px-8 py-4 rounded-xl text-lg">
        Submit →
      </button>
    </div>

    <!-- SUCCESS -->
    <div id="success" class="hidden">
      <h1 class="text-3xl font-bold text-green-600 mb-4">You're in Early Access 🎉</h1>
      <p class="text-gray-500">We will contact you soon.</p>

      <button onclick="closeEarlyBirdForm()" class="mt-8 bg-gray-200 px-6 py-3 rounded-xl">
        Close
      </button>
    </div>

  </div>
</div>
<!-- WhatsApp Floating Button -->
<a 
  href="https://wa.me/919834969229?text=Hi%20PayChat,%20I%20want%20to%20know%20more%20about%20your%20POS"
  target="_blank"
  class="fixed bottom-6 right-6 bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg z-50"
>
  <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
    <path d="M20.52 3.48A11.91 11.91 0 0012.02 0C5.38 0 .02 5.36.02 12c0 2.12.56 4.18 1.63 6L0 24l6.19-1.62A11.96 11.96 0 0012.02 24c6.64 0 12-5.36 12-12 0-3.19-1.24-6.18-3.5-8.52zM12 22c-1.9 0-3.75-.5-5.38-1.45l-.38-.23-3.67.96.98-3.58-.25-.37A9.94 9.94 0 012 12C2 6.48 6.48 2 12 2s10 4.48 10 10-4.48 10-10 10zm5.17-7.36c-.28-.14-1.65-.82-1.9-.91-.25-.09-.43-.14-.61.14-.18.28-.7.91-.86 1.1-.16.18-.32.21-.6.07-.28-.14-1.18-.43-2.25-1.36-.83-.74-1.4-1.65-1.57-1.93-.16-.28-.02-.43.12-.57.13-.13.28-.32.42-.48.14-.16.18-.28.28-.46.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.83-2.01-.22-.53-.44-.46-.61-.47-.16-.01-.34-.01-.52-.01-.18 0-.48.07-.73.34-.25.28-.96.94-.96 2.29s.99 2.66 1.13 2.85c.14.18 1.96 2.99 4.75 4.19.66.28 1.18.44 1.58.56.66.21 1.26.18 1.73.11.53-.08 1.65-.67 1.88-1.32.23-.65.23-1.2.16-1.32-.07-.11-.25-.18-.53-.32z"/>
  </svg>
</a>

<!-- DEMO MODAL -->
<div
    id="demoModal"
    class="fixed inset-0 bg-white z-[9999] hidden"
>

    <!-- SCROLLABLE AREA -->
    <div class="w-full h-full overflow-y-auto">

        <!-- CLOSE -->
        <button
            onclick="closeDemoModal()"
            class="fixed top-6 right-6 w-12 h-12 rounded-2xl hover:bg-gray-100 flex items-center justify-center text-gray-500 z-50 transition"
        >
            ✕
        </button>

        <!-- CENTER WRAPPER -->
        <div class="min-h-screen w-full flex items-center justify-center px-6 py-16">

            <!-- CONTENT -->
            <div class="w-full max-w-2xl mx-auto">

                <!-- HEADER -->
                <div class="text-center mb-10">

                    <div class="inline-flex items-center bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-sm font-semibold mb-6">
                        Free Personalized Walkthrough
                    </div>

                    <h2 class="text-5xl font-bold text-navy leading-tight">
                        See PayChat Live
                    </h2>

                    <p class="text-gray-500 text-lg mt-5 max-w-xl mx-auto leading-relaxed">
                        Tell us about your business and we'll schedule
                        a quick live demo of billing, QR ordering,
                        reports and setup process.
                    </p>

                </div>

                <!-- TRUST -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-5 mb-8">

                    <div class="flex gap-4 items-start">

                        <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold flex-shrink-0">
                            ✓
                        </div>

                        <div>
                            <h3 class="font-semibold text-indigo-700 text-lg">
                                Quick & Founder-Led Demo
                            </h3>

                            <p class="text-gray-600 mt-1 leading-relaxed">
                                We usually respond within a few hours via WhatsApp or phone.
                                No complicated setup or sales pressure.
                            </p>
                        </div>

                    </div>

                </div>

                <!-- FORM -->
                <div class="space-y-6">

                    <!-- NAME -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Name *
                        </label>

                        <input
                            id="demoName"
                            type="text"
                            placeholder="Your name"
                            class="w-full border border-gray-200 rounded-2xl px-5 py-5 text-lg outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition"
                        >
                    </div>

                    <!-- BUSINESS -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Business Name *
                        </label>

                        <input
                            id="demoBusiness"
                            type="text"
                            placeholder="Cafe Mocha"
                            class="w-full border border-gray-200 rounded-2xl px-5 py-5 text-lg outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition"
                        >
                    </div>

                    <!-- PHONE -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Phone Number *
                        </label>

                        <input
                            id="demoPhone"
                            type="tel"
                            placeholder="+91 XXXXX XXXXX"
                            class="w-full border border-gray-200 rounded-2xl px-5 py-5 text-lg outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition"
                        >
                    </div>

                    <!-- EMAIL -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Email
                        </label>

                        <input
                            id="demoEmail"
                            type="email"
                            placeholder="you@example.com"
                            class="w-full border border-gray-200 rounded-2xl px-5 py-5 text-lg outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition"
                        >
                    </div>

                    <!-- GRID -->
                    <div class="grid md:grid-cols-2 gap-5">

                        <!-- TYPE -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Business Type
                            </label>

                            <select
                                id="demoBusinessType"
                                class="w-full border border-gray-200 rounded-2xl px-5 py-5 text-lg outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition"
                            >
                                <option value="">Select</option>
                                <option>Cafe</option>
                                <option>Restaurant</option>
                                <option>Retail Store</option>
                                <option>Salon</option>
                                <option>Bakery</option>
                                <option>Food Court</option>
                                <option>Other</option>
                            </select>
                        </div>

                        <!-- COUNTERS -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Number of Counters
                            </label>

                            <select
                                id="demoCounters"
                                class="w-full border border-gray-200 rounded-2xl px-5 py-5 text-lg outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition"
                            >
                                <option value="">Select</option>
                                <option>1 Counter</option>
                                <option>2-3 Counters</option>
                                <option>4-6 Counters</option>
                                <option>7+ Counters</option>
                            </select>
                        </div>

                    </div>

                    <!-- DEMO TIME -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Preferred Demo Time *
                        </label>

                        <input
                            type="datetime-local"
                            id="demoDate"
                            min=""
                            class="w-full border border-gray-200 rounded-2xl px-5 py-5 text-lg outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition"
                        />
                    </div>

                    <!-- BUTTON -->
                    <button
                        id="demoSubmitBtn"
                        onclick="submitDemo()"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-5 rounded-2xl font-semibold text-xl transition-all duration-300"
                    >
                        Get Free Demo →
                    </button>

                    <!-- FOOTER -->
                    <p class="text-center text-sm text-gray-400 leading-relaxed">
                        No spam. No commitment required.
                        We'll contact you shortly after submission.
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>
<!-- Privacy Policy Modal -->
<div id="privacyModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-8 relative">

        <button onclick="closeModal('privacyModal')"
            class="absolute top-4 right-4 text-gray-500 hover:text-black text-2xl">
            &times;
        </button>

        <h2 class="text-3xl font-bold mb-6 text-navy">Privacy Policy</h2>

        <div class="space-y-5 text-gray-600 leading-relaxed">

            <p>
                PayChat by Webspace Studio Pvt Ltd values your privacy and is committed
                to protecting your business and customer information.
            </p>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">Information We Collect</h3>
                <p>
                    We may collect business details, contact information, payment data,
                    and usage analytics to improve our services.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">How We Use Data</h3>
                <p>
                    Your data is used to provide POS services, process transactions,
                    improve performance, and enhance customer support.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">Data Security</h3>
                <p>
                    We implement industry-standard security measures to protect your data.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">Third-Party Services</h3>
                <p>
                    Some integrations may involve trusted third-party providers including
                    payment gateways and analytics platforms.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">Contact</h3>
                <p>
                    For privacy-related concerns, contact Webspace Studio Pvt Ltd.
                </p>
            </div>

        </div>
    </div>
</div>
<!-- Terms of Service Modal -->
<div id="termsModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-8 relative">

        <button onclick="closeModal('termsModal')"
            class="absolute top-4 right-4 text-gray-500 hover:text-black text-2xl">
            &times;
        </button>

        <h2 class="text-3xl font-bold mb-6 text-navy">Terms of Service</h2>

        <div class="space-y-5 text-gray-600 leading-relaxed">

            <p>
                By using PayChat services, you agree to comply with these terms.
            </p>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">Service Usage</h3>
                <p>
                    Users must use the platform responsibly and comply with all applicable laws.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">Account Responsibility</h3>
                <p>
                    You are responsible for maintaining the confidentiality of your account.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">Payments</h3>
                <p>
                    Subscription fees and transaction-related charges may apply depending
                    on selected services.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">Limitation of Liability</h3>
                <p>
                    Webspace Studio Pvt Ltd shall not be liable for indirect or consequential damages.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-lg text-black mb-2">Updates</h3>
                <p>
                    Terms may be updated periodically without prior notice.
                </p>
            </div>

        </div>
    </div>
</div>
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('click', function(e) {
        ['privacyModal', 'termsModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (e.target === modal) {
                closeModal(id);
            }
        });
    });
</script>
</body>
</html>
