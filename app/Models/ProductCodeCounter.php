<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCodeCounter extends Model
{
    protected $fillable = ['prefix', 'last_number'];
}
