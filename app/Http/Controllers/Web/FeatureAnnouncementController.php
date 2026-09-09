<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FeatureAnnouncement;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeatureAnnouncementController extends Controller
{
    public function index(): View
    {
        return view('master.feature-announcements', [
            'announcements' => FeatureAnnouncement::withCount('userFeatureAnnouncements')
                ->latest('published_at')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAnnouncement($request);
        $validated['key'] = $this->uniqueKey($validated['title']);
        $validated = $this->normalizePublication($validated);

        FeatureAnnouncement::create($validated);

        return back()->with('status', 'Announcement created.');
    }

    public function update(Request $request, FeatureAnnouncement $announcement): RedirectResponse
    {
        $announcement->update($this->normalizePublication($this->validateAnnouncement($request)));

        return back()->with('status', 'Announcement updated.');
    }

    public function toggle(Request $request, FeatureAnnouncement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $updates = ['is_active' => (bool) $validated['is_active']];

        if ($updates['is_active'] && ! $announcement->published_at) {
            $updates['published_at'] = now();
        }

        $announcement->update($updates);

        return back()->with('status', $announcement->fresh()->is_active ? 'Announcement activated.' : 'Announcement deactivated.');
    }

    private function validateAnnouncement(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'action_label' => ['nullable', 'string', 'max:255'],
            'action_url' => ['nullable', 'string', 'max:255', 'regex:/^\\/[A-Za-z0-9_\\-\\/?.=&%#]*$/'],
            'is_active' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function normalizePublication(array $data): array
    {
        if (($data['action_label'] ?? null) === '') {
            $data['action_label'] = null;
        }

        if (($data['action_url'] ?? null) === '') {
            $data['action_url'] = null;
        }

        if (! empty($data['published_at'])) {
            $data['published_at'] = Carbon::parse($data['published_at'], $this->masterTimezone())->utc();
        }

        if (($data['is_active'] ?? false) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function uniqueKey(string $title): string
    {
        $base = Str::slug($title) ?: 'announcement';
        $key = $base.'-'.now()->format('YmdHis');

        while (FeatureAnnouncement::where('key', $key)->exists()) {
            $key = $base.'-'.now()->format('YmdHis').'-'.Str::lower(Str::random(4));
        }

        return $key;
    }

    private function masterTimezone(): string
    {
        return config('app.master_timezone', 'Asia/Kolkata');
    }
}
