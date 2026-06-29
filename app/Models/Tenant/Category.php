<?php 

// app/Models/Category.php
namespace App\Models\Tenant;

class Category extends BaseTenantModel
{
    protected $fillable = ['name', 'description'];

    protected $hidden = ['pivot'];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
