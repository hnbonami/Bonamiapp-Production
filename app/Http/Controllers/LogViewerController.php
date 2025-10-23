<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    public function __construct()
    {
        // Alleen voor ingelogde staff/admin gebruikers
        $this->middleware('auth');
    }

    /**
     * Toon de log viewer interface
     */
    public function index()
    {
        $logFiles = $this->getLogFiles();
        $selectedLog = request('file', 'laravel.log');
        $lines = request('lines', 100); // Aantal regels om te tonen
        
        $logContent = $this->getLogContent($selectedLog, $lines);
        
        return view('logs.index', compact('logFiles', 'selectedLog', 'logContent', 'lines'));
    }

    /**
     * Haal alle beschikbare log bestanden op
     */
    private function getLogFiles()
    {
        $logPath = storage_path('logs');
        
        if (!File::exists($logPath)) {
            return [];
        }
        
        $files = File::files($logPath);
        
        return collect($files)
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $this->formatBytes($file->getSize()),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime())
                ];
            })
            ->sortByDesc('modified')
            ->values()
            ->toArray();
    }

    /**
     * Lees de inhoud van een log bestand
     */
    private function getLogContent($filename, $lines = 100)
    {
        $logPath = storage_path('logs/' . $filename);
        
        if (!File::exists($logPath)) {
            return "Log bestand niet gevonden.";
        }
        
        // Lees laatste X regels van het bestand
        $file = new \SplFileObject($logPath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        
        $startLine = max(0, $lastLine - $lines);
        
        $logLines = [];
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $line = $file->current();
            if ($line !== false) {
                $logLines[] = $this->formatLogLine($line);
            }
            $file->next();
        }
        
        return array_reverse($logLines);
    }

    /**
     * Formatteer een log regel met syntax highlighting
     */
    private function formatLogLine($line)
    {
        $line = htmlspecialchars($line);
        
        // Kleur codes voor verschillende log levels
        if (strpos($line, 'ERROR') !== false) {
            $class = 'text-red-600 font-semibold';
        } elseif (strpos($line, 'WARNING') !== false) {
            $class = 'text-yellow-600 font-semibold';
        } elseif (strpos($line, 'INFO') !== false) {
            $class = 'text-blue-600';
        } elseif (strpos($line, 'DEBUG') !== false) {
            $class = 'text-gray-500';
        } else {
            $class = 'text-gray-700';
        }
        
        return [
            'content' => $line,
            'class' => $class
        ];
    }

    /**
     * Download een specifiek log bestand
     */
    public function download($filename)
    {
        $logPath = storage_path('logs/' . $filename);
        
        if (!File::exists($logPath)) {
            abort(404, 'Log bestand niet gevonden');
        }
        
        return response()->download($logPath);
    }

    /**
     * Verwijder een log bestand
     */
    public function delete($filename)
    {
        $logPath = storage_path('logs/' . $filename);
        
        if (File::exists($logPath) && $filename !== 'laravel.log') {
            File::delete($logPath);
            return redirect()->route('logs.index')->with('success', 'Log bestand verwijderd');
        }
        
        return redirect()->route('logs.index')->with('error', 'Kan log bestand niet verwijderen');
    }

    /**
     * Formatteer bytes naar leesbare grootte
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
