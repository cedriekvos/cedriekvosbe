<x-layouts.app :title="$post->title ?: 'Post'">

    <nav class="font-mono text-xs text-muted mb-8 flex items-center gap-1.5">
        <a href="/" class="text-accent hover:underline underline-offset-2">~/posts</a>
        <span>/</span>
        <span class="text-fg">{{ $post->slug }}</span>
    </nav>

    <header class="mb-10 pb-6" style="border-bottom: 1px solid var(--t-line);">
        <h1 class="font-mono font-bold text-2xl text-fg leading-snug mb-2">
            {{ $post->title ?: $post->slug }}
        </h1>
        <div class="flex items-center gap-2 font-mono text-xs text-muted">
            <time>{{ \Illuminate\Support\Carbon::parse($post->date ?: 'now')->format('d/m/Y') }}</time>
            <span>&middot;</span>
            <span>{{ $post->read_time_minutes }} min read</span>
        </div>
    </header>

    @if(filled($post->excerpt))
        <p class="post-excerpt text-base text-fg italic leading-relaxed mb-10 pl-4">{{ $post->excerpt }}</p>
    @endif

    <article class="prose prose-headings:scroll-mt-24">
        {!! $post->content !!}
    </article>

</x-layouts.app>
