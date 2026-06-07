@props(['text'])

@php
    use League\CommonMark\Environment\Environment;
    use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
    use League\CommonMark\MarkdownConverter;

    $env = new Environment(['html_input' => 'strip', 'allow_unsafe_links' => false]);
    $env->addExtension(new CommonMarkCoreExtension());
    $html = (new MarkdownConverter($env))->convert($text);
@endphp

<div {{ $attributes->merge(['class' => 'prose prose-sm dark:prose-invert max-w-none break-words']) }}>
    {!! $html !!}
</div>
