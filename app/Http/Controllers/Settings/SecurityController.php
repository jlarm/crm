<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Security', [
            'canManageTwoFactor' => true,
            'requiresConfirmation' => config('fortify.confirmPasswordBeforeEnabling2FA', false),
            'twoFactorEnabled' => ! is_null($request->user()->two_factor_secret),
            'recoveryCodes' => $request->user()->two_factor_recovery_codes
                ? json_decode(decrypt($request->user()->two_factor_recovery_codes), true)
                : [],
            'status' => session('status'),
        ]);
    }

    public function update(Request $request, UpdateUserPassword $updater): RedirectResponse
    {
        $updater->update($request->user(), $request->all());

        return to_route('settings.security.edit')->with('status', 'password-updated');
    }
}
