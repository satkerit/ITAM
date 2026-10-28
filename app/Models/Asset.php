<?php

namespace App\Models;

use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'asset_code',
    'category_id',
    'location_id',
    'name',
    'status',
    'serial_number',
    'purchase_date',
    'purchase_cost',
    'purchase_store',
    'photo_path',
    'useful_life_months',
    'specifications',
    'qr_code_path',
    'notes',
])]
class Asset extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => AssetStatus::class,
            'purchase_date' => 'date',
            'purchase_cost' => 'decimal:2',
            'useful_life_months' => 'integer',
            'specifications' => 'array',
        ];
    }

    /**
     * Nilai penyusutan per bulan (metode garis lurus).
     */
    protected function monthlyDepreciation(): Attribute
    {
        return Attribute::get(function (): ?float {
            if (! $this->purchase_cost || ! $this->useful_life_months || $this->useful_life_months < 1) {
                return null;
            }

            return round((float) $this->purchase_cost / $this->useful_life_months, 2);
        });
    }

    /**
     * Akumulasi penyusutan sampai bulan berjalan.
     */
    protected function accumulatedDepreciation(): Attribute
    {
        return Attribute::get(function (): ?float {
            if ($this->monthly_depreciation === null || ! $this->purchase_date) {
                return null;
            }

            $months = max(0, (int) $this->purchase_date->diffInMonths(now()));
            $months = min($months, $this->useful_life_months);

            return round($this->monthly_depreciation * $months, 2);
        });
    }

    /**
     * Nilai buku aset saat ini (tidak pernah negatif).
     */
    protected function bookValue(): Attribute
    {
        return Attribute::get(function (): ?float {
            if ($this->accumulated_depreciation === null) {
                return null;
            }

            return max(0, round((float) $this->purchase_cost - $this->accumulated_depreciation, 2));
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)->where('status', 'active')->latestOfMany();
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(AssetMutation::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function disposal(): HasOne
    {
        return $this->hasOne(AssetDisposal::class);
    }

    public function softwareLicenses(): HasMany
    {
        return $this->hasMany(SoftwareLicense::class);
    }

    public function scopeAvailable(Builder $query): void
    {
        $query->where('status', AssetStatus::Available);
    }

    public function scopeInLocation(Builder $query, int $locationId): void
    {
        $query->where('location_id', $locationId);
    }

    public function scopeInCategory(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }
}
