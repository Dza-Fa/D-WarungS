@extends('layouts.app')

@section('title', 'D-WarungS - Food Court Online Anda')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
        <div class="text-center">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-4 leading-tight">Selamat Datang di D-WarungS</h1>
            <p class="text-lg sm:text-xl md:text-2xl mb-8 opacity-90">Food court favorit Anda, sekarang online!</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <x-button 
                    size="lg" 
                    variant="white" 
                    onclick="document.getElementById('vendors').scrollIntoView({behavior: 'smooth'})"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                    Jelajahi Vendor
                </x-button>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6 hover:bg-gray-50 rounded-xl transition-colors duration-300">
                <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Banyak Vendor</h3>
                <p class="text-gray-600">Pilih dari berbagai vendor makanan semuanya di satu tempat</p>
            </div>
            <div class="text-center p-6 hover:bg-gray-50 rounded-xl transition-colors duration-300">
                <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Pesan Cepat</h3>
                <p class="text-gray-600">Proses pemesanan yang cepat dan mudah dengan konfirmasi instan</p>
            </div>
            <div class="text-center p-6 hover:bg-gray-50 rounded-xl transition-colors duration-300">
                <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Pembayaran Aman</h3>
                <p class="text-gray-600">Opsi pembayaran yang aman untuk ketenangan pikiran Anda</p>
            </div>
        </div>
    </div>
</div>

<!-- Vendors Section -->
<div id="vendors" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Vendor Kami</h2>
                <p class="text-gray-600 mt-1">Temukan makanan lezat dari vendor lokal</p>
            </div>
            <a href="{{ route('vendors.index') }}" class="text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1 transition-colors">
                Lihat Semua
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        @if($vendors->isEmpty())
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6m-6-6h-6m0 0H6"/>
                </svg>
                <p class="text-gray-500 text-lg mb-4">Tidak ada vendor yang tersedia saat ini.</p>
                <p class="text-gray-400">Silakan periksa kembali nanti untuk pembaruan.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($vendors as $vendor)
                    <x-card :clickable="true" class="overflow-hidden group" onclick="window.location='{{ route('vendors.show', $vendor) }}'">
                        <!-- Vendor Cover Image -->
                        <div class="h-40 bg-gradient-to-br from-orange-100 to-orange-200 relative overflow-hidden">
                            @if($vendor->cover_image)
                                <img src="{{ asset('storage/' . $vendor->cover_image) }}" alt="{{ $vendor->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="text-4xl font-bold text-orange-300">{{ substr($vendor->name, 0, 1) }}</span>
                                </div>
                            @endif
                            
                            <!-- Rating Badge -->
                            @if($vendor->rating)
                                <div class="absolute top-3 right-3 bg-white px-3 py-1.5 rounded-full flex items-center gap-1 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($vendor->rating, 1) }}</span>
                                </div>
                            @endif
                            
                            <!-- "Open Now" Badge -->
                            <div class="absolute top-3 left-3">
                                <x-badge variant="success" size="sm">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <circle cx="10" cy="10" r="8"/>
                                    </svg>
                                    Buka
                                </x-badge>
                            </div>
                        </div>
                        
                        <!-- Vendor Info -->
                        <div class="p-4">
                            <div class="flex items-start gap-3 mb-3">
                                @if($vendor->logo)
                                    <img src="{{ asset('storage/' . $vendor->logo) }}" alt="{{ $vendor->name }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
                                @else
                                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-lg font-bold text-orange-600">{{ substr($vendor->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 truncate">{{ $vendor->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $vendor->categories->count() }} kategori</p>
                                </div>
                            </div>
                            
                            @if($vendor->description)
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $vendor->description }}</p>
                            @endif
                            
                            <div class="space-y-2 text-xs text-gray-600">
                                @if($vendor->address)
                                    <p class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                        <span class="truncate">{{ $vendor->address }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- CTA Section -->
<div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Siap Memesan?</h2>
        <p class="text-lg sm:text-xl opacity-90 mb-8 max-w-2xl mx-auto">Jelajahi vendor kami dan temukan makanan favorit Anda hari ini!</p>
        <x-button 
            size="lg"
            variant="white" 
            onclick="window.location='{{ route('vendors.index') }}'"
        >
            Mulai Memesan
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </x-button>
    </div>
</div>
@endsection

