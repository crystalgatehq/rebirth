<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    /**
     * Show the two-factor authentication challenge view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (! session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the two-factor authentication code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = User::findOrFail(session('login.id'));

        $google2fa = app(Google2FA::class);
        
        // Verify the OTP code
        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code
        );

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => __('The provided two factor authentication code was invalid.'),
            ]);
        }

        // Log the user in
        Auth::login($user, session('login.remember', false));

        // Clear the session
        $request->session()->forget('login');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Verify the two-factor authentication recovery code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recover(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string',
        ]);

        $user = User::findOrFail(session('login.id'));
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        if (! in_array($request->recovery_code, $recoveryCodes)) {
            throw ValidationException::withMessages([
                'recovery_code' => __('The provided recovery code was invalid.'),
            ]);
        }

        // Remove the used recovery code
        $recoveryCodes = array_diff($recoveryCodes, [$request->recovery_code]);
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
        ])->save();

        // Log the user in
        Auth::login($user, session('login.remember', false));

        // Clear the session
        $request->session()->forget('login');

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
