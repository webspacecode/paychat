<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeatureAnnouncement extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'user_id',
        'feature_announcement_id',
        'seen_at',
        'action_clicked_at',
    ];

    protected $casts = [
        'seen_at' => 'datetime',
        'action_clicked_at' => 'datetime',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(FeatureAnnouncement::class, 'feature_announcement_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
