@extends('layouts.app')

@section('title', 'Semua Vendor - D-WarungS')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Semua Vendor</h1>
        <p class="mt-2 text-gray-600">Jelajahi semua vendor makanan yang tersedia</p>
    </div>

    @if($vendors->isEmpty())
        <div class="text-center py-12 bg-white rounded-xl shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <p class="text-gray-500 text-lg">Tidak ada vendor yang tersedia saat ini.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block text-orange-600 hover:text-orange-700 font-medium">
                Kembali ke halaman utama
            </a>
        </div>
    @else
        <!-- Vendors Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($vendors as $vendor)
                <a href="{{ route('vendors.show', $vendor) }}" class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden group">
                    <!-- Vendor Cover Image -->
                    <div class="h-36 bg-gradient-to-br from-orange-100 to-orange-200 relative overflow-hidden">
                        @if($vendor->cover_image)
                            <img src="{{ asset('storage/' . $vendor->cover_image) }}" alt="{{ $vendor->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="text-5xl font-bold text-orange-300">{{ substr($vendor->name, 0, 1) }}</span>
                            </div>
                        @endif
                        
                        <!-- Status Badge -->
                        @if($vendor->status === 'active')
                            <span class="absolute top-3 left-3 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                Buka
                            </span>
                        @endif
                        
                        <!-- Rating Badge -->
                        @if($vendor->rating)
                            <div class="absolute top-3 right-3 bg-white px-2 py-1 rounded-full flex items-center gap-1 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="text-sm font-medium">{{ number_format($vendor->rating, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Vendor Info -->
                    <div class="p-4">
                        <div class="flex items-center gap-3 mb-3">
                            @if($vendor->logo)
                                <img src="{{ asset('storage/' . $vendor->logo) }}" alt="{{ $vendor->name }}" class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <span class="text-xl font-bold text-orange-600">{{ substr($vendor->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $vendor->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $vendor->categories->count() }} kategori</p>
                            </div>
                        </div>
                        
                        @if($vendor->description)
                            <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $vendor->description }}</p>
                        @endif
                        
                        <div class="flex items-center justify-between text-sm">
                            @if($vendor->address)
                                <span class="text-gray-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="truncate max-w-[120px]">{{ $vendor->address }}</span>
                                </span>
                            @endif
                            
                            <span class="text-orange-600 font-medium group-hover:underline">
                                Lihat Menu &rarr;
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($vendors->hasPages())
            <div class="mt-8">
                {{ $vendors->links() }}
            </div>
        @endif
    @endif
</div>
@endsection

