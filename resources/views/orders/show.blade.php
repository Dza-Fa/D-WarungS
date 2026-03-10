@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' - D-WarungS')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Link -->
    <a href="{{ route('orders.index') }}" class="inline-flex items-center text-gray-600 hover:text-orange-600 mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Orders
    </a>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
                <p class="mt-1 text-gray-500">Placed on {{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            @switch($order->status)
                @case('pending')
                    <span class="px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Pending</span>
                    @break
                @case('confirmed')
                    <span class="px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">Confirmed</span>
                    @break
                @case('processing')
                    <span class="px-4 py-2 rounded-full text-sm font-medium bg-purple-100 text-purple-800">Processing</span>
                    @break
                @case('ready')
                    <span class="px-4 py-2 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">Ready</span>
                    @break
                @case('completed')
                    <span class="px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">Completed</span>
                    @break
                @case('cancelled')
                    <span class="px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">Cancelled</span>
                    @break
            @endswitch
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <h2 class="font-semibold text-gray-900">Order Items</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($order->orderItems as $item)
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900">{{ $item->product->name ?? 'Product #' . $item->product_id }}</p>
                                    <p class="text-sm text-gray-500">{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <h2 class="font-semibold text-gray-900">Payment Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Payment Method</p>
                            <p class="font-medium text-gray-900">{{ ucfirst($order->payment_method) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Payment Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t">
                        <div class="space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if($order->tax_amount > 0)
                                <div class="flex justify-between text-gray-600">
                                    <span>Tax</span>
                                    <span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($order->discount_amount > 0)
                                <div class="flex justify-between text-gray-600">
                                    <span>Discount</span>
                                    <span class="text-green-600">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                                <span>Total</span>
                                <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Vendor Info -->
            @if($order->vendor)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Vendor</h3>
                    <div class="flex items-center gap-3">
                        @if($order->vendor->logo)
                            <img src="{{ asset('storage/' . $order->vendor->logo) }}" alt="{{ $order->vendor->name }}" class="w-12 h-12 rounded-lg object-cover">
                        @else
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <span class="text-xl font-bold text-orange-600">{{ substr($order->vendor->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-900">{{ $order->vendor->name }}</p>
                            <a href="{{ route('vendors.show', $order->vendor) }}" class="text-sm text-orange-600 hover:underline">View Menu</a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Order Timeline -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Order Timeline</h3>
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="w-2 h-2 mt-2 rounded-full bg-green-500"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Order Placed</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @if($order->status !== 'pending')
                        <div class="flex gap-3">
                            <div class="w-2 h-2 mt-2 rounded-full bg-blue-500"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Order Confirmed</p>
                                <p class="text-xs text-gray-500">Vendor has confirmed your order</p>
                            </div>
                        </div>
                    @endif
                    @if(in_array($order->status, ['processing', 'ready', 'completed']))
                        <div class="flex gap-3">
                            <div class="w-2 h-2 mt-2 rounded-full bg-purple-500"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Order Processing</p>
                                <p class="text-xs text-gray-500">Your order is being prepared</p>
                            </div>
                        </div>
                    @endif
                    @if($order->status === 'completed')
                        <div class="flex gap-3">
                            <div class="w-2 h-2 mt-2 rounded-full bg-green-500"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Order Completed</p>
                                <p class="text-xs text-gray-500">Order has been delivered</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

