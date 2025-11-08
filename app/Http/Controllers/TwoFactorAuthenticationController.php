<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Show the two-factor authentication settings page.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user();
        
        // If 2FA is not set up yet, generate a new secret
        if (!$user->two_factor_secret) {
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();
            
            // Store the secret in the session to use after form submission
            session(['two_factor_secret' => $secret]);
            
            return view('profile.two-factor-authentication-form', [
                'user' => $user,
                'qrCode' => $user->twoFactorQrCodeSvg(),
                'secret' => $secret
            ]);
        }
        
        // If 2FA is already set up, show the current status
        return view('profile.two-factor-authentication-form', [
            'user' => $user,
            'qrCode' => $user->twoFactorQrCodeSvg(),
            'recoveryCodes' => $user->getRecoveryCodes()
        ]);
    }

    /**
     * Enable two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // If 2FA is already enabled, just show the current status
        if ($user->two_factor_secret) {
            return back()->with('status', 'two-factor-authentication-already-enabled');
        }

        // Generate a new two-factor authentication secret
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        
        // Generate recovery codes
        $recoveryCodes = collect(
            array_map(function () {
                return Str::random(10).'-'.Str::random(10);
            }, range(1, 8))
        )->toArray();

        // Store the secret and recovery codes
        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode([
                'recovery_codes' => $recoveryCodes,
            ])),
        ])->save();

        return back()->with([
            'status' => 'two-factor-authentication-enabled',
            'recovery_codes' => $recoveryCodes,
            'two_factor_secret' => $secret // Pass the unencrypted secret to the view
        ]);
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        if (!Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ])->errorBag('disableTwoFactor');
        }

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', 'two-factor-authentication-disabled');
    }

    /**
     * Confirm the two-factor authentication setup.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);

        $valid = $google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code was invalid.')],
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with('status', 'two-factor-authentication-confirmed');
    }

    /**
     * Show the user's recovery codes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function showRecoveryCodes(Request $request)
    {
        $user = $request->user();
        
        if (!$user->two_factor_secret) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Two-factor authentication is not enabled.',
                    'needs_regeneration' => false
                ], 404);
            }
            return back()->with('status', 'two-factor-not-enabled');
        }
        
        $recoveryCodes = $this->getRecoveryCodes($user);
        
        if (empty($recoveryCodes)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No recovery codes found. Please regenerate your recovery codes.',
                    'needs_regeneration' => true
                ], 404);
            }
            return redirect()->route('profile.two-factor.recovery-codes.confirm');
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'recovery_codes' => $recoveryCodes
            ]);
        }
        
        return view('profile.two-factor-recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
            'showConfirmation' => false
        ]);
    }

    /**
     * Show the confirmation page for regenerating recovery codes.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function showRegenerateRecoveryCodes()
    {
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Please confirm to regenerate recovery codes.'
            ]);
        }
        
        return view('profile.two-factor-recovery-codes', [
            'user' => Auth::user(),
            'showConfirmation' => true
        ]);
    }

    /**
     * Regenerate the recovery codes for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        
        $user = $request->user();
        
        // Generate new recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();
        
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode([
                'recovery_codes' => $recoveryCodes,
            ])),
        ])->save();
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Recovery codes regenerated successfully.',
                'recovery_codes' => $recoveryCodes
            ]);
        }
        
        return back()->with([
            'status' => 'recovery-codes-regenerated',
            'recovery_codes' => $recoveryCodes
        ]);
    }

    /**
     * Generate new recovery codes.
     *
     * @return array
     */
    private function generateRecoveryCodes()
    {
        return collect(
            array_map(function () {
                return Str::random(10).'-'.Str::random(10);
            }, range(1, 8))
        )->toArray();
    }

    /**
     * Get the current recovery codes for the user.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    private function getRecoveryCodes($user)
    {
        if (!$user->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($user->two_factor_recovery_codes), true)['recovery_codes'] ?? [];
    }
}
