@php
    $title = $title ?? 'PayChat - Fast Billing for Local Businesses';
    $description = $description ?? 'PayChat is a simple POS for cafes, salons, restaurants and local shops with billing, orders, tokens, invoices and reports.';
    $canonical = $canonical ?? url()->current();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ asset('color-paychat-logo-main.svg') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pc-blue: #1F5EFF;
            --pc-blue-dark: #174BD2;
            --pc-ink: #111827;
            --pc-muted: rgba(17, 24, 39, .62);
            --pc-line: rgba(31, 94, 255, .12);
            --pc-paper: #F6F9FF;
            --pc-bone: #EEF5FF;
            --pc-glass: rgba(255, 255, 255, .68);
        }
        html { scroll-behavior: smooth; }
        body {
            background:
                radial-gradient(circle at 24% -10%, rgba(31, 94, 255, .18), transparent 34rem),
                radial-gradient(circle at 88% 4%, rgba(31, 94, 255, .09), transparent 30rem),
                linear-gradient(180deg, #ffffff 0%, #f6f9ff 48%, #ffffff 100%);
            color: var(--pc-ink);
            text-rendering: geometricPrecision;
        }
        .pc-container { width: 100%; max-width: 80rem; margin-left: auto; margin-right: auto; padding-left: 1rem; padding-right: 1rem; }
        @media (min-width: 640px) { .pc-container { padding-left: 1.5rem; padding-right: 1.5rem; } }
        @media (min-width: 1024px) { .pc-container { padding-left: 2rem; padding-right: 2rem; } }
        .pc-section { padding-top: 5.75rem; padding-bottom: 5.75rem; }
        @media (max-width: 640px) { .pc-section { padding-top: 4rem; padding-bottom: 4rem; } }
        .pc-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border: 1px solid var(--pc-line);
            background: rgba(255, 255, 255, .72);
            padding: .55rem .85rem;
            border-radius: 999px;
            color: rgba(17, 24, 39, .62);
            font-size: .68rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: .14em;
            text-transform: uppercase;
            box-shadow: 0 10px 28px rgba(31, 94, 255, .06);
        }
        .pc-title { font-size: 2.65rem; line-height: .98; font-weight: 850; letter-spacing: -.045em; }
        .pc-page-title { font-size: 2.35rem; line-height: 1.02; font-weight: 850; letter-spacing: -.04em; }
        .pc-section-title { font-size: 2.05rem; line-height: 1.05; font-weight: 850; letter-spacing: -.035em; }
        @media (min-width: 640px) {
            .pc-title { font-size: 4rem; }
            .pc-page-title { font-size: 3.35rem; }
            .pc-section-title { font-size: 2.95rem; }
        }
        @media (min-width: 1024px) {
            .pc-title { font-size: 5.25rem; }
            .pc-page-title { font-size: 4.2rem; }
            .pc-section-title { font-size: 3.8rem; }
        }
        .pc-card {
            border: 1px solid var(--pc-line);
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, .76);
            box-shadow: 0 20px 60px rgba(31, 94, 255, .08);
            backdrop-filter: blur(22px);
        }
        .pc-panel {
            border: 1px solid var(--pc-line);
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, .8);
            box-shadow: 0 24px 80px rgba(31, 94, 255, .1);
            backdrop-filter: blur(22px);
        }
        .pc-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3.35rem;
            border-radius: 999px;
            padding: .9rem 1.3rem;
            font-size: .875rem;
            font-weight: 800;
            transition: transform .2s ease, background .2s ease, border-color .2s ease, color .2s ease;
        }
        .pc-button:hover { transform: translateY(-1px); }
        .pc-button:focus-visible {
            outline: 3px solid rgba(31, 94, 255, .24);
            outline-offset: 2px;
        }
        .pc-button-primary { background: var(--pc-blue); color: white; box-shadow: 0 18px 44px rgba(31, 94, 255, .24); }
        .pc-button-primary:hover { background: var(--pc-blue-dark); }
        .pc-button-secondary { border: 1px solid rgba(31, 94, 255, .16); background: rgba(255, 255, 255, .88); color: #08111F; }
        .pc-button-secondary:hover { border-color: rgba(31, 94, 255, .32); background: #fff; }
        .pc-input {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgba(8, 17, 31, .1);
            background: #fff;
            padding: 1rem 1.1rem;
            font-size: .95rem;
            font-weight: 600;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .pc-input:focus {
            border-color: rgba(31, 94, 255, .58);
            box-shadow: 0 0 0 4px rgba(31, 94, 255, .1);
        }
        .pc-dot {
            display: inline-flex;
            width: .55rem;
            height: .55rem;
            border-radius: 999px;
            background: var(--pc-blue);
            box-shadow: 0 0 0 5px rgba(31, 94, 255, .12);
        }
        .pc-drawer {
            visibility: hidden;
            pointer-events: none;
        }
        .pc-drawer.is-open {
            visibility: visible;
            pointer-events: auto;
        }
        .pc-drawer-panel {
            transform: translateX(100%);
            transition: transform .28s ease;
        }
        .pc-drawer.is-open .pc-drawer-panel {
            transform: translateX(0);
        }
        .pc-drawer-backdrop {
            opacity: 0;
            transition: opacity .28s ease;
        }
        .pc-drawer.is-open .pc-drawer-backdrop {
            opacity: 1;
        }
        @media (max-width: 380px) {
            .pc-title { font-size: 2.25rem; }
            .pc-page-title { font-size: 2rem; }
            .pc-section-title { font-size: 1.85rem; }
            .pc-button { width: 100%; }
        }
    </style>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @stack('head')
</head>
<body class="font-sans antialiased">
    <nav class="sticky top-0 z-50 border-b border-white/50 bg-white/55 shadow-[0_1px_0_rgba(255,255,255,.65)_inset,0_16px_40px_rgba(31,94,255,.07)] backdrop-blur-2xl">
        <div class="pc-container flex h-16 items-center justify-between lg:h-[4.35rem]">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="PayChat home">
                <img src="{{ asset('color-paychat-logo-main.svg') }}" alt="PayChat Logo" class="h-8 w-auto lg:h-10">
            </a>

            <div class="hidden items-center gap-1 rounded-full border border-white/70 bg-white/58 p-1 text-sm font-semibold text-ink/62 shadow-[0_10px_30px_rgba(31,94,255,0.08)] backdrop-blur-2xl lg:flex">
                <a href="{{ url('/features') }}" class="rounded-full px-4 py-2 transition hover:bg-white hover:text-primary hover:shadow-[0_8px_24px_rgba(31,94,255,.08)]">Features</a>
                <a href="{{ url('/pricing') }}" class="rounded-full px-4 py-2 transition hover:bg-white hover:text-primary hover:shadow-[0_8px_24px_rgba(31,94,255,.08)]">Pricing</a>
                <a href="{{ url('/guide') }}" class="rounded-full px-4 py-2 transition hover:bg-white hover:text-primary hover:shadow-[0_8px_24px_rgba(31,94,255,.08)]">Guide</a>
                <a href="{{ url('/about') }}" class="rounded-full px-4 py-2 transition hover:bg-white hover:text-primary hover:shadow-[0_8px_24px_rgba(31,94,255,.08)]">About</a>
                <a href="{{ url('/contact') }}" class="rounded-full px-4 py-2 transition hover:bg-white hover:text-primary hover:shadow-[0_8px_24px_rgba(31,94,255,.08)]">Contact</a>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ url('/start-free-trial') }}" class="hidden rounded-full bg-primary px-5 py-3 text-sm font-bold text-white shadow-[0_16px_38px_rgba(31,94,255,.22)] transition hover:-translate-y-0.5 hover:bg-[#174bd2] sm:inline-flex">
                    Start Free Trial
                </a>
                <button type="button" onclick="openDrawer()" class="rounded-full border border-black/10 bg-white/80 p-2 text-ink shadow-soft transition hover:bg-white lg:hidden" aria-label="Open navigation">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <div id="mobileDrawer" class="pc-drawer fixed inset-0 z-[60] lg:hidden" aria-hidden="true">
        <button type="button" onclick="closeDrawer()" class="pc-drawer-backdrop absolute inset-0 bg-ink/45 backdrop-blur-sm" aria-label="Close navigation"></button>
        <aside class="pc-drawer-panel absolute bottom-0 right-0 top-0 flex w-[min(88vw,24rem)] flex-col border-l border-black/10 bg-paper shadow-lift">
            <div class="flex h-16 items-center justify-between border-b border-black/10 px-5">
                <img src="{{ asset('color-paychat-logo-main.svg') }}" alt="PayChat Logo" class="h-9 w-auto">
                <button type="button" onclick="closeDrawer()" class="flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-ink" aria-label="Close navigation">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-5 py-6">
                <div class="space-y-2">
                    @foreach([
                        ['Features', '/features', 'Billing, orders and reports'],
                        ['Pricing', '/pricing', 'Plans for each stage'],
                        ['Guide', '/guide', 'Training videos'],
                        ['About', '/about', 'Founder-led product'],
                        ['Contact', '/contact', 'Talk to PayChat'],
                    ] as [$label, $path, $hint])
                        <a href="{{ url($path) }}" class="block rounded-2xl border border-primary/10 bg-white/82 p-4 shadow-soft transition hover:border-primary/30 hover:bg-white">
                            <span class="block text-base font-black text-ink">{{ $label }}</span>
                            <span class="mt-1 block text-sm font-medium text-ink/54">{{ $hint }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="border-t border-black/10 p-5">
                <a href="{{ url('/start-free-trial') }}" class="pc-button pc-button-primary w-full">Start Free Trial</a>
                <a href="https://wa.me/919834969229?text=Hi%20PayChat,%20I%20want%20to%20know%20more" target="_blank" rel="noopener noreferrer" class="pc-button pc-button-secondary mt-3 w-full">WhatsApp PayChat</a>
            </div>
        </aside>
    </div>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-primary/10 bg-[#f6f9ff] py-14">
        <div class="pc-container">
            <div class="grid gap-9 md:grid-cols-[1.4fr_0.8fr_0.8fr_0.8fr]">
                <div>
                    <img src="{{ asset('color-paychat-logo-main.svg') }}" alt="PayChat Logo" class="h-11 w-auto">
                    <p class="mt-5 max-w-md text-sm leading-7 text-ink/62">
                        Reliable billing software for cafes, restaurants, bakeries, salons and retail stores.
                    </p>
                    <a href="https://wa.me/919834969229?text=Hi%20PayChat,%20I%20want%20to%20know%20more%20about%20your%20POS" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex text-sm font-bold text-primary hover:text-ink">
                        WhatsApp: +91 98349 69229
                    </a>
                </div>

                <div>
                    <h4 class="text-sm font-black uppercase tracking-wide text-ink">Product</h4>
                    <ul class="mt-4 space-y-3 text-sm font-medium text-ink/58">
                        <li><a href="{{ url('/features') }}" class="hover:text-ink">Features</a></li>
                        <li><a href="{{ url('/pricing') }}" class="hover:text-ink">Pricing</a></li>
                        <li><a href="{{ url('/guide') }}" class="hover:text-ink">Guide</a></li>
                        <li><a href="{{ url('/start-free-trial') }}" class="hover:text-ink">Start Free Trial</a></li>
                        <li><a href="{{ url('/login') }}" class="hover:text-ink">Shop Login</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-black uppercase tracking-wide text-ink">For</h4>
                    <ul class="mt-4 space-y-3 text-sm font-medium text-ink/58">
                        <li><a href="{{ url('/features') }}" class="hover:text-ink">Cafes</a></li>
                        <li><a href="{{ url('/features') }}" class="hover:text-ink">Restaurants</a></li>
                        <li><a href="{{ url('/features') }}" class="hover:text-ink">Bakeries</a></li>
                        <li><a href="{{ url('/features') }}" class="hover:text-ink">Salons</a></li>
                        <li><a href="{{ url('/features') }}" class="hover:text-ink">Retail shops</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-black uppercase tracking-wide text-ink">Company</h4>
                    <ul class="mt-4 space-y-3 text-sm font-medium text-ink/58">
                        <li><a href="{{ url('/about') }}" class="hover:text-ink">About</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-ink">Contact</a></li>
                        <li><button type="button" onclick="openModal('privacyModal')" class="hover:text-ink">Privacy Policy</button></li>
                        <li><button type="button" onclick="openModal('termsModal')" class="hover:text-ink">Terms of Service</button></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-4 border-t border-black/10 pt-6 text-sm text-ink/50 md:flex-row md:items-center md:justify-between">
                <p>&copy; 2026 PayChat. All rights reserved by Webspace Studio Pvt Ltd.</p>
                <p class="font-medium text-ink/45">Built for everyday Indian counters.</p>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/919834969229?text=Hi%20PayChat,%20I%20want%20to%20know%20more%20about%20your%20POS" target="_blank" rel="noopener noreferrer" class="fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lift transition hover:-translate-y-0.5 hover:bg-[#1fb95a]" aria-label="Chat with PayChat on WhatsApp">
        <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20.52 3.48A11.91 11.91 0 0012.02 0C5.38 0 .02 5.36.02 12c0 2.12.56 4.18 1.63 6L0 24l6.19-1.62A11.96 11.96 0 0012.02 24c6.64 0 12-5.36 12-12 0-3.19-1.24-6.18-3.5-8.52zM12 22c-1.9 0-3.75-.5-5.38-1.45l-.38-.23-3.67.96.98-3.58-.25-.37A9.94 9.94 0 012 12C2 6.48 6.48 2 12 2s10 4.48 10 10-4.48 10-10 10zm5.17-7.36c-.28-.14-1.65-.82-1.9-.91-.25-.09-.43-.14-.61.14-.18.28-.7.91-.86 1.1-.16.18-.32.21-.6.07-.28-.14-1.18-.43-2.25-1.36-.83-.74-1.4-1.65-1.57-1.93-.16-.28-.02-.43.12-.57.13-.13.28-.32.42-.48.14-.16.18-.28.28-.46.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.83-2.01-.22-.53-.44-.46-.61-.47-.16-.01-.34-.01-.52-.01-.18 0-.48.07-.73.34-.25.28-.96.94-.96 2.29s.99 2.66 1.13 2.85c.14.18 1.96 2.99 4.75 4.19.66.28 1.18.44 1.58.56.66.21 1.26.18 1.73.11.53-.08 1.65-.67 1.88-1.32.23-.65.23-1.2.16-1.32-.07-.11-.25-.18-.53-.32z"/>
        </svg>
    </a>

    <div id="privacyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
        <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-8 shadow-lift">
            <button type="button" onclick="closeModal('privacyModal')" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-md text-2xl text-ink/45 hover:bg-paper hover:text-ink">&times;</button>
            <h2 class="mb-6 text-3xl font-black text-ink">Privacy Policy</h2>
            <div class="space-y-5 text-sm leading-7 text-ink/62">
                <p>PayChat by Webspace Studio Pvt Ltd values your privacy and is committed to protecting your business and customer information.</p>
                <div><h3 class="mb-2 text-lg font-black text-ink">Information We Collect</h3><p>We may collect business details, contact information, payment data and usage analytics to improve our services.</p></div>
                <div><h3 class="mb-2 text-lg font-black text-ink">How We Use Data</h3><p>Your data is used to provide POS services, process transactions, improve performance and support customers.</p></div>
                <div><h3 class="mb-2 text-lg font-black text-ink">Data Security</h3><p>We use reasonable security measures to protect business data handled through the platform.</p></div>
                <div><h3 class="mb-2 text-lg font-black text-ink">Contact</h3><p>For privacy-related concerns, contact Webspace Studio Pvt Ltd through the PayChat contact page.</p></div>
            </div>
        </div>
    </div>

    <div id="termsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
        <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-8 shadow-lift">
            <button type="button" onclick="closeModal('termsModal')" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-md text-2xl text-ink/45 hover:bg-paper hover:text-ink">&times;</button>
            <h2 class="mb-6 text-3xl font-black text-ink">Terms of Service</h2>
            <div class="space-y-5 text-sm leading-7 text-ink/62">
                <p>By using PayChat services, you agree to use the platform responsibly and comply with applicable laws.</p>
                <div><h3 class="mb-2 text-lg font-black text-ink">Service Usage</h3><p>Users are responsible for the accuracy of business, product, billing and customer information entered into the platform.</p></div>
                <div><h3 class="mb-2 text-lg font-black text-ink">Account Responsibility</h3><p>You are responsible for maintaining the confidentiality of your account access.</p></div>
                <div><h3 class="mb-2 text-lg font-black text-ink">Payments</h3><p>Subscription fees and transaction-related charges may apply depending on selected services.</p></div>
                <div><h3 class="mb-2 text-lg font-black text-ink">Updates</h3><p>Terms may be updated periodically as the product and services evolve.</p></div>
            </div>
        </div>
    </div>

    <script>
        function openDrawer() {
            const drawer = document.getElementById('mobileDrawer');
            drawer?.classList.add('is-open');
            drawer?.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            const drawer = document.getElementById('mobileDrawer');
            drawer?.classList.remove('is-open');
            drawer?.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = 'auto';
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            modal?.classList.remove('hidden');
            modal?.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            modal?.classList.add('hidden');
            modal?.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        window.addEventListener('click', function (event) {
            ['privacyModal', 'termsModal'].forEach((id) => {
                const modal = document.getElementById(id);
                if (event.target === modal) {
                    closeModal(id);
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
