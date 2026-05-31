<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class TableSessionTable extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'table_session_id',
        'table_id',
        'role',
        'joined_at',
        'released_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function tableSession()
    {
        return $this->belongsTo(TableSession::class);
    }

    public function table()
    {
        return $this->belongsTo(Resource::class, 'table_id');
    }
}
