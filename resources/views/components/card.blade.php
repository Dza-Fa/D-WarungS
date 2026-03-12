@props([
    'hoverable' => true,
    'clickable' => true,
])

<div 
    @class([
        'bg-white rounded-xl border border-gray-200 overflow-hidden transition-all duration-300',
        'hover:shadow-md hover:border-gray-300' => $hoverable,
        'cursor-pointer hover:shadow-lg' => $clickable,
        'shadow-sm' => !$hoverable,
    ])
    {{ $attributes }}
>
    {{ $slot }}
</div>
