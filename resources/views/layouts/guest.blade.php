<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-orange-50 to-orange-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- Logo -->
            <div class="flex items-center gap-3 mb-8">
                <a href="/" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                    <div class="bg-orange-500 text-white p-3 rounded-lg font-bold text-2xl">D</div>
                    <span class="text-2xl font-bold text-gray-900">D-WarungS</span>
                </a>
            </div>

            <!-- Form Container -->
            <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-lg rounded-lg border border-gray-100">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
