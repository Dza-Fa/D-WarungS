@extends('layouts.app')

@section('title', 'D-WarungS - Your Food Court Online')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to D-WarungS</h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90">Your favorite food court, now online!</p>
            <a href="#vendors" class="inline-block bg-white text-orange-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors">
                Explore Vendors
            </a>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6">
                <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Multiple Vendors</h3>
                <p class="text-gray-600">Choose from a variety of food vendors all in one place</p>
            </div>
            <div class="text-center p-6">
                <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Fast Ordering</h3>
                <p class="text-gray-600">Quick and easy ordering process with instant confirmation</p>
            </div>
            <div class="text-center p-6">
                <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Secure Payments</h3>
                <p class="text-gray-600">Safe and secure payment options for your peace of mind</p>
            </div>
        </div>
    </div>
</div>

<!-- Vendors Section -->
<div id="vendors" class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Our Vendors</h2>
            <a href="{{ route('vendors.index') }}" class="text-orange-600 hover:text-orange-700 font-medium">
                View All &rarr;
            </a>
        </div>

        @if($vendors->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">No vendors available at the moment. Please check back later.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($vendors as $vendor)
                    <a href="{{ route('vendors.show', $vendor) }}" class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                        <!-- Vendor Cover Image -->
                        <div class="h-40 bg-gradient-to-br from-orange-100 to-orange-200 relative">
                            @if($vendor->cover_image)
                                <img src="{{ asset('storage/' . $vendor->cover_image) }}" alt="{{ $vendor->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="text-4xl font-bold text-orange-300">{{ substr($vendor->name, 0, 1) }}</span>
                                </div>
                            @endif
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
                            <div class="flex items-center gap-3 mb-2">
                                @if($vendor->logo)
                                    <img src="{{ asset('storage/' . $vendor->logo) }}" alt="{{ $vendor->name }}" class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <span class="text-xl font-bold text-orange-600">{{ substr($vendor->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $vendor->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $vendor->categories->count() }} categories</p>
                                </div>
                            </div>
                            @if($vendor->description)
                                <p class="text-sm text-gray-600 line-clamp-2">{{ $vendor->description }}</p>
                            @endif
                            @if($vendor->address)
                                <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $vendor->address }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- CTA Section -->
<div class="bg-orange-500 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Ready to Order?</h2>
        <p class="text-xl opacity-90 mb-6">Browse our vendors and find your favorite food today!</p>
        <a href="{{ route('vendors.index') }}" class="inline-block bg-white text-orange-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors">
            Start Ordering
        </a>
    </div>
</div>
@endsection

