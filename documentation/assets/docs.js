/*
 * Shared by every documentation page; load it in <head>, before the Tailwind
 * browser build.
 *
 * 1. Applies the light, dark or auto theme before the first paint, with the
 *    same localStorage key and rules as the website's layout.
 * 2. Gives Tailwind the website's theme. The browser build only reads inline
 *    <style type="text/tailwindcss"> blocks and cannot import a file, so the
 *    block is added here once instead of being copied into every page. It
 *    mirrors the @theme block in resources/css/app.css, except that the fonts
 *    come from the system stacks in docs.css.
 * 3. Wires the theme toggle in the header, as the website's layout does.
 * 4. Renders Mermaid diagrams in the current theme, again on every switch.
 */
(function () {
    var STORAGE_KEY = 'theme';
    var MERMAID = 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';
    var mql = window.matchMedia('(prefers-color-scheme: dark)');
    var options = [];
    var diagrams = [];
    var mermaid = null;

    function getMode() {
        var stored = localStorage.getItem(STORAGE_KEY);
        return (stored === 'light' || stored === 'dark') ? stored : 'auto';
    }

    function applyMode(mode) {
        var isDark = mode === 'dark' || (mode === 'auto' && mql.matches);
        var changed = document.documentElement.classList.contains('dark') !== isDark;
        document.documentElement.classList.toggle('dark', isDark);
        syncToggle(mode);
        if (changed) {
            renderDiagrams();
        }
    }

    applyMode(getMode());
    mql.addEventListener('change', function () {
        if (getMode() === 'auto') {
            applyMode('auto');
        }
    });

    var theme = document.createElement('style');
    theme.type = 'text/tailwindcss';
    theme.textContent = [
        '@custom-variant dark (&:where(.dark, .dark *));',
        '@theme {',
        '    --font-mono: var(--t-font-mono);',
        '    --font-sans: var(--t-font-sans);',
        '    --color-fg: var(--t-fg);',
        '    --color-muted: var(--t-muted);',
        '    --color-accent: var(--t-accent);',
        '    --color-hi: var(--t-hi);',
        '    --color-surface: var(--t-surface);',
        '    --color-line: var(--t-line);',
        '    --color-base: var(--t-bg);',
        '    --color-card: var(--t-card);',
        '    --color-danger: var(--t-danger);',
        '    --color-warn: var(--t-warn);',
        '    --color-info: var(--t-info);',
        '}',
    ].join('\n');
    document.head.appendChild(theme);

    // ── Theme toggle ────────────────────────────────────────
    function syncToggle(mode) {
        options.forEach(function (option) {
            var isActive = option.dataset.mode === mode;
            option.setAttribute('aria-checked', isActive ? 'true' : 'false');
            option.querySelector('.theme-option-label').textContent = option.dataset.label + (isActive ? ' ✓' : '');
        });
    }

    function wireToggle() {
        var toggle = document.getElementById('theme-toggle');
        var menu = document.getElementById('theme-menu');

        if (!toggle || !menu) {
            return;
        }

        options = Array.from(menu.querySelectorAll('[role="option"]'));
        syncToggle(getMode());

        function optionFor(mode) {
            return options.find(function (option) {
                return option.dataset.mode === mode;
            });
        }

        function openMenu() {
            menu.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
            (optionFor(getMode()) || options[0]).focus();
        }

        function closeMenu() {
            menu.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }

        function selectMode(mode) {
            localStorage.setItem(STORAGE_KEY, mode);
            applyMode(mode);
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
            if (!menu.hidden && !event.target.closest('.theme-switcher')) {
                menu.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ── Mermaid ─────────────────────────────────────────────
    function token(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    function renderDiagrams() {
        if (diagrams.length === 0) {
            return;
        }

        mermaid = mermaid || import(MERMAID).then(function (module) {
            return module.default;
        });

        mermaid.then(function (instance) {
            instance.initialize({
                startOnLoad: false,
                theme: 'base',
                securityLevel: 'loose',
                flowchart: { curve: 'basis', useMaxWidth: true },
                themeVariables: {
                    darkMode: document.documentElement.classList.contains('dark'),
                    fontFamily: token('--t-font-mono'),
                    fontSize: '13px',
                    background: token('--t-card'),
                    primaryColor: token('--t-card'),
                    primaryTextColor: token('--t-fg'),
                    primaryBorderColor: token('--t-line'),
                    secondaryColor: token('--t-surface'),
                    tertiaryColor: token('--t-surface'),
                    mainBkg: token('--t-card'),
                    nodeBorder: token('--t-line'),
                    clusterBkg: token('--t-surface'),
                    clusterBorder: token('--t-line'),
                    titleColor: token('--t-muted'),
                    lineColor: token('--t-muted'),
                    textColor: token('--t-fg'),
                    edgeLabelBackground: token('--t-card'),
                },
            });

            diagrams.forEach(function (diagram) {
                diagram.node.removeAttribute('data-processed');
                diagram.node.textContent = diagram.source;
            });

            return instance.run({
                nodes: diagrams.map(function (diagram) {
                    return diagram.node;
                }),
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        wireToggle();

        diagrams = Array.from(document.querySelectorAll('pre.mermaid')).map(function (node) {
            return { node: node, source: node.textContent };
        });
        renderDiagrams();
    });
}());
