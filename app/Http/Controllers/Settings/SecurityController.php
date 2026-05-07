<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('settings/Security', [
            'canManageTwoFactor' => true,
            'requiresConfirmation' => config('fortify.confirmPasswordBeforeEnabling2FA', false),
            'twoFactorEnabled' => ! is_null($user->two_factor_secret),
            'recoveryCodes' => $user->two_factor_recovery_codes
                ? json_decode(is_string($decrypted = decrypt($user->two_factor_recovery_codes)) ? $decrypted : '', true)
                : [],
            'status' => session('status'),
        ]);
    }

    public function update(Request $request, UpdateUserPassword $updater): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array<string, string> $input */
        $input = $request->all();
        $updater->update($user, $input);

        return to_route('settings.security.edit')->with('status', 'password-updated');
    }
}
