@vite(['resources/css/app.css', 'resources/js/app.js'])
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login test Bonamiapp</title>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-4">Welkom bij Bonamiapp</h1>
        <p class="text-lg text-gray-600 mb-6">Dé plek voor sportcoaching, klantenbeheer en professionele begeleiding. Log in of registreer en ontdek alle mogelijkheden!</p>
        <div class="flex flex-col gap-4">
            <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg text-center cursor-pointer">Ga naar dashboard</a>
            <a href="{{ route('login') }}" class="border border-blue-600 text-blue-600 font-semibold py-3 px-6 rounded-lg text-center cursor-pointer">Log in</a>
        </div>
    </div>
</body>
</html>
