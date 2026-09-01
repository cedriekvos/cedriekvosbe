<div>
    <div class="flex items-center justify-between mb-8">
        <h1 class="font-mono text-xl text-fg">~/admin/posts</h1>
        <a href="{{ route('admin.posts.create') }}"
           class="font-mono text-xs text-accent hover:underline underline-offset-2">
            [+ new post]
        </a>
    </div>

    @if (session('status'))
        <div class="font-mono text-xs text-accent mb-6">{{ session('status') }}</div>
    @endif

    <div class="space-y-2">
        @forelse ($posts as $post)
            <div class="flex items-center justify-between gap-4 py-2"
                 style="border-bottom: 1px solid var(--t-line);">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm text-fg truncate">{{ $post->title ?: $post->slug }}</span>
                        @if ($post->is_draft)
                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded"
                                  style="background: var(--t-surface); color: var(--t-muted); border: 1px solid var(--t-line);">
                                draft
                            </span>
                        @endif
                    </div>
                    <div class="font-mono text-[11px] text-muted">
                        {{ $post->slug }} · {{ $post->date ? \Illuminate\Support\Carbon::parse($post->date)->format('d/m/Y') : '—' }}
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0 font-mono text-xs">
                    <a href="{{ route('admin.posts.edit', ['slug' => $post->slug]) }}"
                       class="text-accent hover:underline underline-offset-2">[edit]</a>
                    <button type="button"
                            wire:click="delete('{{ $post->slug }}')"
                            wire:confirm="Delete {{ $post->slug }}?"
                            class="text-muted hover:text-fg">
                        [delete]
                    </button>
                </div>
            </div>
        @empty
            <p class="font-mono text-sm text-muted">No posts yet.</p>
        @endforelse
    </div>
</div>
