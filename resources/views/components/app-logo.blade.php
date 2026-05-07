@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center gap-2 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-400 px-3 py-1.5">
            <span class="text-sm font-bold uppercase tracking-wider text-amber-900 dark:text-amber-100 whitespace-nowrap">Wisdom Inn</span>
            <span class="text-zinc-400 dark:text-zinc-500 font-light">X</span>
            <img src="{{ asset('images/uthm-holdings.png') }}" alt="Wisdom Inn Logo" class="h-6 w-auto" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center gap-2 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-400 px-3 py-1.5">
            <span class="text-sm font-bold uppercase tracking-wider text-amber-900 dark:text-amber-100 whitespace-nowrap">Wisdom Inn</span>
            <span class="text-zinc-400 dark:text-zinc-500 font-light">X</span>
            <img src="{{ asset('images/uthm-holdings.png') }}" alt="Wisdom Inn Logo" class="h-6 w-auto" />
        </x-slot>
    </flux:brand>
@endif
