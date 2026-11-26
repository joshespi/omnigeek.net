<button {{ $attributes->merge(['type' => 'submit', 'class' => 'site-primary-action disabled:opacity-60']) }}>
    {{ $slot }}
</button>
