@props(['label', 'routePrefix', 'items'])

<div class="inline-flex items-center">
    <x-dropdown align="left" width="48" contentClasses="py-1 bg-white dark:bg-gray-700">
        <x-slot name="trigger">
            <button @class([
                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none',
                'border-brand-400 text-gray-900 dark:text-gray-100' => request()->routeIs($routePrefix.'.*'),
                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' => ! request()->routeIs($routePrefix.'.*'),
            ])>
                {{ $label }}
                <svg class="ms-1 fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            {{ $slot }}
            {{ $items }}
        </x-slot>
    </x-dropdown>
</div>
