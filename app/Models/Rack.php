<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rack extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description'];

    public function subRacks(): HasMany
    {
        return $this->hasMany(SubRack::class);
    }
}
