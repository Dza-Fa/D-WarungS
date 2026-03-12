@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'fullWidth' => false,
])

<button 
    type="{{ $type }}"
    @class([
        'font-medium rounded-lg transition-all duration-300 inline-flex items-center justify-center gap-2',
        'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500',
        'active:scale-95 transform',
        // Size classes
        'px-3 py-1.5 text-xs sm:text-sm' => $size === 'sm',
        'px-4 py-2 text-sm sm:text-base' => $size === 'md',
        'px-6 py-3 text-base sm:text-lg' => $size === 'lg',
        // Width
        'w-full' => $fullWidth,
        // Variant classes
        'bg-orange-600 text-white hover:bg-orange-700 disabled:bg-gray-400 focus:ring-orange-500 shadow-sm hover:shadow' => $variant === 'primary',
        'bg-gray-200 text-gray-900 hover:bg-gray-300 disabled:bg-gray-100 focus:ring-gray-500' => $variant === 'secondary',
        'bg-red-600 text-white hover:bg-red-700 disabled:bg-gray-400 focus:ring-red-500 shadow-sm hover:shadow' => $variant === 'danger',
        'bg-green-600 text-white hover:bg-green-700 disabled:bg-gray-400 focus:ring-green-500 shadow-sm hover:shadow' => $variant === 'success',
        'bg-white text-orange-600 hover:bg-gray-100 disabled:bg-gray-200 focus:ring-orange-500 shadow-sm hover:shadow' => $variant === 'white',
        'bg-transparent border-2 border-orange-600 text-orange-600 hover:bg-orange-50 disabled:border-gray-400 disabled:text-gray-400 focus:ring-orange-500' => $variant === 'outline',
        'bg-transparent text-orange-600 hover:bg-orange-50 disabled:text-gray-400 focus:ring-orange-500' => $variant === 'ghost',
        // States
        'disabled:opacity-60 disabled:cursor-not-allowed' => $disabled || $loading,
    ])
    @disabled($disabled || $loading)
    {{ $attributes }}
>
    @if($loading)
        <span class="inline-block w-4 h-4 border-2 border-current border-r-transparent rounded-full animate-spin flex-shrink-0"></span>
    @elseif($icon && $iconPosition === 'left')
        <span class="flex-shrink-0">{{ $icon }}</span>
    @endif
    
    <span @class(['line-clamp-1' => !$fullWidth])>{{ $slot }}</span>
    
    @if($icon && $iconPosition === 'right' && !$loading)
        <span class="flex-shrink-0">{{ $icon }}</span>
    @endif
</button>
