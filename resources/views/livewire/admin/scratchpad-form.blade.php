<div>
    <nav class="font-mono text-xs text-muted mb-8 flex items-center gap-1.5">
        <a href="{{ route('admin.posts.index') }}" class="text-accent hover:underline underline-offset-2">~/admin/posts</a>
        <span>/</span>
        <span class="text-fg">scratchpad</span>
    </nav>

    <form wire:submit="save" class="space-y-6">
        <div>
            <label for="post-body" class="block font-mono text-xs text-muted mb-1">content (markdown)</label>
            <div wire:ignore>
                <textarea id="post-body" wire:model="content">{{ $content }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="button"
                    onclick="if (window.postEditor) { @this.set('content', window.postEditor.value()); } @this.save()"
                    class="font-mono text-xs px-3 py-2 text-accent hover:underline underline-offset-2"
                    style="border: 1px solid var(--t-line);">
                [save]
            </button>
            <a href="{{ route('admin.posts.index') }}"
               class="font-mono text-xs text-muted hover:text-fg">[cancel]</a>
        </div>
    </form>
</div>
