<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $license_id
 * @property string $instance_identifier
 * @property \Illuminate\Support\Carbon $activated_at
 * @property \Illuminate\Support\Carbon|null $deactivated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\License $license
 * @method static \Database\Factories\ActivationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation whereActivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation whereDeactivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation whereInstanceIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation whereLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Activation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'license_id',
        'instance_identifier',
        'activated_at',
        'deactivated_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Check if activation is active
     */
    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }
}
