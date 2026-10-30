<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'location_id', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Batas lokasi yang boleh diakses pengguna. Null berarti tidak dibatasi.
     */
    public function visibleLocationId(): ?int
    {
        return $this->isAdmin() ? null : $this->location_id;
    }

    public function canAccessLocation(?int $locationId): bool
    {
        $scopedLocationId = $this->visibleLocationId();

        return $scopedLocationId === null || $scopedLocationId === $locationId;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'user_id');
    }

    public function createdAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'assigned_by');
    }

    public function processedMutations(): HasMany
    {
        return $this->hasMany(AssetMutation::class, 'processed_by');
    }

    public function performedMaintenances(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'performed_by');
    }

    public function authorizedDisposals(): HasMany
    {
        return $this->hasMany(AssetDisposal::class, 'authorized_by');
    }

    public function assignedSoftwareLicenses(): HasMany
    {
        return $this->hasMany(SoftwareLicense::class, 'assigned_user_id');
    }
}
