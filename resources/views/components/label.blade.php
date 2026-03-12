@props([
    'for' => null,
    'required' => false,
])

<label 
    @if($for)
        for="{{ $for }}"
    @endif
    @class([
        'block text-sm font-medium text-gray-700 mb-2 transition-colors duration-200',
    ])
>
    {{ $slot }}
    @if($required)
        <span class="text-red-600 font-bold ml-0.5" aria-label="required">*</span>
    @endif
</label>
