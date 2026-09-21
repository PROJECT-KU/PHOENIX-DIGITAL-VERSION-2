<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Blog Phoenix Digital</title>
        <link>{{ route('blog.index') }}</link>
        <description>Tips, panduan, dan info terbaru seputar akun premium, tools AI, dan keamanan digital.</description>
        <language>id-ID</language>
        <lastBuildDate>{{ $diperbarui }}</lastBuildDate>
        <atom:link href="{{ route('blog.feed') }}" rel="self" type="application/rss+xml" />
        @foreach ($artikel as $a)
        <item>
            <title>{{ $a->title }}</title>
            <link>{{ route('blog.show', $a->slug) }}</link>
            <guid isPermaLink="true">{{ route('blog.show', $a->slug) }}</guid>
            <pubDate>{{ optional($a->published_at ?? $a->created_at)->toRfc7231String() }}</pubDate>
            @if ($a->category)
            <category>{{ $a->category }}</category>
            @endif
            @foreach ($a->tagDaftar() as $t)
            <category>{{ $t }}</category>
            @endforeach
            <description>{{ $a->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($a->body), 200) }}</description>
        </item>
        @endforeach
    </channel>
</rss>
