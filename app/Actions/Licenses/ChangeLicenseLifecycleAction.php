<?php

namespace App\Actions\Licenses;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Observability\Metrics;
use App\Observability\Tracer;
use App\Repositories\LicenseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChangeLicenseLifecycleAction
{
    public function __construct(
        protected LicenseRepository $licenseRepo
    ) {}

    /**
     * Update license lifecycle state.
     *
     * @param  string  $action  'renew'|'suspend'|'resume'|'cancel'
     * @param  int|null  $renewDays  Number of days to extend if action is 'renew'
     *
     * @throws \InvalidArgumentException
     * @throws Throwable
     */
    public function execute(License $license, string $action, ?int $renewDays = null): License
    {
        Tracer::startSpan('license.lifecycle.change', [
            'license_id' => $license->id,
            'action' => $action,
        ]);

        try {
            DB::transaction(function () use ($license, $action, $renewDays) {
                switch ($action) {
                    case 'renew':
                        $this->renew($license, $renewDays);
                        break;
                    case 'suspend':
                        $this->suspend($license);
                        break;
                    case 'resume':
                        $this->resume($license);
                        break;
                    case 'cancel':
                        $this->cancel($license);
                        break;
                    default:
                        throw new \InvalidArgumentException("Invalid lifecycle action: {$action}");
                }
            });

            Metrics::increment('license.lifecycle.changed', 1, [
                'license_id' => $license->id,
                'action' => $action,
            ]);

            Tracer::endSpan('license.lifecycle.change', [
                'license_status' => $license->status->value,
                'expires_at' => optional($license->expires_at)->toDateTimeString(),
            ]);

            return $license->refresh();
        } catch (\Throwable $e) {
            Tracer::endSpan('license.lifecycle.change', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Renew (extend) a license.
     */
    protected function renew(License $license, ?int $days): void
    {
        if (! $days) {
            throw new \InvalidArgumentException('Renewal requires number of days');
        }

        $newExpiry = $license->expires_at
            ? $license->expires_at->addDays($days)
            : Carbon::now()->addDays($days);

        $license->update([
            'expires_at' => $newExpiry,
            'status' => LicenseStatus::VALID,
        ]);
    }

    /**
     * Suspend a license.
     */
    protected function suspend(License $license): void
    {
        $license->update([
            'status' => LicenseStatus::SUSPENDED,
        ]);
    }

    /**
     * Resume a suspended license.
     */
    protected function resume(License $license): void
    {
        if ($license->status !== LicenseStatus::SUSPENDED) {
            throw new \InvalidArgumentException('Only suspended licenses can be resumed');
        }

        $license->update([
            'status' => LicenseStatus::VALID,
        ]);
    }

    /**
     * Cancel a license.
     */
    protected function cancel(License $license): void
    {
        $license->update([
            'status' => LicenseStatus::CANCELLED,
        ]);
    }
}
