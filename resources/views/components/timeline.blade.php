@props([
    'steps' => [],
    'currentStep' => 0,
    'timestamps' => [],
])

<div class="space-y-4">
    @foreach($steps as $index => $step)
        @php
            $isCompleted = $index < $currentStep;
            $isCurrent = $index === $currentStep;
            $timestamp = $timestamps[$index] ?? null;
        @endphp
        
        <div class="flex gap-4">
            <!-- Timeline Marker -->
            <div class="flex flex-col items-center">
                <div @class([
                    'w-10 h-10 rounded-full border-2 flex items-center justify-center font-semibold text-sm transition-all duration-300 flex-shrink-0',
                    'bg-orange-600 border-orange-600 text-white' => $isCompleted || $isCurrent,
                    'bg-white border-gray-300 text-gray-400' => !$isCompleted && !$isCurrent,
                ])>
                    @if($isCompleted)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        {{ $index + 1 }}
                    @endif
                </div>
                
                @if($index < count($steps) - 1)
                    <div class="w-1 h-12 mt-2 transition-all duration-300" @class([
                        'bg-orange-600' => $isCompleted || $isCurrent,
                        'bg-gray-300' => !$isCompleted && !$isCurrent,
                    ])></div>
                @endif
            </div>
            
            <!-- Step Content -->
            <div class="pb-4 flex-1 min-w-0">
                <div class="flex items-baseline gap-2">
                    <h4 @class([
                        'font-semibold transition-colors duration-300',
                        'text-orange-600' => $isCurrent,
                        'text-gray-900' => $isCompleted,
                        'text-gray-600' => !$isCompleted && !$isCurrent,
                    ])>
                        {{ $step }}
                    </h4>
                    
                    @if($isCurrent)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 animate-pulse">
                            In Progress
                        </span>
                    @endif
                </div>
                
                @if($timestamp)
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $timestamp }}
                    </p>
                @elseif($isCurrent)
                    <p class="text-sm text-gray-500 mt-1">
                        Estimated time: 
                        <span class="font-medium text-gray-700">
                            {{ $slot ?? '30 minutes' }}
                        </span>
                    </p>
                @endif
            </div>
        </div>
    @endforeach
</div>
