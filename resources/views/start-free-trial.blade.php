@extends('layouts.marketing', [
    'title' => 'Start Free PayChat POS Trial',
    'description' => 'Start a free PayChat POS trial for your cafe, restaurant, salon or retail store. Get guided setup for billing, QR ordering, KOT, payments, invoices and reports.',
    'canonical' => url('/start-free-trial'),
])

@section('content')
    <section class="border-b border-black/10 bg-paper">
        <div class="pc-container grid gap-10 py-14 lg:grid-cols-[0.9fr_1.1fr] lg:py-20">
            <div class="flex flex-col justify-center">
                <p class="pc-eyebrow">Free trial with guided setup</p>
                <h1 class="pc-page-title mt-6 text-ink">Start your PayChat POS trial.</h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-ink/64">Get a walkthrough for fast billing, QR ordering, dine-in table service, KOT, payments, invoices, inventory and reports.</p>
                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    <div class="pc-card rounded-lg p-5">
                        <p class="text-3xl font-black text-ink">30</p>
                        <p class="mt-1 text-sm font-semibold text-ink/55">day trial access</p>
                    </div>
                    <div class="pc-card rounded-lg p-5">
                        <p class="text-3xl font-black text-ink">GST</p>
                        <p class="mt-1 text-sm font-semibold text-ink/55">ready invoice flow</p>
                    </div>
                </div>
            </div>

            <section class="pc-card rounded-xl p-6 lg:p-8" aria-labelledby="trial-form-title">
                <div class="mb-7">
                    <h2 id="trial-form-title" class="text-2xl font-black text-ink">Tell us about your business</h2>
                    <p class="mt-2 text-sm font-medium text-ink/55">We usually respond within a few hours via WhatsApp or phone.</p>
                </div>

                <form id="trialForm" class="space-y-5">
                    <div>
                        <label for="demoName" class="mb-2 block text-sm font-bold text-ink/70">Name *</label>
                        <input id="demoName" name="name" type="text" required placeholder="Your name" class="w-full rounded-md border border-black/10 px-4 py-4 outline-none transition focus:border-ink">
                    </div>
                    <div>
                        <label for="demoBusiness" class="mb-2 block text-sm font-bold text-ink/70">Business Name *</label>
                        <input id="demoBusiness" name="business_name" type="text" required placeholder="Cafe Mocha" class="w-full rounded-md border border-black/10 px-4 py-4 outline-none transition focus:border-ink">
                    </div>
                    <div>
                        <label for="demoPhone" class="mb-2 block text-sm font-bold text-ink/70">Phone Number *</label>
                        <input id="demoPhone" name="phone" type="tel" required placeholder="+91 XXXXX XXXXX" class="w-full rounded-md border border-black/10 px-4 py-4 outline-none transition focus:border-ink">
                    </div>
                    <div>
                        <label for="demoEmail" class="mb-2 block text-sm font-bold text-ink/70">Email</label>
                        <input id="demoEmail" name="email" type="email" placeholder="you@example.com" class="w-full rounded-md border border-black/10 px-4 py-4 outline-none transition focus:border-ink">
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="demoBusinessType" class="mb-2 block text-sm font-bold text-ink/70">Business Type</label>
                            <select id="demoBusinessType" name="business_type" class="w-full rounded-md border border-black/10 px-4 py-4 outline-none transition focus:border-ink">
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
                        <div>
                            <label for="demoCounters" class="mb-2 block text-sm font-bold text-ink/70">Number of Counters</label>
                            <select id="demoCounters" name="counters" class="w-full rounded-md border border-black/10 px-4 py-4 outline-none transition focus:border-ink">
                                <option value="">Select</option>
                                <option>1 Counter</option>
                                <option>2-3 Counters</option>
                                <option>4-6 Counters</option>
                                <option>7+ Counters</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="demoDate" class="mb-2 block text-sm font-bold text-ink/70">Preferred Demo Time *</label>
                        <input id="demoDate" name="preferred_demo_time" type="datetime-local" required class="w-full rounded-md border border-black/10 px-4 py-4 outline-none transition focus:border-ink">
                    </div>
                    <div id="trialMessage" class="hidden rounded-md p-4 text-sm font-semibold"></div>
                    <button id="trialSubmitBtn" type="submit" class="pc-button pc-button-primary w-full">Start Free Trial</button>
                </form>
            </section>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

            const demoDate = document.getElementById('demoDate');
            if (demoDate) {
                demoDate.min = now.toISOString().slice(0, 16);
            }

            const form = document.getElementById('trialForm');
            const button = document.getElementById('trialSubmitBtn');
            const message = document.getElementById('trialMessage');

            form?.addEventListener('submit', async (event) => {
                event.preventDefault();

                const payload = {
                    name: document.getElementById('demoName').value,
                    email: document.getElementById('demoEmail').value,
                    phone: document.getElementById('demoPhone').value,
                    business_name: document.getElementById('demoBusiness').value,
                    business_type: document.getElementById('demoBusinessType').value,
                    counters: document.getElementById('demoCounters').value,
                    preferred_demo_time: document.getElementById('demoDate').value,
                };

                message.className = 'hidden rounded-md p-4 text-sm font-semibold';

                try {
                    button.disabled = true;
                    button.textContent = 'Scheduling...';

                    const response = await fetch('/api/demo-leads', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Something went wrong');
                    }

                    form.reset();
                    message.textContent = 'Done. Your free trial request has been received. We will contact you shortly.';
                    message.className = 'rounded-md bg-emerald-50 p-4 text-sm font-semibold text-emerald-700';
                } catch (error) {
                    message.textContent = error.message || 'Failed to submit trial request.';
                    message.className = 'rounded-md bg-red-50 p-4 text-sm font-semibold text-red-700';
                } finally {
                    button.disabled = false;
                    button.textContent = 'Start Free Trial';
                }
            });
        });
    </script>
@endpush
