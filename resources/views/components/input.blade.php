@props([
    'name',
    'label' => null,
    'type' => 'text',
    'error' => null,
    'hint' => null,
    'required' => false,
    'placeholder' => null,
])

<div class="mb-4">
    @if($label)
        <label 
            for="{{ $name }}"
            class="block text-sm font-medium text-gray-700 mb-2"
        >
            {{ $label }}
            @if($required)
                <span class="text-red-600 font-bold" aria-label="required">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        @class([
            'block w-full px-4 py-2 text-base border rounded-lg transition-all duration-200',
            'focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent',
            'border-gray-300 hover:border-gray-400 bg-white' => !$error,
            'border-red-300 hover:border-red-400 bg-red-50 focus:ring-red-500' => $error,
            'disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed disabled:border-gray-300',
            'placeholder-gray-400',
        ])
        {{ $attributes }}
    />
    
    @if($error)
        <div class="mt-2 text-sm text-red-600 flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414L10 16.586l-6.687-6.687a1 1 0 00-1.414 1.414l8 8a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $error }}</span>
        </div>
    @endif
    
    @if($hint && !$error)
        <p class="mt-2 text-sm text-gray-500">{{ $hint }}</p>
    @endif
</div>
