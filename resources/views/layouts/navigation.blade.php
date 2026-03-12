<nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50" x-data="{ open: false }">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        <div class="bg-orange-500 text-white p-2 rounded-lg font-bold text-lg">D</div>
                        <span class="text-lg font-bold text-gray-900">D-WarungS</span>
                    </a>
                </div>

                <!-- Main Navigation Links -->
                <div class="hidden space-x-1 sm:ms-10 sm:flex sm:items-center">
                    <a href="{{ route('home') }}" @class([
                        'px-3 py-2 rounded-md text-sm font-medium transition-colors border-b-2',
                        'text-orange-600 bg-orange-50 border-orange-600' => request()->routeIs('home'),
                        'text-gray-700 border-transparent hover:text-orange-600 hover:border-orange-300' => !request()->routeIs('home'),
                    ])>
                        {{ __('Home') }}
                    </a>
                    <a href="{{ route('vendors.index') }}" @class([
                        'px-3 py-2 rounded-md text-sm font-medium transition-colors border-b-2',
                        'text-orange-600 bg-orange-50 border-orange-600' => request()->routeIs('vendors.*'),
                        'text-gray-700 border-transparent hover:text-orange-600 hover:border-orange-300' => !request()->routeIs('vendors.*'),
                    ])>
                        {{ __('Vendors') }}
                    </a>
                    
                    @auth
                        <a href="{{ route('orders.index') }}" @class([
                            'px-3 py-2 rounded-md text-sm font-medium transition-colors border-b-2',
                            'text-orange-600 bg-orange-50 border-orange-600' => request()->routeIs('orders.*'),
                            'text-gray-700 border-transparent hover:text-orange-600 hover:border-orange-300' => !request()->routeIs('orders.*'),
                        ])>
                            {{ __('My Orders') }}
                        </a>
                    @endauth
                </div>

            <!-- Right Side -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                <!-- Cart Link -->
                @php
                    $cartCount = session()->get('cart', []) ? array_sum(array_column(session()->get('cart', []), 'quantity')) : 0;
                @endphp
                <a href="{{ route('cart.index') }}" @class([
                    'relative inline-flex items-center px-3 py-2 rounded-md transition-colors border-b-2',
                    'text-orange-600 bg-orange-50 border-orange-600' => request()->routeIs('cart.*'),
                    'text-gray-700 border-transparent hover:text-orange-600 hover:border-orange-300' => !request()->routeIs('cart.*'),
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold h-5 w-5 rounded-full flex items-center justify-center">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                @auth
                    <!-- User Menu -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-orange-600 hover:border-orange-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <!-- Role-based Links -->
@if(Auth::user()->role === 'vendor' && Auth::user()->vendor)
                                <x-dropdown-link :href="route('vendor.dashboard.index')">
                                    {{ __('Vendor Dashboard') }}
                                </x-dropdown-link>
                            @endif
                            
                            @if(Auth::user()->role === 'cashier')
                                <x-dropdown-link :href="route('cashier.dashboard')">
                                    {{ __('Cashier Dashboard') }}
                                </x-dropdown-link>
                            @endif
                            
                            @if(Auth::user()->role === 'admin')
                                <x-dropdown-link :href="route('admin.dashboard')">
                                    {{ __('Admin Dashboard') }}
                                </x-dropdown-link>
                            @endif
                            
                            @if(Auth::user()->role === 'admin')
                                <x-dropdown-link :href="route('admin.dashboard')">
                                    {{ __('Admin Dashboard') }}
                                </x-dropdown-link>
                            @endif
                            
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-orange-600 px-3 py-2 font-medium">Log in</a>
                    <a href="{{ route('register') }}" class="bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-600 transition-colors font-medium">Sign up</a>
                @endauth
            </div>

            <!-- Mobile menu button & Cart -->
            <div class="flex sm:hidden items-center gap-2">
                <!-- Mobile Cart -->
                @php
                    $mobileCartCount = session()->get('cart', []) ? array_sum(array_column(session()->get('cart', []), 'quantity')) : 0;
                @endphp
                <a href="{{ route('cart.index') }}" class="relative inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-orange-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    @if($mobileCartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold h-4 w-4 rounded-full flex items-center justify-center">
                            {{ $mobileCartCount }}
                        </span>
                    @endif
                </a>

                <!-- Hamburger Menu Button -->
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-orange-600 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path x-show="!open" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div x-show="open" x-transition class="hidden sm:hidden bg-white border-t border-gray-100">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}" @class([
                'block px-3 py-2 rounded-md text-base font-medium transition-colors',
                'text-orange-600 bg-orange-50' => request()->routeIs('home'),
                'text-gray-700 hover:text-orange-600 hover:bg-gray-100' => !request()->routeIs('home'),
            ])>
                {{ __('Home') }}
            </a>
            <a href="{{ route('vendors.index') }}" @class([
                'block px-3 py-2 rounded-md text-base font-medium transition-colors',
                'text-orange-600 bg-orange-50' => request()->routeIs('vendors.*'),
                'text-gray-700 hover:text-orange-600 hover:bg-gray-100' => !request()->routeIs('vendors.*'),
            ])>
                {{ __('Vendors') }}
            </a>
            
            @auth
                <a href="{{ route('orders.index') }}" @class([
                    'block px-3 py-2 rounded-md text-base font-medium transition-colors',
                    'text-orange-600 bg-orange-50' => request()->routeIs('orders.*'),
                    'text-gray-700 hover:text-orange-600 hover:bg-gray-100' => !request()->routeIs('orders.*'),
                ])>
                    {{ __('My Orders') }}
                </a>
                
@if(Auth::user()->role === 'vendor' && Auth::user()->vendor)
                    <a href="{{ route('vendor.dashboard.index') }}" @class([
                        'block px-3 py-2 rounded-md text-base font-medium transition-colors',
                        'text-orange-600 bg-orange-50' => request()->routeIs('vendor.*'),
                        'text-gray-700 hover:text-orange-600 hover:bg-gray-100' => !request()->routeIs('vendor.*'),
                    ])>
                        {{ __('Vendor Dashboard') }}
                    </a>
                @endif
                
                @if(Auth::user()->role === 'cashier')
                    <a href="{{ route('cashier.dashboard') }}" @class([
                        'block px-3 py-2 rounded-md text-base font-medium transition-colors',
                        'text-orange-600 bg-orange-50' => request()->routeIs('cashier.*'),
                        'text-gray-700 hover:text-orange-600 hover:bg-gray-100' => !request()->routeIs('cashier.*'),
                    ])>
                        {{ __('Cashier Dashboard') }}
                    </a>
                @endif
                
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" @class([
                        'block px-3 py-2 rounded-md text-base font-medium transition-colors',
                        'text-orange-600 bg-orange-50' => request()->routeIs('admin.*'),
                        'text-gray-700 hover:text-orange-600 hover:bg-gray-100' => !request()->routeIs('admin.*'),
                    ])>
                        {{ __('Admin Dashboard') }}
                    </a>
                @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="px-4 mb-3">
                    <div class="font-medium text-base text-gray-900">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <div class="space-y-1 px-2">
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-orange-600 hover:bg-gray-100 transition-colors">
                        {{ __('Profile') }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
        @else
            <div class="pt-4 pb-3 border-t border-gray-200 px-2 space-y-1">
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                    {{ __('Log in') }}
                </a>
                <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium bg-orange-500 text-white hover:bg-orange-600 transition-colors">
                    {{ __('Sign up') }}
                </a>
            </div>
        @endauth
    </div>
</nav>
