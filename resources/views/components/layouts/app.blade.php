<!DOCTYPE html>
<html lang="nl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) && $title ? $title . ' - Cedriek Vos' : 'Cedriek Vos - Senior software engineer' }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            var STORAGE_KEY = 'theme';
            var mql = window.matchMedia('(prefers-color-scheme: dark)');

            function getMode() {
                var stored = localStorage.getItem(STORAGE_KEY);
                return (stored === 'light' || stored === 'dark') ? stored : 'auto';
            }

            function applyMode(mode) {
                var isDark = mode === 'dark' || (mode === 'auto' && mql.matches);
                document.documentElement.classList.toggle('dark', isDark);
                if (window.__syncThemeToggle) {
                    window.__syncThemeToggle(mode);
                }
            }

            applyMode(getMode());
            mql.addEventListener('change', function () {
                if (getMode() === 'auto') {
                    applyMode('auto');
                }
            });

            window.__getThemeMode = getMode;
            window.__applyThemeMode = applyMode;
        }());
    </script>
</head>
<body class="antialiased flex flex-col min-h-screen">

    <header class="site-header sticky top-0 z-10">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="fox-logo flex items-center gap-2 font-mono font-semibold text-sm text-fg hover:text-accent transition-colors leading-none">
                <svg viewBox="0 0 24 24" class="fox-icon w-[1.1rem] h-[1.1rem] shrink-0" aria-hidden="true">
                    <polygon class="fox-icon-a" points="2,3 9,8 12,5 12,22 3,15" />
                    <polygon class="fox-icon-b" points="22,3 15,8 12,5 12,22 21,15" />
                </svg>
                /home/cedriek<span class="cursor"></span>
            </a>
            <div class="flex items-center gap-4">
                <a href="https://github.com/cedriekvos" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="inline-flex text-fg hover:scale-110 transition-transform duration-150">
                    <svg viewBox="0 0 98 96" class="w-[1.1rem] h-[1.1rem] shrink-0" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <g clip-path="url(#clip0_730_27126)">
                            <path d="M41.4395 69.3848C28.8066 67.8535 19.9062 58.7617 19.9062 46.9902C19.9062 42.2051 21.6289 37.0371 24.5 33.5918C23.2559 30.4336 23.4473 23.7344 24.8828 20.959C28.7109 20.4805 33.8789 22.4902 36.9414 25.2656C40.5781 24.1172 44.4062 23.543 49.0957 23.543C53.7852 23.543 57.6133 24.1172 61.0586 25.1699C64.0254 22.4902 69.2891 20.4805 73.1172 20.959C74.457 23.543 74.6484 30.2422 73.4043 33.4961C76.4668 37.1328 78.0937 42.0137 78.0937 46.9902C78.0937 58.7617 69.1934 67.6621 56.3691 69.2891C59.623 71.3945 61.8242 75.9883 61.8242 81.252L61.8242 91.2051C61.8242 94.0762 64.2168 95.7031 67.0879 94.5547C84.4102 87.9512 98 70.6289 98 49.1914C98 22.1074 75.9883 6.69539e-07 48.9043 4.309e-07C21.8203 1.92261e-07 -1.9479e-07 22.1074 -4.3343e-07 49.1914C-6.20631e-07 70.4375 13.4941 88.0469 31.6777 94.6504C34.2617 95.6074 36.75 93.8848 36.75 91.3008L36.75 83.6445C35.4102 84.2188 33.6875 84.6016 32.1562 84.6016C25.8398 84.6016 22.1074 81.1563 19.4277 74.7441C18.375 72.1602 17.2266 70.6289 15.0254 70.3418C13.877 70.2461 13.4941 69.7676 13.4941 69.1934C13.4941 68.0449 15.4082 67.1836 17.3223 67.1836C20.0977 67.1836 22.4902 68.9063 24.9785 72.4473C26.8926 75.2227 28.9023 76.4668 31.2949 76.4668C33.6875 76.4668 35.2187 75.6055 37.4199 73.4043C39.0469 71.7773 40.291 70.3418 41.4395 69.3848Z" fill="currentColor"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_730_27126">
                                <rect width="98" height="96" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </a>
                <div class="theme-switcher relative">
                    <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Theme" aria-haspopup="listbox" aria-expanded="false">
                        <svg data-mode="dark" viewBox="0 0 24 24" class="theme-icon w-[1.1rem] h-[1.1rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                            <path d="M12 3a6 6 0 1 0 6 7.5A6 6 0 0 1 12 3Z" />
                        </svg>
                    </button>
                    <ul id="theme-menu" class="theme-menu" role="listbox" hidden>
                        <li role="option" data-mode="light" tabindex="-1" aria-checked="false" data-label="Light">
                            <svg data-mode="light" viewBox="0 0 24 24" class="theme-icon w-[1.1rem] h-[1.1rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="4" />
                                <path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5.6 5.6 4.2 4.2M19.8 19.8l-1.4-1.4M5.6 18.4l-1.4 1.4M19.8 4.2l-1.4 1.4" />
                            </svg>
                            <span class="theme-option-label">Light</span>
                        </li>
                        <li role="option" data-mode="dark" tabindex="-1" aria-checked="false" data-label="Dark">
                            <svg data-mode="dark" viewBox="0 0 24 24" class="theme-icon w-[1.1rem] h-[1.1rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                                <path d="M12 3a6 6 0 1 0 6 7.5A6 6 0 0 1 12 3Z" />
                            </svg>
                            <span class="theme-option-label">Dark</span>
                        </li>
                        <li role="option" data-mode="auto" tabindex="-1" aria-checked="false" data-label="Auto">
                            <svg data-mode="auto" viewBox="0 0 24 24" class="theme-icon w-[1.1rem] h-[1.1rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="7" />
                                <path d="M12 5a7 7 0 0 1 0 14Z" fill="currentColor" stroke="none" />
                            </svg>
                            <span class="theme-option-label">Auto</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-5xl mx-auto w-full px-6 py-10">
        {{ $slot }}
    </main>

    <footer class="site-footer">
        <div class="max-w-5xl mx-auto px-6 py-5">
            <p class="font-mono text-xs text-muted">
                <span class="text-accent">cedriek@blog</span>:<span class="text-hi">~</span>$&nbsp;<span style="opacity: 0.45;">exit 0</span>
            </p>
        </div>
    </footer>

    <script>
        (function () {
            var container = document.querySelector('.theme-switcher');
            var toggle = document.getElementById('theme-toggle');
            var menu = document.getElementById('theme-menu');
            var options = Array.from(menu.querySelectorAll('[role="option"]'));

            function optionFor(mode) {
                return options.find(function (option) {
                    return option.dataset.mode === mode;
                });
            }

            function sync(mode) {
                mode = mode || window.__getThemeMode();
                toggle.querySelector('svg').replaceWith(optionFor(mode).querySelector('svg').cloneNode(true));
                options.forEach(function (option) {
                    var isActive = option.dataset.mode === mode;
                    option.setAttribute('aria-checked', isActive ? 'true' : 'false');
                    option.querySelector('.theme-option-label').textContent = option.dataset.label + (isActive ? ' ✓' : '');
                });
            }

            window.__syncThemeToggle = sync;
            sync();

            function openMenu() {
                menu.hidden = false;
                toggle.setAttribute('aria-expanded', 'true');
                (optionFor(window.__getThemeMode()) || options[0]).focus();
            }

            function closeMenu() {
                menu.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
                toggle.focus();
            }

            function selectMode(mode) {
                localStorage.setItem('theme', mode);
                window.__applyThemeMode(mode);
                closeMenu();
            }

            toggle.addEventListener('click', function () {
                if (toggle.getAttribute('aria-expanded') === 'true') {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            toggle.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ' || event.key === 'ArrowDown') {
                    event.preventDefault();
                    openMenu();
                }
            });

            options.forEach(function (option, index) {
                option.addEventListener('click', function () {
                    selectMode(option.dataset.mode);
                });

                option.addEventListener('keydown', function (event) {
                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        options[(index + 1) % options.length].focus();
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        options[(index - 1 + options.length) % options.length].focus();
                    } else if (event.key === 'Enter') {
                        event.preventDefault();
                        selectMode(option.dataset.mode);
                    } else if (event.key === 'Escape') {
                        event.preventDefault();
                        closeMenu();
                    }
                });
            });

            document.addEventListener('click', function (event) {
                if (toggle.getAttribute('aria-expanded') === 'true' && !container.contains(event.target)) {
                    closeMenu();
                }
            });
        }());
    </script>

</body>
</html>
