<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\FeatureAnnouncement;
use App\Models\UserFeatureAnnouncement;
use Illuminate\Http\Request;

class FeatureAnnouncementController extends Controller
{
    public function unseen(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->isTenantUser()) {
            return response()->json(['data' => []]);
        }

        $announcements = FeatureAnnouncement::query()
            ->select(['id', 'key', 'title', 'description', 'action_label', 'action_url', 'published_at'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereDoesntHave('userFeatureAnnouncements', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByRaw('COALESCE(published_at, created_at) asc')
            ->orderBy('id')
            ->limit(10)
            ->get();

        return response()->json(['data' => $announcements]);
    }

    public function seen(Request $request, string $tenantSlug, FeatureAnnouncement $announcement)
    {
        $record = $this->recordFor($request, $announcement);

        if (! $record->seen_at) {
            $record->seen_at = now();
            $record->save();
        }

        return response()->json(['message' => 'Announcement marked seen.']);
    }

    public function actionClicked(Request $request, string $tenantSlug, FeatureAnnouncement $announcement)
    {
        $record = $this->recordFor($request, $announcement);
        $record->seen_at = $record->seen_at ?: now();
        $record->action_clicked_at = $record->action_clicked_at ?: now();
        $record->save();

        return response()->json(['message' => 'Announcement action recorded.']);
    }

    private function recordFor(Request $request, FeatureAnnouncement $announcement): UserFeatureAnnouncement
    {
        $user = $request->user();
        abort_unless($user && $user->isTenantUser(), 403);

        return UserFeatureAnnouncement::firstOrCreate([
            'user_id' => $user->id,
            'feature_announcement_id' => $announcement->id,
        ]);
    }
}
