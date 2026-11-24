@props(['text', 'article' => false])

<div {{ $attributes->class([
    'prose max-w-none prose-img:rounded-md prose-img:mx-auto',
    'article-prose' => $article,
    'prose-sm dark:prose-invert break-words' => ! $article,
]) }}>
    {!! \App\Support\Markdown::render($text) !!}
</div>
