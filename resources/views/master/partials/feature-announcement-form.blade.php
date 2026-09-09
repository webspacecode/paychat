@php
    $publishedValue = old('published_at', $announcement?->published_at?->timezone(config('app.master_timezone', 'Asia/Kolkata'))->format('Y-m-d\TH:i'));
@endphp

<label class="grid gap-1 text-sm font-bold text-slate-700 md:col-span-2">Title
    <input name="title" value="{{ old('title', $announcement?->title) }}" class="rounded-md border border-slate-200 px-3 py-2" required>
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700 md:col-span-2">Description
    <textarea name="description" rows="4" class="rounded-md border border-slate-200 px-3 py-2" required>{{ old('description', $announcement?->description) }}</textarea>
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700">Action Button Label
    <input name="action_label" value="{{ old('action_label', $announcement?->action_label) }}" class="rounded-md border border-slate-200 px-3 py-2" placeholder="Configure">
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700">Action URL
    <input name="action_url" value="{{ old('action_url', $announcement?->action_url) }}" class="rounded-md border border-slate-200 px-3 py-2" placeholder="/settings">
</label>
<label class="grid gap-1 text-sm font-bold text-slate-700">Published At
    <input type="datetime-local" name="published_at" value="{{ $publishedValue }}" class="rounded-md border border-slate-200 px-3 py-2">
</label>
<label class="flex items-center gap-2 text-sm font-bold text-slate-700">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $announcement?->is_active ?? true)) class="rounded border-slate-300">
    Active
</label>
