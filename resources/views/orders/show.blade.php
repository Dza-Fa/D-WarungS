@extends('layouts.app')

@section('title', 'Detail Pesanan - D-WarungS')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Pesanan #{{ $order->order_number }}</h1>
                <p class="mt-2 text-gray-600">Dipesan pada {{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            <a href="{{ route('orders.index') }}" class="text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Pesanan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content - Order Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Item Pesanan</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($order->orderItems as $item)
                        <div class="px-6 py-4 flex items-center gap-4">
                            <!-- Product Image -->
                            <div class="flex-shrink-0">
                                @if($item->product?->image)
                                    <img src="{{ asset('storage/' . $item->product?->image) }}" 
                                         alt="{{ $item->product?->name }}" 
                                         class="w-16 h-16 rounded-lg object-cover">
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <!-- Product Info -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 truncate">{{ $item->product?->name ?? 'Produk #' . $item->product_id }}</h3>
                                <p class="text-sm text-gray-500">Jml: {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                            <!-- Subtotal -->
                            <div class="text-right">
                                <p class="font-medium text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- Order Summary -->
                <div class="px-6 py-4 bg-gray-50">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Pajak (10%)</span>
                        <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                    </div>
                    @if($order->discount > 0)
                    <div class="flex justify-between text-sm text-green-600 mb-2">
                        <span>Diskon</span>
                        <span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-200">
                        <span>Total</span>
                        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Detail Pembayaran</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Metode Pembayaran</p>
                            <p class="font-medium text-gray-900 capitalize">{{ $order->payment_method ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">ID Transaksi</p>
                            <p class="font-medium text-gray-900">{{ $order->transaction_id ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Status & Vendor Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Order Status Card -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Pesanan</h2>
                
                <!-- Status Badge -->
                <div class="mb-6">
                    @switch($order->status)
                        @case('pending')
                            <span class="px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Menunggu</span>
                            @break
                        @case('confirmed')
                            <span class="px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">Dikonfirmasi</span>
                            @break
                        @case('processing')
                            <span class="px-4 py-2 rounded-full text-sm font-medium bg-purple-100 text-purple-800">Diproses</span>
                            @break
                        @case('ready')
                            <span class="px-4 py-2 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">Siap</span>
                            @break
                        @case('completed')
                            <span class="px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">Selesai</span>
                            @break
                        @case('cancelled')
                            <span class="px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">Dibatalkan</span>
                            @break
                        @default
                            <span class="px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800">{{ ucfirst($order->status) }}</span>
                    @endswitch
                </div>

                <!-- Payment Status -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Status Pembayaran</h3>
                    @switch($order->payment_status)
                        @case('pending')
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Menunggu</span>
                            @break
                        @case('paid')
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Lunas</span>
                            @break
                        @case('failed')
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Gagal</span>
                            @break
                        @case('refunded')
                            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">Dikembalikan</span>
                            @break
                    @endswitch
                </div>

                <!-- Vendor Info -->
                <div class="mb-6 border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Vendor</h3>
                    <p class="font-semibold text-gray-900">{{ $order->vendor->name ?? 'N/A' }}</p>
                    @if($order->vendor)
                        <div class="mt-2 text-sm text-gray-600">
                            @if($order->vendor->address)
                                <p class="flex items-start gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>{{ $order->vendor->address }}</span>
                                </p>
                            @endif
                            @if($order->vendor->phone)
                                <p class="flex items-center gap-2 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span>{{ $order->vendor->phone }}</span>
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="border-t pt-4 space-y-3">
                    @if(in_array($order->status, ['pending', 'confirmed']))
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 font-medium transition-colors">
                                Batalkan Pesanan
                            </button>
                        </form>
                    @endif
                    
                    @if($order->status === 'completed' && $order->vendor)
                        <a href="{{ route('vendors.show', $order->vendor) }}" class="block w-full text-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition-colors">
                            Pesan Lagi
                        </a>
                    @endif
                    
                    @if($order->vendor)
                        <a href="{{ route('vendors.show', $order->vendor) }}" class="block w-full text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                            Hubungi Vendor
                        </a>
                    @endif
                </div>
            </div>

            <!-- Order Timeline -->
            @if(in_array($order->status, ['processing', 'ready', 'completed']))
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Timeline Pesanan</h3>
                    <div class="relative">
                        <!-- Timeline Line -->
                        <div class="absolute left-1.5 top-2 bottom-2 w-0.5 bg-gray-200"></div>
                        
                        <div class="space-y-4">
                            <!-- Order Placed -->
                            <div class="relative flex items-start">
                                <div class="w-3 h-3 rounded-full bg-green-500 border-2 border-white ring-2 ring-green-500 relative z-10"></div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Pesanan Dibuat</p>
                                    <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            
                            <!-- Order Confirmed -->
                            @if($order->status !== 'pending')
                            <div class="relative flex items-start">
                                <div class="w-3 h-3 rounded-full {{ in_array($order->status, ['confirmed', 'processing', 'ready', 'completed']) ? 'bg-green-500' : 'bg-gray-300' }} border-2 border-white ring-2 {{ in_array($order->status, ['confirmed', 'processing', 'ready', 'completed']) ? 'ring-green-500' : 'ring-gray-300' }} relative z-10"></div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Pesanan Dikonfirmasi</p>
                                    @if($order->confirmed_at)
                                        <p class="text-xs text-gray-500">{{ $order->confirmed_at->format('d M Y, H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <!-- Ready for Pickup -->
                            @if(in_array($order->status, ['ready', 'completed']))
                            <div class="relative flex items-start">
                                <div class="w-3 h-3 rounded-full bg-green-500 border-2 border-white ring-2 ring-green-500 relative z-10"></div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Siap Diambil</p>
                                    @if($order->ready_at)
                                        <p class="text-xs text-gray-500">{{ $order->ready_at->format('d M Y, H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            <!-- Completed -->
                            @if($order->status === 'completed')
                            <div class="relative flex items-start">
                                <div class="w-3 h-3 rounded-full bg-green-500 border-2 border-white ring-2 ring-green-500 relative z-10"></div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Pesanan Selesai</p>
                                    @if($order->completed_at)
                                        <p class="text-xs text-gray-500">{{ $order->completed_at->format('d M Y, H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        <!-- Pickup Action - Show when order is ready or processing and paid -->
        @if(in_array($order->status, ['ready', 'processing']) && $order->payment_status === 'paid')
            <div class="border-t pt-4">
                <p class="text-sm text-gray-600 mb-3">Pesanan Anda siap untuk diambil!</p>
                <form action="{{ route('orders.pickup', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                        Konfirmasi Pengambilan
                    </button>
                </form>
            </div>
        @endif

        <!-- Order Completed Message -->
        @if($order->status === 'completed')
            <div class="border-t pt-4">
                <div class="bg-green-50 text-green-800 p-4 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="font-medium">Pesanan Selesai</p>
                    <p class="text-sm">Terima kasih atas pembelian Anda!</p>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

