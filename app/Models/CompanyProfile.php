<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tagline',
        'description',
        'email',
        'phone',
        'mobile',
        'website',
        'tax_id',
        'address',
        'city',
        'postal_code',
        'country',
        'logo_path',
        'office_photo_path',
    ];

    /**
     * Get or initialize the singleton company profile record.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'name' => 'PT Solusi Teknologi Nusantara',
                'tagline' => 'Enterprise IT Asset Management & Digital Infrastructure',
                'description' => 'Penyedia infrastruktur teknologi informasi dan pengelolaan siklus hidup aset IT korporasi terintegrasi.',
                'email' => 'contact@solusitekno.co.id',
                'phone' => '+62 21 5550 8989',
                'mobile' => '+62 812 3456 7890',
                'website' => 'https://solusitekno.co.id',
                'tax_id' => '01.234.567.8-012.000',
                'address' => 'Gedung Menara Sudirman Lt. 15, Jl. Jend. Sudirman Kav. 60',
                'city' => 'Jakarta Selatan',
                'postal_code' => '12190',
                'country' => 'Indonesia',
            ]
        );
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }

        return null;
    }

    public function getOfficePhotoUrlAttribute(): ?string
    {
        if ($this->office_photo_path && Storage::disk('public')->exists($this->office_photo_path)) {
            return Storage::disk('public')->url($this->office_photo_path);
        }

        return null;
    }
}
