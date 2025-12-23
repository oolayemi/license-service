<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
