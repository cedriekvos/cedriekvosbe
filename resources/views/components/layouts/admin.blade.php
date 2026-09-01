<!DOCTYPE html>
<html lang="nl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) && $title ? $title . ' — /home/cedriek' : 'admin — /home/cedriek' }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
    <script>
        (function () {
            function applyTheme() {
                var s = localStorage.getItem('theme');
                var mode = (s === 'light' || s === 'dark') ? s : 'auto';
                var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', mode === 'dark' || (mode === 'auto' && d));
            }

            applyTheme();

            document.addEventListener('livewire:navigating', function (e) {
                e.detail.onSwap(applyTheme);
            });
        }());
    </script>
</head>
<body class="antialiased flex flex-col min-h-screen">

    <header class="site-header sticky top-0 z-10">
        <div class="max-w-3xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="font-mono font-semibold text-sm text-fg hover:text-accent transition-colors leading-none">
                /home/cedriek<span class="cursor"></span>
            </a>
            <nav class="hidden md:flex items-center gap-4 font-mono text-xs">
                <a href="{{ route('admin.posts.index') }}" class="text-muted hover:text-fg">[posts]</a>
                <a href="{{ route('admin.messages.index') }}" class="text-muted hover:text-fg">[messages]</a>
                <a href="{{ route('admin.about.edit') }}" class="text-muted hover:text-fg">[about]</a>
                <a href="{{ route('admin.scratchpad.edit') }}" class="text-muted hover:text-fg">[scratchpad]</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-muted hover:text-fg">[logout]</button>
                </form>
            </nav>
            <button id="mobile-menu-toggle" type="button" class="md:hidden font-mono text-xs text-muted hover:text-fg" aria-expanded="false" aria-controls="mobile-menu">
                <span id="mobile-menu-label">[menu]</span>
            </button>
        </div>
        <nav id="mobile-menu" class="hidden md:hidden flex-col gap-3 px-6 pt-4 pb-6 font-mono text-xs" style="border-top: 1px solid var(--t-line);">
            <a href="{{ route('admin.posts.index') }}" class="text-muted hover:text-fg">[posts]</a>
            <a href="{{ route('admin.messages.index') }}" class="text-muted hover:text-fg">[messages]</a>
            <a href="{{ route('admin.about.edit') }}" class="text-muted hover:text-fg">[about]</a>
            <a href="{{ route('admin.scratchpad.edit') }}" class="text-muted hover:text-fg">[scratchpad]</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-muted hover:text-fg">[logout]</button>
            </form>
        </nav>
    </header>

    <main class="flex-1 max-w-3xl mx-auto w-full px-6 py-10">
        {{ $slot }}
    </main>

    <footer style="border-top: 1px solid var(--t-line);">
        <div class="max-w-3xl mx-auto px-6 py-5">
            <p class="font-mono text-xs text-muted">
                <span class="text-accent">cedriek@blog</span>:<span class="text-hi">~/admin</span>$&nbsp;<span style="opacity: 0.45;">exit 0</span>
            </p>
        </div>
    </footer>

    <script>
        var menuToggle = document.getElementById('mobile-menu-toggle');
        var menuLabel  = document.getElementById('mobile-menu-label');
        var menu       = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', function () {
            var isOpen = menu.classList.toggle('flex');
            menu.classList.toggle('hidden', !isOpen);
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            menuLabel.textContent = isOpen ? '[close]' : '[menu]';
        });
    </script>

</body>
</html>
