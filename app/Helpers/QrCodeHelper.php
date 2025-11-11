<?php

namespace App\Helpers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeHelper
{
    /**
     * Genereer QR code HTML
     */
    public static function generate($url, $size = 150)
    {
        try {
            if (!$url) {
                return '';
            }

            // Genereer SVG QR code
            $qrCode = QrCode::size($size)
                ->margin(0)
                ->generate($url);

            return '<div class="qr-code-container" style="text-align: center; margin: 20px 0;">
                        ' . $qrCode . '
                    </div>';
        } catch (\Exception $e) {
            \Log::error('QR Code generatie gefaald: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Genereer QR code met tekst
     */
    public static function generateWithText($url, $text, $size = 150)
    {
        try {
            $qrHtml = self::generate($url, $size);
            
            if (!$qrHtml) {
                return '';
            }

            return '<div class="qr-code-with-text" style="text-align: center; margin: 20px 0;">
                        ' . $qrHtml . '
                        <p style="margin-top: 10px; font-size: 12px; color: #666;">' . e($text) . '</p>
                    </div>';
        } catch (\Exception $e) {
            \Log::error('QR Code met tekst generatie gefaald: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Check of QR code package geïnstalleerd is
     */
    public static function isAvailable()
    {
        return class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode');
    }
}
