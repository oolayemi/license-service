<?php

namespace App\Models;

use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 */
class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'is_internal',
        'api_token',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    /**
     * @return HasMany<LicenseKey, $this>
     */
    public function licenseKeys(): HasMany
    {
        return $this->hasMany(LicenseKey::class, 'brand_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
