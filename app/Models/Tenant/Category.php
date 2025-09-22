<?php 

// app/Models/Category.php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'description'];
}
