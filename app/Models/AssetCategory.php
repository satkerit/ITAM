<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'description', 'schema_fields'])]
class AssetCategory extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'schema_fields' => 'array',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    public function softwareLicenses(): HasMany
    {
        return $this->hasMany(SoftwareLicense::class, 'category_id');
    }
}
