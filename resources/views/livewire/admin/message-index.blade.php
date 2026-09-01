<div>
    <div class="flex items-center justify-between mb-8">
        <h1 class="font-mono text-xl text-fg">~/admin/messages</h1>
        <a href="{{ route('admin.messages.create') }}"
           class="font-mono text-xs text-accent hover:underline underline-offset-2">
            [+ new message]
        </a>
    </div>

    @if (session('status'))
        <div class="font-mono text-xs text-accent mb-6">{{ session('status') }}</div>
    @endif

    <div class="space-y-2">
        @forelse ($messages as $message)
            <div class="flex items-center justify-between gap-4 py-2"
                 style="border-bottom: 1px solid var(--t-line);">
                <div class="min-w-0 flex-1">
                    <div class="message-body font-mono text-sm text-fg">{!! $message->body_as_html !!}</div>
                    <div class="font-mono text-[11px] text-muted">
                        {{ \Illuminate\Support\Carbon::parse($message->posted_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0 font-mono text-xs">
                    <a href="{{ route('admin.messages.edit', ['id' => $message->id]) }}"
                       class="text-accent hover:underline underline-offset-2">[edit]</a>
                    <button type="button"
                            wire:click="delete('{{ $message->id }}')"
                            wire:confirm="Delete this message?"
                            class="text-muted hover:text-fg">
                        [delete]
                    </button>
                </div>
            </div>
        @empty
            <p class="font-mono text-sm text-muted">Nog geen berichten.</p>
        @endforelse
    </div>
</div>
