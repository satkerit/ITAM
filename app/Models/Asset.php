<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
     * Terbitkan QR otomatis dari kode aset setiap kali aset disimpan.
     * QR diperbarui bila kode aset berubah, atau diterbitkan ulang bila path kosong / file hilang.
     */
    protected static function booted(): void
    {
        static::saving(function (Asset $asset): void {
            if ($asset->exists
                && ! $asset->isDirty('asset_code')
                && filled($asset->qr_code_path)
                && Storage::disk('public')->exists($asset->qr_code_path)) {
                return;
            }

            $previousPath = $asset->getOriginal('qr_code_path');
            $path = 'qrcodes/'.$asset->asset_code.'.svg';

            Storage::disk('public')->put(
                $path,
                $asset->qrSvg()
            );

            if ($previousPath && $previousPath !== $path) {
                Storage::disk('public')->delete($previousPath);
            }

            $asset->qr_code_path = $path;
        });
    }

    /**
     * SVG QR dari kode aset. Satu-satunya sumber generator QR agar ukuran dan koreksi error konsisten.
     */
    public function qrSvg(int $size = 300): string
    {
        return QrCode::format('svg')->size($size)->errorCorrection('H')->generate($this->asset_code);
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
        return $this->hasOne(AssetAssignment::class)->where('status', AssignmentStatus::Active)->latestOfMany();
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

    /**
     * Aset dengan riwayat operasional tidak boleh dihapus agar audit trail tetap utuh.
     */
    public function hasOperationalHistory(): bool
    {
        return $this->assignments()->exists()
            || $this->mutations()->exists()
            || $this->maintenanceLogs()->exists()
            || $this->disposal()->exists();
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

    /**
     * Batasi kueri ke aset yang boleh dilihat pengguna sesuai lokasinya.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): void
    {
        $locationId = $user?->visibleLocationId();

        if ($locationId !== null) {
            $query->where('location_id', $locationId);
        }
    }
}
