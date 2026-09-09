<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureAnnouncement extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'key',
        'title',
        'description',
        'action_label',
        'action_url',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function userFeatureAnnouncements(): HasMany
    {
        return $this->hasMany(UserFeatureAnnouncement::class);
    }
}
