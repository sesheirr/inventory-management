<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CaptchaGenerator
{
    /**
     * Generate a random captcha code
     */
    public static function generate($length = 6)
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude similar looking characters
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Store in session
        Session::put('captcha_code', strtoupper($code));
        
        return $code;
    }

    /**
     * Generate captcha as SVG
     */
    public static function generateSVG($code = null, $width = 280, $height = 60)
    {
        if ($code === null) {
            $code = self::generate();
        }

        $svg = '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" xmlns="http://www.w3.org/2000/svg" style="width: 100% !important; height: 100% !important; display: block !important; border-radius: 8px !important; pointer-events: none !important;">';
        
        // Background - Light clean rect for maximum contrast in all themes
        $svg .= '<rect width="100%" height="100%" fill="#f8fafc" rx="8"/>';
        
        // Add noise lines
        for ($i = 0; $i < 6; $i++) {
            $x1 = random_int(0, $width);
            $y1 = random_int(0, $height);
            $x2 = random_int(0, $width);
            $y2 = random_int(0, $height);
            $color = 'rgb(' . random_int(180, 210) . ',' . random_int(190, 220) . ',' . random_int(220, 245) . ')';
            $svg .= '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="' . $color . '" stroke-width="2" opacity="0.7"/>';
        }
        
        // Add noise circles
        for ($i = 0; $i < 20; $i++) {
            $cx = random_int(0, $width);
            $cy = random_int(0, $height);
            $color = 'rgb(' . random_int(140, 180) . ',' . random_int(160, 200) . ',' . random_int(200, 240) . ')';
            $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . random_int(1, 3) . '" fill="' . $color . '" opacity="0.6"/>';
        }
        
        // Add text with distinct high-contrast bold dark colors
        $fontSize = 32;
        $charSpacing = ($width - 30) / strlen($code);
        $x = 15;
        $palette = ['#1e293b', '#0f172a', '#1e40af', '#1d4ed8', '#3730a3', '#0369a1', '#111827'];
        
        foreach (str_split($code) as $char) {
            $angle = random_int(-18, 18); // Random rotation
            $offsetY = random_int(-4, 4); // Random vertical offset
            $color = $palette[array_rand($palette)];
            
            $cx = $x + $charSpacing / 2;
            $cy = $height / 2 + 8 + $offsetY;

            $svg .= '<text ';
            $svg .= 'x="' . $cx . '" ';
            $svg .= 'y="' . $cy . '" ';
            $svg .= 'font-size="' . $fontSize . '" ';
            $svg .= 'font-weight="800" ';
            $svg .= 'font-family="Consolas, Monaco, monospace" ';
            $svg .= 'fill="' . $color . '" ';
            $svg .= 'text-anchor="middle" ';
            $svg .= 'transform="rotate(' . $angle . ' ' . $cx . ' ' . $cy . ')" ';
            $svg .= 'letter-spacing="2"';
            $svg .= '>' . htmlspecialchars($char) . '</text>';
            
            $x += $charSpacing;
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }

    /**
     * Verify captcha code
     */
    public static function verify($userInput)
    {
        $sessionCode = Session::get('captcha_code');
        if (!$sessionCode || !is_string($userInput)) {
            return false;
        }
        return strtoupper(trim($userInput)) === $sessionCode;
    }
}
