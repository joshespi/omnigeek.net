@include('partials.xml-declaration')
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ route('home') }}</link>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
        <description>{{ config('app.name') }} — a small mini-blog for geeks.</description>
        <language>en-us</language>
        <lastBuildDate>{{ ($posts->first()?->displayDate() ?? now())->toRssString() }}</lastBuildDate>
@foreach ($posts as $post)
        <item>
            <title><![CDATA[{!! $post->title ?: 'A post by '.$post->user->name !!}]]></title>
            <link>{{ route('posts.show', $post) }}</link>
            <guid isPermaLink="true">{{ route('posts.show', $post) }}</guid>
            <pubDate>{{ $post->displayDate()->toRssString() }}</pubDate>
            <dc:creator><![CDATA[{!! $post->user->name !!}]]></dc:creator>
            @foreach ($post->categories as $category)
            <category><![CDATA[{!! $category->name !!}]]></category>
            @endforeach
            <description><![CDATA[{!! \App\Support\Markdown::render($post->body ?? '') !!}]]></description>
        </item>
@endforeach
    </channel>
</rss>
