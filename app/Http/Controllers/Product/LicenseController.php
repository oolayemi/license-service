<?php

namespace App\Http\Controllers\Product;

use App\Actions\Licenses\ActivateLicenseAction;
use App\Actions\Licenses\CheckLicenseStatusAction;
use App\Actions\Licenses\DeactivateSeatAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Activate a license for a specific instance.
     */
    public function activate(
        Request $request,
        ActivateLicenseAction $action
    ) {
        $request->validate([
            'license_key' => 'required|string',
            'product_code' => 'required|string',
            'instance_id' => 'nullable|string',
        ]);

        $activation = $action->execute(
            $request->input('license_key'),
            $request->input('product_code'),
            $request->input('instance_id')
        );

        return response()->json([
            'activation_id' => $activation->id,
            'activated_at' => $activation->activated_at->toDateTimeString(),
            'instance_identifier' => $activation->instance_identifier,
        ]);
    }

    /**
     * Check license status and entitlements.
     */
    public function status(
        Request $request,
        CheckLicenseStatusAction $action
    ) {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $status = $action->execute($request->input('license_key'));

        return response()->json($status);
    }

    /**
     * Deactivate seat / activation.
     */
    public function deactivateSeat(
        Request $request,
        DeactivateSeatAction $action
    ) {
        $request->validate([
            'activation_id' => 'required|string',
        ]);

        $activation = $action->execute($request->input('activation_id'));

        return response()->json([
            'activation_id' => $activation->id,
            'deactivated_at' => $activation->deactivated_at->toDateTimeString(),
        ]);
    }
}
