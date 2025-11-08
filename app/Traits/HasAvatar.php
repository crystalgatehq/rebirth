<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Trait for handling user avatars
 */
trait HasAvatar
{
    /**
     * Get the URL to the user's avatar.
     *
     * @param string|null $email
     * @param string|null $name
     * @param int $size
     * @param string $type
     * @return string
     */
    public function getAvatarUrl(
        ?string $email = null,
        ?string $name = null,
        int $size = 80,
        string $type = 'identicon' // 'identicon', 'monsterid', 'wavatar', 'retro', 'robohash', 'blank', 'mp', '404'
    ): string {
        // If email is not provided, try to get it from the model
        $email = $email ?? $this->email ?? '';
        
        // If name is not provided, try to get it from the model
        $name = $name ?? $this->name ?? '';
        
        // If we have an email, try to use Gravatar
        if ($email) {
            $hash = md5(strtolower(trim($email)));
            return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$type}";
        }
        
        // Fallback to initials avatar if no email
        return $this->getInitialsAvatar($name, $size);
    }
    
    /**
     * Generate an avatar with user's initials.
     *
     * @param string $name
     * @param int $size
     * @return string
     */
    protected function getInitialsAvatar(string $name, int $size = 80): string
    {
        // Get initials from name
        $initials = $this->getInitials($name);
        
        // Generate a consistent background color based on the name
        $bgColor = $this->stringToColor($name);
        $textColor = $this->getContrastColor($bgColor);
        
        // Create an SVG with the initials
        $svg = sprintf(
            '<svg width="%d" height="%d" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">' .
            '<rect width="200" height="200" fill="%s" />' .
            '<text x="50%%" y="50%%" font-size="80" fill="%s" text-anchor="middle" dy=".3em" font-family="Arial, sans-serif">%s</text>' .
            '</svg>',
            $size,
            $size,
            $bgColor,
            $textColor,
            $initials
        );
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Get initials from a name.
     *
     * @param string $name
     * @return string
     */
    protected function getInitials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        
        return count($words) === 1 ? strtoupper(substr($words[0], 0, 2)) : 'US';
    }
    
    /**
     * Generate a consistent color from a string.
     *
     * @param string $string
     * @return string
     */
    protected function stringToColor(string $string): string
    {
        // Generate a hash from the string
        $hash = md5($string);
        
        // Use the first 6 characters of the hash to create a hex color
        return '#' . substr($hash, 0, 6);
    }
    
    /**
     * Get a contrasting color (black or white) for a given hex color.
     *
     * @param string $hexColor
     * @return string
     */
    protected function getContrastColor(string $hexColor): string
    {
        // Remove the '#' if present
        $hexColor = ltrim($hexColor, '#');
        
        // Convert to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        // Calculate the relative luminance (perceived brightness)
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        // Return black or white depending on the background color
        return $luminance > 0.5 ? '#000000' : '#FFFFFF';
    }
}
