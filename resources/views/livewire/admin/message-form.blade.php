<div>
    <nav class="font-mono text-xs text-muted mb-8 flex items-center gap-1.5">
        <a href="{{ route('admin.messages.index') }}" class="text-accent hover:underline underline-offset-2">~/admin/messages</a>
        <span>/</span>
        <span class="text-fg">{{ $id ?? 'new' }}</span>
    </nav>

    <form wire:submit="save" class="space-y-6">
        <div>
            <label for="body" class="block font-mono text-xs text-muted mb-1">message</label>
            <textarea id="body" wire:model="body" rows="4" maxlength="280"
                      class="w-full font-mono text-sm px-3 py-2 bg-transparent"
                      style="border: 1px solid var(--t-line); color: var(--t-fg);"></textarea>
            @error('body') <p class="font-mono text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="font-mono text-xs px-3 py-2 text-accent hover:underline underline-offset-2"
                    style="border: 1px solid var(--t-line);">
                [save]
            </button>
            <a href="{{ route('admin.messages.index') }}"
               class="font-mono text-xs text-muted hover:text-fg">[cancel]</a>
        </div>
    </form>
</div>
