@props([
    'datalistId',
    'hints' => collect(),
    'placeholder' => 'Series name (optional)',
])

{{-- Series: group a multi-part read. Shared across authors; part number sets reading order.
     Shared by the compose box and the inline post editor — each passes its own datalist id. --}}
<div class="mt-3 flex flex-col sm:flex-row gap-2">
    <div class="flex-1">
        <input type="text" wire:model="form.series" list="{{ $datalistId }}"
            placeholder="{{ $placeholder }}"
            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
        @if ($hints->isNotEmpty())
            <datalist id="{{ $datalistId }}">
                @foreach ($hints as $hint)
                    <option value="{{ $hint }}"></option>
                @endforeach
            </datalist>
        @endif
        <x-input-error :messages="$errors->get('form.series')" class="mt-1" />
    </div>
    <div class="sm:w-28">
        <input type="number" min="1" max="999" wire:model="form.seriesPart" placeholder="Part #"
            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
        <x-input-error :messages="$errors->get('form.seriesPart')" class="mt-1" />
    </div>
</div>
