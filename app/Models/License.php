<?php

namespace App\Models;

use App\Enums\LicenseStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'license_key_id',
        'product_id',
        'status',
        'expires_at',
        'max_seats',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'max_seats' => 'integer',
        'status' => LicenseStatus::class,
    ];

    public function licenseKey(): BelongsTo
    {
        return $this->belongsTo(LicenseKey::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function activations(): HasMany
    {
        return $this->hasMany(Activation::class, 'license_id');
    }

    /**
     * Check if the license is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    /**
     * Check if license is active (usable)
     */
    public function isActive(): bool
    {
        return $this->status === LicenseStatus::VALID && ! $this->isExpired();
    }

    public function isUsable(): bool
    {
        // Only VALID licenses can be used
        if ($this->status !== LicenseStatus::VALID) {
            return false;
        }

        // Expired licenses are not usable
        if ($this->isExpired()) {
            return false;
        }

        // Future extension: check seat availability, etc.
        // if ($this->max_seats && $this->activations()->whereNull('deactivated_at')->count() >= $this->max_seats) {
        //     return false;
        // }

        return true;
    }
}
