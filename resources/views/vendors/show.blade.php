@extends('layouts.app')

@section('title', $vendor->name . ' - D-WarungS')

@section('content')
<!-- Vendor Header -->
<div class="bg-white shadow-sm sticky top-16 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('vendors.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                
                <div class="flex items-center gap-3">
                    @if($vendor->logo)
                        <img src="{{ asset('storage/' . $vendor->logo) }}" alt="{{ $vendor->name }}" class="w-14 h-14 rounded-xl object-cover shadow-sm">
                    @else
                        <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center shadow-sm">
                            <span class="text-2xl font-bold text-orange-600">{{ substr($vendor->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $vendor->name }}</h1>
                        <div class="flex items-center gap-2 text-sm">
                            @if($vendor->rating)
                                <span class="flex items-center gap-1 text-yellow-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ number_format($vendor->rating, 1) }}
                                </span>
                            @endif
                            @if($vendor->address)
                                <span class="text-gray-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $vendor->address }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('cart.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Cart
                    @php
                        $cartCount = session()->get('cart', []) ? array_sum(array_column(session()->get('cart', []), 'quantity')) : 0;
                    @endphp
                    @if($cartCount > 0)
                        <span class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Vendor Description -->
@if($vendor->description)
    <div class="bg-gray-50 border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-gray-600">{{ $vendor->description }}</p>
        </div>
    </div>
@endif

<!-- Menu Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if($vendor->categories->isEmpty())
        <div class="text-center py-12 bg-white rounded-xl shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-gray-500 text-lg">No menu items available yet.</p>
        </div>
    @else
        @foreach($vendor->categories as $category)
            <div class="mb-10" id="category-{{ $category->id }}">
                <div class="flex items-center gap-3 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $category->name }}</h2>
                    @if($category->products->count() > 0)
                        <span class="bg-orange-100 text-orange-700 text-sm px-3 py-1 rounded-full">
                            {{ $category->products->count() }} items
                        </span>
                    @endif
                </div>
                
                @if($category->products->isEmpty())
                    <p class="text-gray-500">No products in this category.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($category->products as $product)
                            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                <!-- Product Image -->
                                <div class="h-40 bg-gray-100 relative">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                    
                                    @if($product->is_featured)
                                        <span class="absolute top-3 left-3 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                                            Featured
                                        </span>
                                    @endif
                                </div>
                                
                                <!-- Product Info -->
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 mb-1">{{ $product->name }}</h3>
                                    @if($product->description)
                                        <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $product->description }}</p>
                                    @endif
                                    
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-lg font-bold text-orange-600">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                            @if($product->original_price && $product->original_price > $product->price)
                                                <span class="text-sm text-gray-400 line-through ml-2">Rp {{ number_format($product->original_price, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                        
                                        <form action="{{ route('cart.add') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center gap-1 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                Add
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection

