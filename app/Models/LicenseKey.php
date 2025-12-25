<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $brand_id
 * @property string $customer_email
 * @property string $key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Brand $brand
 * @property-read Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @method static \Database\Factories\LicenseKeyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseKey query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseKey whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseKey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseKey whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseKey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseKey whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseKey whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LicenseKey extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'brand_id',
        'customer_email',
        'key',
    ];

    protected $casts = [
        'id' => 'string'
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'license_key_id');
    }
}
