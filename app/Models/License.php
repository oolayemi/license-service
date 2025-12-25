<?php

namespace App\Models;

use App\Enums\LicenseStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;



/**
 * @property string $id
 * @property string $license_key_id
 * @property string $product_id
 * @property LicenseStatus $status
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property int|null $max_seats
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Activation> $activations
 * @property-read int|null $activations_count
 * @property-read \App\Models\LicenseKey $licenseKey
 * @property-read \App\Models\Product $product
 * @method static \Database\Factories\LicenseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereMaxSeats($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        'id' => 'string',
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
