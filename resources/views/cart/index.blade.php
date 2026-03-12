@extends('layouts.app')

@section('title', 'Keranjang Belanja - D-WarungS')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Keranjang Belanja</h1>
        <p class="mt-2 text-gray-600">Periksa barang Anda sebelum checkout</p>
    </div>

    @php
        $cart = session()->get('cart', []);
        $cartTotal = 0;
        foreach ($cart as $item) {
            $cartTotal += $item['price'] * $item['quantity'];
        }
    @endphp

    @if(empty($cart))
        <div class="text-center py-16 bg-white rounded-xl shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h2 class="text-2xl font-semibold text-gray-700 mb-2">Keranjang Anda kosong</h2>
            <p class="text-gray-500 mb-6">Sepertinya Anda belum menambahkan barang ke keranjang.</p>
            <a href="{{ route('vendors.index') }}" class="inline-block bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                Lihat Vendor
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        @foreach($cart as $id => $item)
                            <div class="p-6 flex items-center gap-4">
                                <!-- Product Image -->
                                <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    @if(isset($item['image']) && $item['image'])
                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900">{{ $item['name'] }}</h3>
                                    <p class="text-orange-600 font-bold">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                </div>
                                
                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('cart.update') }}" method="POST" class="flex items-center">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $id }}">
                                        <button type="button" onclick="this.parentNode.querySelector('input[name=quantity]').stepDown(); this.parentNode.submit()" class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-l-lg hover:bg-gray-100">
                                            -
                                        </button>
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="w-12 h-8 text-center border-t border-b border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 no-spinners">
                                        <button type="button" onclick="this.parentNode.querySelector('input[name=quantity]').stepUp(); this.parentNode.submit()" class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-r-lg hover:bg-gray-100">
                                            +
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Item Total -->
                                <div class="text-right min-w-[80px]">
                                    <p class="font-semibold text-gray-900">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</p>
                                </div>
                                
                                <!-- Remove Button -->
                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $id }}">
                                    <button type="submit" class="text-red-500 hover:text-red-700 p-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Ringkasan Pesanan</h2>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal ({{ count($cart) }} barang)</span>
                            <span>Rp {{ number_format($cartTotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Pajak (10%)</span>
                            <span>Rp {{ number_format($cartTotal * 0.1, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t pt-4 flex justify-between text-lg font-bold text-gray-900">
                            <span>Total</span>
                            <span>Rp {{ number_format($cartTotal * 1.1, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    @auth
                        <a href="{{ route('checkout.index') }}" class="block w-full bg-orange-500 hover:bg-orange-600 text-white text-center py-3 rounded-lg font-semibold transition-colors">
                            Lanjut ke Checkout
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="block w-full bg-orange-500 hover:bg-orange-600 text-white text-center py-3 rounded-lg font-semibold transition-colors">
                            Login untuk Checkout
                        </a>
                        <p class="text-center text-sm text-gray-500 mt-3">
                            Belum punya akun? 
                            <a href="{{ route('register') }}" class="text-orange-600 hover:underline">Daftar di sini</a>
                        </p>
                    @endauth
                    
                    <a href="{{ route('vendors.index') }}" class="block text-center text-gray-600 hover:text-orange-600 mt-4 text-sm">
                        &larr; Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

