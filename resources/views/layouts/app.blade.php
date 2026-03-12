<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'D-WarungS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-sm">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Flash Messages as Alerts (at top of content) -->
            @if(session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
                    <x-alert type="success" title="Success">
                        {{ session('success') }}
                    </x-alert>
                </div>
            @endif

            @if(session('warning'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
                    <x-alert type="warning" title="Warning">
                        {{ session('warning') }}
                    </x-alert>
                </div>
            @endif

            @if(session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
                    <x-alert type="danger" title="Error">
                        {{ session('error') }}
                    </x-alert>
                </div>
            @endif

            <!-- Page Content -->
            <main class="flex-1">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 mt-12">
                <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4">About D-WarungS</h3>
                            <p class="text-sm text-gray-600">Your one-stop platform for ordering food from multiple vendors in your area.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4">Quick Links</h3>
                            <ul class="space-y-2 text-sm">
                                <li><a href="#" class="text-gray-600 hover:text-orange-600 transition-colors">About Us</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-orange-600 transition-colors">Contact</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-orange-600 transition-colors">FAQ</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4">Legal</h3>
                            <ul class="space-y-2 text-sm">
                                <li><a href="#" class="text-gray-600 hover:text-orange-600 transition-colors">Privacy Policy</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-orange-600 transition-colors">Terms of Service</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-8 text-center text-gray-500 text-sm">
                        <p>&copy; {{ date('Y') }} D-WarungS. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Mobile Bottom Navigation -->
        <x-bottom-nav :currentRoute="request()->route()->getName()" />

        <!-- Toast Notification Container -->
        <x-toast-container />

        <!-- Inline script for session toasts -->
        @if(session('success') || session('error') || session('warning'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Auto-dismiss alerts after 4 seconds
                    const alerts = document.querySelectorAll('[role="alert"]');
                    alerts.forEach(alert => {
                        const dismissBtn = alert.querySelector('button');
                        if (dismissBtn) {
                            setTimeout(() => {
                                dismissBtn.click();
                            }, 4000);
                        }
                    });
                });
            </script>
        @endif
    </body>
</html>

