<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $appends = [
        'tables',
        'primary_table',
        'linked_tables',
        'table_display',
        'is_linked_table',
        'linked_primary_table',
        'linked_with',
    ];

    protected $fillable = [
        'location_id',
        'name',
        'code',
        'type',
        'area',
        'floor',
        'capacity',
        'status',
        'pos_x',
        'pos_y',
        'width',
        'height',
        'shape',
        'rotation',
        'sort_order',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class, 'table_id')
            ->where('dining_flow', 'table_service')
            ->whereIn('status', ['draft', 'pending_payment']);
    }

    public function tableSessions()
    {
        return $this->hasMany(TableSession::class, 'table_id');
    }

    public function activeTableSession()
    {
        return $this->hasOne(TableSession::class, 'table_id')
            ->where('status', 'active');
    }

    public function tableSessionLinks()
    {
        return $this->hasMany(TableSessionTable::class, 'table_id');
    }

    public function activeTableSessionsThroughPivot()
    {
        return $this->belongsToMany(TableSession::class, 'table_session_tables', 'table_id', 'table_session_id')
            ->withPivot(['role', 'joined_at', 'released_at'])
            ->wherePivotNull('released_at')
            ->where('table_sessions.status', 'active');
    }

    public function getTablesAttribute()
    {
        $session = $this->getActiveTableSessionForPayload();

        return $session?->tables;
    }

    public function getPrimaryTableAttribute()
    {
        $session = $this->getActiveTableSessionForPayload();

        return $session?->primaryTable ?? $session?->table;
    }

    public function getLinkedTablesAttribute()
    {
        $session = $this->getActiveTableSessionForPayload();

        return $session?->linkedTables;
    }

    public function getTableDisplayAttribute(): ?string
    {
        return $this->getActiveTableSessionForPayload()?->table_display;
    }

    public function getIsLinkedTableAttribute(): bool
    {
        $session = $this->getActiveTableSessionForPayload();

        return $session !== null && (int) $session->table_id !== (int) $this->id;
    }

    public function getLinkedPrimaryTableAttribute()
    {
        return $this->is_linked_table ? $this->primary_table : null;
    }

    public function getLinkedWithAttribute()
    {
        $session = $this->getActiveTableSessionForPayload();

        if (! $session || ! $session->relationLoaded('tables')) {
            return null;
        }

        return $session->tables
            ->reject(fn ($table) => (int) $table->id === (int) $this->id)
            ->values();
    }

    private function getActiveTableSessionForPayload(): ?TableSession
    {
        if ($this->relationLoaded('activeTableSession')) {
            return $this->getRelation('activeTableSession');
        }

        return null;
    }
}
