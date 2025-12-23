<?php

namespace App\Services;

use App\Models\LicenseKey;
use Illuminate\Support\Str;

class LicenseKeyGenerator
{
    /**
     * Generate a unique license key.
     *
     * Format example:
     * RM-ABCDEF-GHIJKL
     */
    public function generate(string $brandSlug): string
    {
        do {
            $key = $this->buildKey($brandSlug);
        } while ($this->exists($key));

        return $key;
    }

    private function buildKey(string $brandSlug): string
    {
        $prefix = strtoupper(Str::substr($brandSlug, 0, 2));

        return Str::upper(sprintf(
            '%s-%s-%s',
            $prefix,
            Str::random(6),
            Str::random(6),
        ));
    }

    private function exists(string $key): bool
    {
        return LicenseKey::query()
            ->where('key', $key)
            ->exists();
    }
}
