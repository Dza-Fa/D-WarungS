@props([
    'type' => 'info',
    'dismissible' => true,
    'title' => null,
    'icon' => true,
])

<div 
    x-data="{ show: true }"
    x-show="show"
    x-transition
    @class([
        'mb-4 p-4 rounded-lg flex items-start gap-3 border',
        'bg-blue-50 border-blue-200 text-blue-800' => $type === 'info',
        'bg-green-50 border-green-200 text-green-800' => $type === 'success',
        'bg-yellow-50 border-yellow-200 text-yellow-800' => $type === 'warning',
        'bg-red-50 border-red-200 text-red-800' => $type === 'danger',
    ])
    role="alert"
>
    @if($icon)
        <div class="flex-shrink-0 mt-0.5">
            @if($type === 'success')
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            @elseif($type === 'danger')
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            @elseif($type === 'warning')
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            @else
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/>
                </svg>
            @endif
        </div>
    @endif
    
    <div class="flex-1">
        @if($title)
            <h3 class="font-semibold mb-1">{{ $title }}</h3>
        @endif
        <p class="text-sm">{{ $slot }}</p>
    </div>
    
    @if($dismissible)
        <button 
            @click="show = false"
            class="flex-shrink-0 text-current opacity-70 hover:opacity-100 transition-opacity p-1 -mr-1"
            aria-label="Dismiss alert"
            type="button"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    @endif
</div>
