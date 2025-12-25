<?php

namespace App\Models;

use Database\Factories\LicenseKeyFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $key
 * @property string $brand_id
 * @property string|null $customer_email
 * @property Collection<int, License> $licenses
 */
class LicenseKey extends Model
{
    /** @use HasFactory<LicenseKeyFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'brand_id',
        'customer_email',
        'key',
    ];

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return HasMany<License, $this>
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'license_key_id');
    }
}
