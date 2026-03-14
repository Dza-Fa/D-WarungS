<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="D-WarungS — Platform food court online Anda. Pesan makanan dari berbagai vendor lokal dengan mudah dan cepat.">
        <meta property="og:title" content="@yield('title', config('app.name', 'D-WarungS'))">
        <meta property="og:description" content="Food court favorit Anda, sekarang online! Temukan berbagai pilihan makanan dari vendor lokal.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">

        <title>@yield('title', config('app.name', 'D-WarungS'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        {{-- Fix #18: Skip-to-content for keyboard users --}}
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[9999] focus:bg-orange-600 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg focus:font-medium">
            Langsung ke konten utama
        </a>

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
            <main id="main-content" class="flex-1">
                @yield('content')
            </main>

            <!-- Footer — Fix #11: Indonesian headings, Fix #10: removed dead # links, Fix #20: removed mt-12 -->
            <footer class="bg-white border-t border-gray-200">
                <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4">Tentang D-WarungS</h3>
                            <p class="text-sm text-gray-600">Platform terpadu Anda untuk memesan makanan dari berbagai vendor lokal di sekitar Anda.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4">Tautan Cepat</h3>
                            <ul class="space-y-2 text-sm">
                                <li><a href="{{ route('vendors.index') }}" class="text-gray-600 hover:text-orange-600 transition-colors">Semua Vendor</a></li>
                                <li><span class="text-gray-400 cursor-default">Tentang Kami <span class="text-xs">(segera hadir)</span></span></li>
                                <li><span class="text-gray-400 cursor-default">Hubungi Kami <span class="text-xs">(segera hadir)</span></span></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4">Hukum &amp; Privasi</h3>
                            <ul class="space-y-2 text-sm">
                                <li><span class="text-gray-400 cursor-default">Kebijakan Privasi <span class="text-xs">(segera hadir)</span></span></li>
                                <li><span class="text-gray-400 cursor-default">Ketentuan Layanan <span class="text-xs">(segera hadir)</span></span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-8 text-center text-gray-500 text-sm">
                        <p>&copy; {{ date('Y') }} D-WarungS. Hak cipta dilindungi.</p>
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

