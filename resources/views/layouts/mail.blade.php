<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonami Sportcoaching</title>
    <style>
        body {
            background: #f3f6fa;
            font-family: Arial, sans-serif;
            color: #222;
            margin: 0;
            padding: 0;
        }
        .mail-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 2.5em 2em 2em 2em;
        }
        .mail-logo {
            display: block;
            margin: 32px auto 24px auto;
            width: 7em;
            height: auto;
        }
        .mail-footer {
            text-align: center;
            color: #888;
            font-size: 0.95em;
            margin-top: 2em;
        }
    </style>
</head>
<body>
    <img src="{{ $message->embed(public_path('logo_bonami_mail.png')) }}" alt="Bonami" class="mail-logo">
    <div class="mail-container">
        @yield('content')
    </div>
    <div class="mail-footer">
        © {{ date('Y') }} Bonami. All rights reserved.
    </div>
</body>
</html>
