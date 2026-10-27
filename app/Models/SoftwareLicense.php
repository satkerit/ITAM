<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'license_key',
    'category_id',
    'asset_id',
    'assigned_user_id',
    'seats',
    'purchase_date',
    'expiration_date',
    'cost',
    'notes',
])]
class SoftwareLicense extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'expiration_date' => 'date',
            'cost' => 'decimal:2',
            'seats' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
