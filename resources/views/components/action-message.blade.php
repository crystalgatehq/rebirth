@props([
    'type' => 'success',
    'message' => '',
    'dismissable' => true,
    'timeout' => 5000
])

@php
    $allStyles = [
        'success' => [
            'bg' => 'bg-green-50',
            'text' => 'text-green-800',
            'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
            'iconColor' => 'text-green-400'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'text' => 'text-red-800',
            'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z',
            'iconColor' => 'text-red-400'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'text' => 'text-yellow-800',
            'icon' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
            'iconColor' => 'text-yellow-400'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-800',
            'icon' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h2a1 1 0 100-2v-3a1 1 0 00-1-1H9z',
            'iconColor' => 'text-blue-400'
        ]
    ];

    $style = $allStyles[$type] ?? $allStyles['success'];
@endphp

<div x-data="{ show: true }" 
     x-show="show" 
     x-init="@if($timeout) setTimeout(() => { show = false }, {{ $timeout }}) @endif"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="rounded-md p-4 {{ $style['bg'] }}">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 {{ $style['iconColor'] }}" 
                 viewBox="0 0 20 20" 
                 fill="currentColor">
                <path fill-rule="evenodd" d="{{ $style['icon'] }}" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium {{ $style['text'] }}">
                {{ $message }}
            </p>
        </div>
        @if($dismissable)
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button @click="show = false" 
                            type="button" 
                            class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $style['bg'] }} {{ $style['iconColor'] }} hover:opacity-75">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
