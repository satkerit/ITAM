<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'address', 'description'])]
class Location extends Model
{
    use HasFactory;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function mutationsFrom(): HasMany
    {
        return $this->hasMany(AssetMutation::class, 'from_location_id');
    }

    public function mutationsTo(): HasMany
    {
        return $this->hasMany(AssetMutation::class, 'to_location_id');
    }
}
