<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class TableSession extends Model
{
    protected $connection = 'tenant';

    protected $appends = [
        'table_display',
    ];

    protected $fillable = [
        'location_id',
        'table_id',
        'order_id',
        'guest_count',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function table()
    {
        return $this->belongsTo(Resource::class, 'table_id');
    }

    public function sessionTables()
    {
        return $this->hasMany(TableSessionTable::class);
    }

    public function tables()
    {
        return $this->belongsToMany(Resource::class, 'table_session_tables', 'table_session_id', 'table_id')
            ->withPivot(['role', 'joined_at', 'released_at'])
            ->withTimestamps()
            ->wherePivotNull('released_at');
    }

    public function primaryTable()
    {
        return $this->hasOneThrough(
            Resource::class,
            TableSessionTable::class,
            'table_session_id',
            'id',
            'id',
            'table_id'
        )
            ->where('table_session_tables.role', 'primary')
            ->whereNull('table_session_tables.released_at');
    }

    public function linkedTables()
    {
        return $this->belongsToMany(Resource::class, 'table_session_tables', 'table_session_id', 'table_id')
            ->withPivot(['role', 'joined_at', 'released_at'])
            ->withTimestamps()
            ->wherePivot('role', 'linked')
            ->wherePivotNull('released_at');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getTableDisplayAttribute(): ?string
    {
        $tables = $this->relationLoaded('tables') && $this->tables->isNotEmpty()
            ? $this->tables
            : collect([$this->relationLoaded('table') ? $this->getRelation('table') : $this->table()->first()])->filter();

        $display = $tables
            ->map(fn ($table) => $this->displayNameForTable($table))
            ->filter()
            ->unique()
            ->values()
            ->implode(' + ');

        return $display !== '' ? $display : null;
    }

    private function displayNameForTable($table): ?string
    {
        if (is_array($table)) {
            return $table['name'] ?? $table['code'] ?? null;
        }

        if (is_object($table)) {
            return $table->name ?? $table->code ?? null;
        }

        return null;
    }
}
