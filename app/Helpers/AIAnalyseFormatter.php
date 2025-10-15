<?php

namespace App\Helpers;

class AIAnalyseFormatter
{
    /**
     * Converteer AI analyse plain text naar mooie HTML output
     */
    public static function format(?string $text): string
    {
        if (empty($text)) {
            return '<p class="text-gray-500 italic">Geen AI analyse beschikbaar.</p>';
        }
        
        // Escape HTML voor veiligheid
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        // Split tekst in secties
        $lines = explode("\n", $text);
        $html = '';
        $inList = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }
                $html .= '<br>';
                continue;
            }
            
            // H1 headers (HOOFDLETTERS met emoji)
            if (preg_match('/^([🎯📊💪🏆🔍⚠️🚀✅❌]+\s*)?([A-Z\s]{3,}):?\s*$/', $line, $matches)) {
                if ($inList) $html .= '</ul>';
                $inList = false;
                $emoji = $matches[1] ?? '';
                $title = trim($matches[2]);
                $html .= "<h1>{$emoji} {$title}</h1>";
                continue;
            }
            
            // H2 headers (Emoji + Tekst)
            if (preg_match('/^([🎯📊💪🏆🔍⚠️🚀✅❌💤🏃]+)\s+([^:]+):\s*(.*)$/', $line, $matches)) {
                if ($inList) $html .= '</ul>';
                $inList = false;
                $html .= "<h2>{$matches[1]} {$matches[2]}</h2>";
                if (!empty($matches[3])) {
                    $html .= "<p>" . self::formatInline($matches[3]) . "</p>";
                }
                continue;
            }
            
            // Bullet points
            if (preg_match('/^([•\-\*]|\d+\.)\s+(.+)$/', $line, $matches)) {
                if (!$inList) {
                    $html .= '<ul class="space-y-2">';
                    $inList = true;
                }
                $html .= "<li>" . self::formatInline($matches[2]) . "</li>";
                continue;
            }
            
            // Normale paragraaf
            if ($inList) {
                $html .= '</ul>';
                $inList = false;
            }
            $html .= '<p>' . self::formatInline($line) . '</p>';
        }
        
        if ($inList) $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Formatteer inline tekst
     */
    private static function formatInline(string $text): string
    {
        // Vetgedrukte tekst
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        
        // Getallen met eenheden
        $text = preg_replace('/(\d+(?:\.\d+)?)\s*(W|Watt|bpm|km\/h|mmol\/L|%)/', '<strong class="text-blue-600">$1 $2</strong>', $text);
        
        // Positieve woorden (groen)
        $text = preg_replace('/\b(uitstekend|goed|zeer goed|optimaal|sterk)\b/i', '<span class="positive">$1</span>', $text);
        
        // Negatieve woorden (rood)
        $text = preg_replace('/\b(zwak|laag|let op|waarschuwing)\b/i', '<span class="negative">$1</span>', $text);
        
        // Highlights
        $text = preg_replace('/\b(LT1|LT2|VO2max|FTP|Zone \d)\b/', '<span class="highlight">$1</span>', $text);
        
        return $text;
    }
}
