<?php

namespace App\Http\Controllers\Brand;

use App\Actions\Licenses\ChangeLicenseLifecycleAction;
use App\Actions\Licenses\ListLicensesByEmailAction;
use App\Actions\Licenses\ProvisionLicenseAction;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\ChangeLicenseLifecycleRequest;
use App\Http\Requests\Brand\ProvisionLicenseRequest;
use App\Models\License;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Provision a license key and licenses for one or more products.
     *
     * @return JsonResponse
     */
    public function provision(ProvisionLicenseRequest $request, ProvisionLicenseAction $provisionAction)
    {
        try {

            $brand = $request->attributes->get('brand'); // this comes from the brand api token middleware

            $licenseKey = $provisionAction->execute(
                $brand,
                $request->input('customer_email'),
                $request->input('product_codes'),
                $request->input('expires_at'),
                $request->input('max_seats')
            );

            return ApiResponse::success([
                'license_key' => $licenseKey->key,
                'customer_email' => $licenseKey->customer_email,
                'licenses' => $licenseKey->licenses->map(fn ($l) => [
                    'product_code' => $l->product->code,
                    'product_name' => $l->product->name,
                    'status' => $l->status->value,
                    'expires_at' => optional($l->expires_at)->toDateTimeString(),
                    'max_seats' => $l->max_seats,
                ]),
            ]);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Change license lifecycle: renew, suspend, resume, cancel
     *
     * @return JsonResponse
     */
    public function changeLifecycle(
        ChangeLicenseLifecycleRequest $request,
        License $license,
        ChangeLicenseLifecycleAction $action
    ) {

        try {
            $updatedLicense = $action->execute(
                $license,
                $request->input('action'),
                $request->input('renew_days')
            );

            return ApiResponse::success([
                'license_id' => $updatedLicense->id,
                'status' => $updatedLicense->status->value,
                'expires_at' => optional($updatedLicense->expires_at)->toDateTimeString(),
            ]);

        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * List all licenses by customer email (across all licenses for this brand)
     *
     * @return JsonResponse
     */
    public function listByEmail(
        Request $request,
        ListLicensesByEmailAction $action
    ) {
        $request->validate([
            'customer_email' => 'required|email',
        ]);

        try {
            $brand = $request->attributes->get('brand'); // from api_token middleware
            $licenses = $action->execute($brand, $request->input('customer_email'));

            return ApiResponse::success([
                'customer_email' => $request->input('customer_email'),
                'licenses' => $licenses,
            ]);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
}
