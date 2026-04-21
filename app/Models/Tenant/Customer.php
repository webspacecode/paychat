<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'pos_customers';

    protected $fillable = ['name','email','phone','address'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
