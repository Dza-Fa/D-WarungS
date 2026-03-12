@props([
    'currentRoute' => '',
])

<!-- Bottom Navigation Bar (Mobile only) -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-40">
    <div class="flex items-stretch justify-around w-full">
        <!-- Home -->
        <a 
            href="{{ route('home') }}"
            @class([
                'flex-1 flex flex-col items-center justify-center py-3 px-2 transition-colors duration-200 hover:bg-gray-50 touch-target',
                'text-orange-600 border-t-2 border-orange-600' => $currentRoute === 'home',
                'text-gray-500 border-t-2 border-transparent' => $currentRoute !== 'home',
            ])
        >
            <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-3h2v3a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
            <span class="text-xs font-medium">Home</span>
        </a>
        
        <!-- Search -->
        <a 
            href="{{ route('vendors.index') }}"
            @class([
                'flex-1 flex flex-col items-center justify-center py-3 px-2 transition-colors duration-200 hover:bg-gray-50 touch-target',
                'text-orange-600 border-t-2 border-orange-600' => in_array($currentRoute, ['vendors.index', 'vendors.show']),
                'text-gray-500 border-t-2 border-transparent' => !in_array($currentRoute, ['vendors.index', 'vendors.show']),
            ])
        >
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-xs font-medium">Search</span>
        </a>
        
        <!-- Cart -->
        <a 
            href="{{ route('cart.index') }}"
            @class([
                'flex-1 flex flex-col items-center justify-center py-3 px-2 transition-colors duration-200 hover:bg-gray-50 relative touch-target',
                'text-orange-600 border-t-2 border-orange-600' => $currentRoute === 'cart.index',
                'text-gray-500 border-t-2 border-transparent' => $currentRoute !== 'cart.index',
            ])
        >
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-xs font-medium">Cart</span>
            <!-- Cart Count Badge -->
            @php
                $bottomCartCount = session()->get('cart', []) ? array_sum(array_column(session()->get('cart', []), 'quantity')) : 0;
            @endphp
            @if($bottomCartCount > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center" id="cart-count">
                    {{ $bottomCartCount }}
                </span>
            @endif
        </a>
        
        <!-- Orders -->
        @auth
            <a 
                href="{{ route('orders.index') }}"
                @class([
                    'flex-1 flex flex-col items-center justify-center py-3 px-2 transition-colors duration-200 hover:bg-gray-50 touch-target',
                    'text-orange-600 border-t-2 border-orange-600' => in_array($currentRoute, ['orders.index', 'orders.show']),
                    'text-gray-500 border-t-2 border-transparent' => !in_array($currentRoute, ['orders.index', 'orders.show']),
                ])
            >
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-xs font-medium">Orders</span>
            </a>
        @endauth
        
        <!-- Account -->
        <a 
            href="{{ auth()->check() ? route('profile.edit') : route('login') }}"
            @class([
                'flex-1 flex flex-col items-center justify-center py-3 px-2 transition-colors duration-200 hover:bg-gray-50 touch-target',
                'text-orange-600 border-t-2 border-orange-600' => $currentRoute === 'profile.edit',
                'text-gray-500 border-t-2 border-transparent' => $currentRoute !== 'profile.edit',
            ])
        >
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="text-xs font-medium">Account</span>
        </a>
    </div>
</nav>

<!-- Padding to avoid content overlap -->
<div class="h-20 md:h-0"></div>

<style>
    .touch-target {
        min-height: 44px;
        min-width: 44px;
    }
</style>
