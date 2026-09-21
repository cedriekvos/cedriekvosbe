<div>
    <div class="flex items-center justify-between mb-8">
        <h1 class="font-mono text-xl text-fg">~/admin/pages</h1>
        <a href="{{ route('admin.pages.create') }}"
           class="font-mono text-xs text-accent hover:underline underline-offset-2">
            [+ new page]
        </a>
    </div>

    @if (session('status'))
        <div class="font-mono text-xs text-accent mb-6">{{ session('status') }}</div>
    @endif

    <div class="space-y-2">
        @forelse ($pages as $page)
            <div class="flex items-center justify-between gap-4 py-2"
                 style="border-bottom: 1px solid var(--t-line);">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm text-fg truncate">{{ $page->title ?: $page->slug }}</span>
                        @if ($page->is_draft)
                            <span class="font-mono text-[10px] px-1.5 py-0.5 rounded"
                                  style="background: var(--t-surface); color: var(--t-muted); border: 1px solid var(--t-line);">
                                draft
                            </span>
                        @endif
                    </div>
                    <div class="font-mono text-[11px] text-muted">
                        {{ $page->slug }}
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0 font-mono text-xs">
                    <a href="{{ route('admin.pages.edit', ['slug' => $page->slug]) }}"
                       class="text-accent hover:underline underline-offset-2">[edit]</a>
                    <button type="button"
                            wire:click="delete('{{ $page->slug }}')"
                            wire:confirm="Delete {{ $page->slug }}?"
                            class="text-muted hover:text-fg">
                        [delete]
                    </button>
                </div>
            </div>
        @empty
            <p class="font-mono text-sm text-muted">No pages yet.</p>
        @endforelse
    </div>
</div>
