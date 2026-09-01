<x-layouts.app>
    @if($about->is_visible)
        <p class="font-mono text-xs text-muted mb-4">
            <span class="text-accent">cedriek@blog</span>:<span class="text-hi">~</span>$ whoami
        </p>

        <section data-section="about-me" class="about-panel mb-12 pl-5 py-1">
            @if($about->heading !== '')
                <h1 class="about-heading font-mono text-2xl font-bold mb-3 leading-snug">
                    {{ $about->heading }}
                </h1>
            @endif
            @if($about->bio_as_html !== '')
                <div class="about-bio text-sm text-muted leading-relaxed space-y-3">{!! $about->bio_as_html !!}</div>
            @endif
        </section>
    @endif

    <p class="font-mono text-xs text-muted mb-4">
        <span class="text-accent">cedriek@blog</span>:<span class="text-hi">~</span>$ ls posts/
    </p>

    @if(empty($posts))
        <p class="font-mono text-sm text-muted">// no posts yet</p>
    @else
        <div class="post-grid grid grid-cols-1 sm:grid-cols-2 gap-5">
            @foreach($posts as $post)
                <a href="/blog/{{ $post->slug }}" class="post-card group block p-5 rounded-lg">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-mono font-bold text-lg text-fg leading-snug group-hover:text-accent transition-colors">
                            {{ $post->title ?: $post->slug }}
                        </h3>
                        @if($post->is_featured)
                            <x-post-badge class="shrink-0 mt-0.5">Uitgelicht</x-post-badge>
                        @endif
                    </div>
                    @if(filled($post->excerpt))
                        <p class="text-sm text-muted mt-2 leading-relaxed">{{ $post->excerpt }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-4 font-mono text-xs text-muted">
                        <span>{{ \Illuminate\Support\Carbon::parse($post->date ?: 'now')->format('d/m/Y') }}</span>
                        <span>&middot;</span>
                        <span>{{ $post->read_time_minutes }} min read</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <section data-section="microblog" class="mt-12">
        <p class="font-mono text-xs text-muted mb-4">
            <span class="text-accent">cedriek@blog</span>:<span class="text-hi">~</span>$ tail -n 100 messages.log
        </p>

        @if(empty($messages))
            <p class="font-mono text-sm text-muted">// Nog geen berichten.</p>
        @else
            <div class="message-feed">
                @foreach($messages as $message)
                    <div class="message-entry">
                        <span class="message-dot"></span>
                        <time class="font-mono text-xs text-muted tabular-nums">
                            {{ \Illuminate\Support\Carbon::parse($message->posted_at)->format('d/m/Y H:i') }}
                        </time>
                        <div class="message-body text-sm text-fg leading-relaxed mt-1">{!! $message->body_as_html !!}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
