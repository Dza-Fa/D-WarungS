@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
])

<span @class([
    'inline-flex items-center gap-1 font-medium rounded-full transition-colors duration-200',
    // Size classes
    'px-2 py-1 text-xs' => $size === 'sm',
    'px-3 py-1.5 text-sm' => $size === 'md',
    'px-4 py-2 text-base' => $size === 'lg',
    // Variant classes
    'bg-orange-100 text-orange-800' => $variant === 'primary',
    'bg-green-100 text-green-800' => $variant === 'success',
    'bg-red-100 text-red-800' => $variant === 'danger',
    'bg-yellow-100 text-yellow-800' => $variant === 'warning',
    'bg-blue-100 text-blue-800' => $variant === 'info',
    'bg-gray-100 text-gray-800' => $variant === 'secondary',
]) {{ $attributes }}>
    @if($icon)
        <span class="flex-shrink-0">{{ $icon }}</span>
    @endif
    {{ $slot }}
</span>
