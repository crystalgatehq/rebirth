<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Show the two-factor authentication setup page.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $user = request()->user();
        
        // Generate QR code URL
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $writer = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(192),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            )
        );
        
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('profile.two-factor-authentication-form', [
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
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
        $request->validate([
            'secret' => ['required', 'string'],
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyKey(
            $request->secret,
            $request->code,
            config('google2fa.window', 4)
        );

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code was invalid.')],
            ]);
        }

        $user = $request->user();
        $user->enableTwoFactorAuthentication($request->secret);

        return back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ], [], [
            'password' => __('password'),
        ]);

        $request->user()->disableTwoFactorAuthentication();

        return back()->with('status', 'two-factor-authentication-disabled');
    }
    
    /**
     * Regenerate two-factor authentication recovery codes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerate(Request $request)
    {
        $request->user()->generateRecoveryCodes();
        
        return back()->with('status', 'recovery-codes-generated');
    }
    
    /**
     * Generate a new two-factor authentication secret and QR code.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateSecret(Request $request)
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $user = $request->user();
        
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
        
        $writer = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(192),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            )
        );
        
        $qrCodeSvg = $writer->writeString($qrCodeUrl);
        
        return response()->json([
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }
}
