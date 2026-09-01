@php
    $selectedFeatureIds = $plan?->features?->pluck('id')->map(fn ($id) => (string) $id)->all() ?? [];
@endphp

<label class="grid gap-1 text-sm font-bold text-slate-700">Key
    <input name="key" value="{{ old('key', $plan?->key) }}" class="rounded-md border border-slate-200 px-3 py-2" placeholder="trial" required>
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700">Name
    <input name="name" value="{{ old('name', $plan?->name) }}" class="rounded-md border border-slate-200 px-3 py-2" required>
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700 md:col-span-2">Description
    <textarea name="description" rows="3" class="rounded-md border border-slate-200 px-3 py-2">{{ old('description', $plan?->description) }}</textarea>
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700">Monthly price
    <input name="monthly_price" value="{{ old('monthly_price', $plan?->monthly_price ?? 0) }}" type="number" min="0" step="0.01" class="rounded-md border border-slate-200 px-3 py-2" required>
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700">Yearly price
    <input name="yearly_price" value="{{ old('yearly_price', $plan?->yearly_price ?? 0) }}" type="number" min="0" step="0.01" class="rounded-md border border-slate-200 px-3 py-2" required>
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700">Currency
    <input name="currency" value="{{ old('currency', $plan?->currency ?? 'INR') }}" maxlength="3" class="rounded-md border border-slate-200 px-3 py-2 uppercase" required>
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700">Trial days
    <input name="trial_days" value="{{ old('trial_days', $plan?->trial_days ?? 0) }}" type="number" min="0" max="365" class="rounded-md border border-slate-200 px-3 py-2">
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700">Sort order
    <input name="sort_order" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}" type="number" min="0" max="9999" class="rounded-md border border-slate-200 px-3 py-2">
</label>
<div class="flex items-center gap-4 text-sm font-bold text-slate-700">
    <label class="flex items-center gap-2">
        <input type="checkbox" name="is_trial" value="1" @checked(old('is_trial', $plan?->is_trial)) class="rounded border-slate-300">
        Trial
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan?->is_active ?? true)) class="rounded border-slate-300">
        Active
    </label>
</div>
<fieldset class="md:col-span-2">
    <legend class="text-sm font-black text-slate-900">Included Features</legend>
    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($features as $feature)
            <label class="flex items-start gap-2 rounded-md border border-slate-100 bg-slate-50 p-3 text-sm">
                <input type="checkbox" name="feature_ids[]" value="{{ $feature->id }}" @checked(in_array((string) $feature->id, old('feature_ids', $selectedFeatureIds), true)) class="mt-1 rounded border-slate-300">
                <span>
                    <span class="block font-black text-slate-800">{{ $feature->name }}</span>
                    <span class="block text-xs font-semibold text-slate-500">{{ $feature->key }}</span>
                </span>
            </label>
        @endforeach
    </div>
</fieldset>
