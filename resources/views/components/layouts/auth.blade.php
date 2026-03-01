<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — Acceso</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
</body>
</html>
