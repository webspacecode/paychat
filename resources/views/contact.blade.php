@extends('layouts.marketing', [
    'title' => 'Contact PayChat | POS Demo, Pricing & Setup Support',
    'description' => 'Talk to PayChat about a POS demo, pricing, onboarding or billing setup for your cafe, restaurant, bakery, salon, retail shop or service business in India.',
    'keywords' => 'contact PayChat, POS demo India, billing software demo, cafe POS support, salon POS setup, restaurant billing onboarding, PayChat sales',
    'canonical' => url('/contact'),
])

@section('content')
    <section class="pc-surface-hero">
        <div class="pc-container grid gap-10 py-14 lg:grid-cols-[0.9fr_1.1fr] lg:py-20">
            <div class="flex flex-col justify-center">
                <p class="pc-eyebrow">Contact</p>
                <h1 class="pc-page-title mt-4 text-ink">Talk to a real person.</h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-ink/64">Ask about PayChat setup, pricing, onboarding or whether it fits your cafe, restaurant, salon, bakery or service business.</p>
                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    <div class="pc-soft-card p-5">
                        <p class="text-sm font-black uppercase tracking-wide text-ink/40">WhatsApp</p>
                        <a href="https://wa.me/919834969229?text=Hi%20PayChat,%20I%20want%20to%20know%20more" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex text-lg font-black text-ink hover:text-primary">+91 98349 69229</a>
                    </div>
                    <div class="pc-soft-card p-5">
                        <p class="text-sm font-black uppercase tracking-wide text-ink/40">Email</p>
                        <a href="mailto:hello@paychat.shop" class="mt-2 inline-flex text-lg font-black text-ink hover:text-primary">hello@paychat.shop</a>
                    </div>
                </div>
            </div>

            <section class="pc-soft-card p-6 lg:p-8" aria-labelledby="contact-form-title">
                <h2 id="contact-form-title" class="text-2xl font-black text-ink">Send your details</h2>
                <p class="mt-2 text-sm leading-6 text-ink/55">The form opens WhatsApp with your message ready to send.</p>
                <div class="mt-7 space-y-4">
                    <input id="name" placeholder="Your name *" class="pc-input">
                    <input id="phone" placeholder="Phone number *" class="pc-input">
                    <input id="business" placeholder="Business name" class="pc-input">
                    <button type="button" onclick="sendWhatsApp()" class="pc-button w-full bg-[#25D366] text-white hover:bg-[#1fb95a]">Send on WhatsApp</button>
                </div>
            </section>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function sendWhatsApp() {
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const business = document.getElementById('business').value.trim();

            if (!name || !phone) {
                alert('Please fill your name and phone number.');
                return;
            }

            const msg = `Hi PayChat,%0AName: ${encodeURIComponent(name)}%0APhone: ${encodeURIComponent(phone)}%0ABusiness: ${encodeURIComponent(business)}`;
            window.open(`https://wa.me/919834969229?text=${msg}`, '_blank');
        }
    </script>
@endpush
