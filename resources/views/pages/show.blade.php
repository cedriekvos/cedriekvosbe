<x-layouts.app :title="$page->title ?: 'Page'">

    <nav class="font-mono text-xs text-muted mb-8 flex items-center gap-1.5">
        <a href="/" class="text-accent hover:underline underline-offset-2">~/</a>
        <span>/</span>
        <span class="text-fg">{{ $page->slug }}</span>
    </nav>

    <header class="mb-10 pb-6" style="border-bottom: 1px solid var(--t-line);">
        <h1 class="font-mono font-bold text-2xl text-fg leading-snug mb-2">
            {{ $page->title ?: $page->slug }}
        </h1>
    </header>

    <article class="prose prose-headings:scroll-mt-24">
        {!! $page->content !!}
    </article>

</x-layouts.app>
