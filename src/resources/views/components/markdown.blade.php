@props(['text'])

<div {{ $attributes->merge(['class' => 'prose prose-sm dark:prose-invert max-w-none break-words prose-img:rounded-md prose-img:mx-auto']) }}>
    {!! \App\Support\Markdown::render($text) !!}
</div>
