<div>
    <nav class="font-mono text-xs text-muted mb-8 flex items-center gap-1.5">
        <a href="{{ route('admin.posts.index') }}" class="text-accent hover:underline underline-offset-2">~/admin/posts</a>
        <span>/</span>
        <span class="text-fg">{{ $originalSlug ?? 'new' }}</span>
    </nav>

    <form wire:submit="save" class="space-y-6">
        <div>
            <label for="title" class="block font-mono text-xs text-muted mb-1">title</label>
            <input type="text" id="title" wire:model="title" wire:blur="fillSlugFromTitle"
                   class="w-full font-mono text-sm px-3 py-2 bg-transparent"
                   style="border: 1px solid var(--t-line); color: var(--t-fg);">
            @error('title') <p class="font-mono text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="slug" class="block font-mono text-xs text-muted mb-1">slug</label>
                <input type="text" id="slug" wire:model="slug"
                       class="w-full font-mono text-sm px-3 py-2 bg-transparent"
                       style="border: 1px solid var(--t-line); color: var(--t-fg);">
                @error('slug') <p class="font-mono text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                <p class="font-mono text-[10px] text-muted mt-1">lowercase letters, digits, dashes</p>
            </div>
            <div>
                <label for="date" class="block font-mono text-xs text-muted mb-1">date</label>
                <input type="date" id="date" wire:model="date"
                       class="w-full font-mono text-sm px-3 py-2 bg-transparent"
                       style="border: 1px solid var(--t-line); color: var(--t-fg);">
                @error('date') <p class="font-mono text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="excerpt" class="block font-mono text-xs text-muted mb-1">excerpt</label>
            <textarea id="excerpt" wire:model="excerpt" rows="2"
                      class="w-full font-mono text-sm px-3 py-2 bg-transparent"
                      style="border: 1px solid var(--t-line); color: var(--t-fg);"></textarea>
            @error('excerpt') <p class="font-mono text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="post-body" class="block font-mono text-xs text-muted mb-1">body (markdown)</label>
            <div wire:ignore>
                <textarea id="post-body" wire:model="body">{{ $body }}</textarea>
            </div>
            @error('body') <p class="font-mono text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 font-mono text-xs text-muted">
            <input type="checkbox" wire:model="isDraft" class="accent-current">
            draft (hide from public listing)
        </label>

        <label class="flex items-center gap-2 font-mono text-xs text-muted">
            <input type="checkbox" wire:model="isFeatured" class="accent-current">
            featured (show the Uitgelicht badge)
        </label>

        <div class="flex items-center gap-3 pt-2">
            <button type="button"
                    onclick="if (window.postEditor) { @this.set('body', window.postEditor.value()); } @this.save()"
                    class="font-mono text-xs px-3 py-2 text-accent hover:underline underline-offset-2"
                    style="border: 1px solid var(--t-line);">
                [save]
            </button>
            <a href="{{ route('admin.posts.index') }}"
               class="font-mono text-xs text-muted hover:text-fg">[cancel]</a>
        </div>
    </form>
</div>
