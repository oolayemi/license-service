<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicenseKey extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'brand_id',
        'customer_email',
        'key',
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
