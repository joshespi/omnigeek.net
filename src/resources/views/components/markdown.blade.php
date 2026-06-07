@props(['text'])

<div {{ $attributes->merge(['class' => 'prose prose-sm dark:prose-invert max-w-none break-words']) }}>
    {!! \App\Support\Markdown::render($text) !!}
</div>
