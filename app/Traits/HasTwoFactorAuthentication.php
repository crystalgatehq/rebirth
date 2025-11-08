<?php

namespace App\Traits;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

/**
 * Provides two-factor authentication functionality for the User model.
 */
trait HasTwoFactorAuthentication
{
    /**
     * Get the user's two-factor authentication recovery codes.
     *
     * @return array
     */
    public function getRecoveryCodes()
    {
        if (is_null($this->two_factor_recovery_codes)) {
            return [];
        }

        try {
            $decrypted = decrypt($this->two_factor_recovery_codes);
            $decoded = json_decode($decrypted, true);
            
            // Ensure we return an array, even if json_decode fails
            return is_array($decoded) ? $decoded : [];
        } catch (\Exception $e) {
            // If decryption or JSON decoding fails, return empty array
            return [];
        }
    }

    /**
     * Generate a new set of recovery codes.
     *
     * @return array
     */
    public function generateRecoveryCodes()
    {
        $codes = collect(
            array_map(
                fn () => implode('-', str_split(random_int(100000, 999999).bin2hex(random_bytes(5)), 5)),
                range(1, 8)
            )
        )->toArray();

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();

        return $codes;
    }

    /**
     * Get the QR code SVG of the user's two-factor authentication QR code URL.
     *
     * @return string
     */
    public function twoFactorQrCodeSvg()
    {
        $google2fa = app(Google2FA::class);
        
        $secret = decrypt($this->two_factor_secret);
        
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $secret
        );
        
        // Generate a seed based on the current time (changes every 30 seconds)
        $seed = floor(time() / 30);
        
        // Define color palettes
        $palettes = [
            // Vibrant
            ['foreground' => [41, 128, 185], 'background' => [236, 240, 241]],
            // Dark theme
            ['foreground' => [52, 152, 219], 'background' => [44, 62, 80]],
            // Warm
            ['foreground' => [230, 126, 34], 'background' => [253, 227, 167]],
            // Cool
            ['foreground' => [155, 89, 182], 'background' => [236, 240, 241]],
            // High contrast
            ['foreground' => [0, 0, 0], 'background' => [255, 255, 255]],
            // Inverted
            ['foreground' => [255, 255, 255], 'background' => [0, 0, 0]]
        ];
        
        // Select a color palette based on the seed
        $palette = $palettes[$seed % count($palettes)];
        
        // Define dot styles as strings (the package doesn't have these as constants)
        $dotStyles = ['square', 'rounded', 'dot'];
        
        // Define error correction levels (L = ~7%, M = ~15%, Q = ~25%, H = ~30%)
        $errorLevels = [
            \BaconQrCode\Common\ErrorCorrectionLevel::L(),
            \BaconQrCode\Common\ErrorCorrectionLevel::M(),
            \BaconQrCode\Common\ErrorCorrectionLevel::Q(),
            \BaconQrCode\Common\ErrorCorrectionLevel::H()
        ];
        
        // Select styles based on the seed
        $dotStyle = $dotStyles[($seed + 1) % count($dotStyles)];
        $errorLevel = $errorLevels[($seed + 2) % count($errorLevels)];
        
        // Generate a slight variation in the foreground color
        $hue = ($seed * 13) % 360; // Rotate through the color wheel
        $saturation = 70 + (($seed * 7) % 30); // 70-100%
        $lightness = 40 + (($seed * 11) % 20); // 40-60%
        
        // Convert HSL to RGB for the dynamic color
        $dynamicColor = $this->hslToRgb($hue / 360, $saturation / 100, $lightness / 100);
        
        // Create a renderer with dynamic styles
        $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd(
            null, // Width
            null, // Height
            'square', // Shape (square or circle)
            $errorLevel,
            4, // Margin (in modules)
            $dynamicColor, // Dynamic foreground color
            $palette['background'] // Background from palette
        );
        
        // Create a custom renderer style based on the dot style
        $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(
            256, // Size (will be scaled to 256x256 by the backend)
            2,   // Margin (in modules)
            null, // Module size (null for auto)
            null  // Quiet zone (null for auto)
        );
        
        // Create the writer with the custom renderer
        $writer = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $renderer)
        );
        
        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Enable two-factor authentication for the user.
     *
     * @param  string  $secret
     * @return void
     */
    public function enableTwoFactorAuthentication($secret)
    {
        $this->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->generateRecoveryCodes();
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @return void
     */
    public function disableTwoFactorAuthentication()
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    /**
     * Determine if two-factor authentication has been enabled.
     *
     * @return bool
     */
    public function hasEnabledTwoFactorAuthentication()
    {
        return ! is_null($this->two_factor_secret);
    }
    
    /**
     * Convert HSL color to RGB
     *
     * @param float $h Hue (0-1)
     * @param float $s Saturation (0-1)
     * @param float $l Lightness (0-1)
     * @return array RGB array [r, g, b]
     */
    private function hslToRgb($h, $s, $l)
    {
        $r = $l;
        $g = $l;
        $b = $l;
        $v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);
        
        if ($v > 0) {
            $m = $l + $l - $v;
            $sv = ($v - $m) / $v;
            $h *= 6.0;
            $sextant = floor($h);
            $fract = $h - $sextant;
            $vsf = $v * $sv * $fract;
            $mid1 = $m + $vsf;
            $mid2 = $v - $vsf;
            
            switch ($sextant) {
                case 0:
                    $r = $v;
                    $g = $mid1;
                    $b = $m;
                    break;
                case 1:
                    $r = $mid2;
                    $g = $v;
                    $b = $m;
                    break;
                case 2:
                    $r = $m;
                    $g = $v;
                    $b = $mid1;
                    break;
                case 3:
                    $r = $m;
                    $g = $mid2;
                    $b = $v;
                    break;
                case 4:
                    $r = $mid1;
                    $g = $m;
                    $b = $v;
                    break;
                default:
                    $r = $v;
                    $g = $m;
                    $b = $mid2;
                    break;
            }
        }
        
        return [
            round($r * 255),
            round($g * 255),
            round($b * 255)
        ];
    }
}
