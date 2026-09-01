@props(['icon' => '★'])

<span {{ $attributes->merge(['class' => 'post-badge']) }}>
    <span aria-hidden="true">{{ $icon }}</span>{{ $slot->isEmpty() ? 'Must read' : $slot }}
</span>
