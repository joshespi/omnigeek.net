@props(['items', 'model', 'value' => 'id', 'label' => 'name'])

<div class="flex flex-wrap gap-2">
    @foreach ($items as $item)
        <label class="cursor-pointer">
            <input type="checkbox" wire:model="{{ $model }}" value="{{ $item->{$value} }}" class="peer sr-only" />
            <span class="inline-block px-3 py-1 rounded-full text-sm border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-600">
                {{ $item->{$label} }}
            </span>
        </label>
    @endforeach
</div>
