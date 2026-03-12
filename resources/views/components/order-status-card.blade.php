@props([
    'order' => null,
    'status' => 'pending',
    'estimatedTime' => '30 min',
    'currentStep' => 1,
])

<x-card class="overflow-hidden">
    <!-- Header with Status -->
    <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-orange-200">
        <div class="flex items-center justify-between">
            <div>
                @if($order)
                    <h3 class="text-lg font-semibold text-gray-900">Order #{{ $order->order_number }}</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Placed on {{ $order->created_at->format('d M Y, H:i') }}
                    </p>
                @else
                    <h3 class="text-lg font-semibold text-gray-900">Order Status</h3>
                @endif
            </div>
            
            <div class="text-right">
                <x-badge 
                    @class([
                        'bg-orange-100 text-orange-800' => $status === 'pending',
                        'bg-blue-100 text-blue-800' => $status === 'confirmed',
                        'bg-purple-100 text-purple-800' => $status === 'preparing',
                        'bg-green-100 text-green-800' => $status === 'ready',
                        'bg-green-500 text-white' => $status === 'delivered',
                    ])
                    size="md"
                >
                    @switch($status)
                        @case('pending')
                            <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                <circle cx="10" cy="10" r="8"/>
                            </svg>
                            Pending
                            @break
                        @case('confirmed')
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Confirmed
                            @break
                        @case('preparing')
                            <svg class="w-4 h-4 animate-spin" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 10a7 7 0 1014 0 7 7 0 01-14 0z" clip-rule="evenodd"/>
                            </svg>
                            Preparing
                            @break
                        @case('ready')
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Ready
                            @break
                        @case('delivered')
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Delivered
                            @break
                    @endswitch
                </x-badge>
            </div>
        </div>
    </div>
    
    <!-- Estimated Time Section -->
    @if($estimatedTime && $status !== 'delivered')
        <div class="px-6 py-3 bg-blue-50 border-b border-blue-100">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">Estimated wait time</p>
                    <p class="text-xs text-blue-700 mt-0.5">{{ $estimatedTime }}</p>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Timeline Content -->
    <div class="px-6 py-6">
        {{ $slot }}
    </div>
</x-card>
