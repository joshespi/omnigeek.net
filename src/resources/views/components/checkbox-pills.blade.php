@props(['items', 'model', 'value' => 'id', 'label' => 'name'])

<div class="flex flex-wrap gap-2">
    @foreach ($items as $item)
        <label class="cursor-pointer">
            <input type="checkbox" wire:model="{{ $model }}" value="{{ $item->{$value} }}" class="peer sr-only" />
            <span class="category-chip peer-checked:border-brand-600 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:hover:bg-brand-700 peer-checked:hover:text-white peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-editorial-accent">
                {{ $item->{$label} }}
            </span>
        </label>
    @endforeach
</div>
