<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Logs - Bonami Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }
        .header {
            background: #2d2d30;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #4ec9b0;
            font-size: 20px;
        }
        .info {
            color: #858585;
            font-size: 13px;
        }
        .log-container {
            background: #252526;
            border-radius: 8px;
            padding: 20px;
            overflow-x: auto;
            max-height: 80vh;
            overflow-y: auto;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
            font-size: 13px;
        }
        /* Syntax highlighting voor Laravel logs */
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; }
        .info { color: #4fc1ff; }
        .date { color: #858585; }
        .stack { color: #9cdcfe; }
        .file-path { color: #ce9178; text-decoration: underline; }
        
        .search-box {
            background: #3c3c3c;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .search-box input {
            width: 100%;
            padding: 8px 12px;
            background: #1e1e1e;
            border: 1px solid #555;
            color: #d4d4d4;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        .btn {
            background: #0e639c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover { background: #1177bb; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>🔍 Laravel Error Logs</h1>
            <div class="info">{{ $fullPath }}</div>
            <div class="info">Laatste wijziging: {{ $lastModified }}</div>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn">← Terug naar Dashboard</a>
        </div>
    </div>

    <div class="search-box">
        <input type="text" id="search" placeholder="Zoek in logs... (bijv. 'ERROR', 'Document niet gevonden', etc.)" onkeyup="filterLogs()">
    </div>

    <div class="log-container">
        <pre id="log-content">{{ $content }}</pre>
    </div>

    <script>
        // Syntax highlighting
        const logContent = document.getElementById('log-content');
        let originalContent = logContent.innerHTML;
        
        // Highlight errors, warnings, etc.
        originalContent = originalContent
            .replace(/\[ERROR\]/g, '<span class="error">[ERROR]</span>')
            .replace(/\[WARNING\]/g, '<span class="warning">[WARNING]</span>')
            .replace(/\[INFO\]/g, '<span class="info">[INFO]</span>')
            .replace(/\[\d{4}-\d{2}-\d{2}[^\]]+\]/g, '<span class="date">$&</span>')
            .replace(/(\/[\w\/\-\.]+\.php)/g, '<span class="file-path">$1</span>')
            .replace(/(#\d+ [^\n]+)/g, '<span class="stack">$1</span>');
        
        logContent.innerHTML = originalContent;
        
        // Search functionaliteit
        function filterLogs() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const lines = originalContent.split('\n');
            
            if (searchTerm === '') {
                logContent.innerHTML = originalContent;
                return;
            }
            
            const filtered = lines.filter(line => 
                line.toLowerCase().includes(searchTerm)
            ).join('\n');
            
            logContent.innerHTML = filtered || '<span style="color: #858585;">Geen resultaten gevonden...</span>';
        }
        
        // Auto-scroll naar onderkant (meest recente logs)
        window.addEventListener('load', function() {
            const container = document.querySelector('.log-container');
            container.scrollTop = container.scrollHeight;
        });
    </script>
</body>
</html>
