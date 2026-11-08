#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Generates documentation/architecture/*.html from the source in app/, and
 * documentation/specifications/index.html, the feature trace, from the specs,
 * leesmij and acceptance tests, and documentation/assets/tokens.css, the
 * website's colour tokens, from resources/css/app.css.
 *
 * The pages are committed so they open with no build step, but every fact on
 * them — every class, edge, layer and vendor touch point, every scenario and
 * the test that checks it — is parsed out of the repository at generation time.
 * Re-run after a refactor or a spec change and the map stays honest.
 *
 * Usage: php documentation/tools/generate.php [--dump|--check]
 *
 * --check writes nothing: it exits non-zero when a committed page differs from
 * what the sources generate now, which is how composer qa keeps them current.
 */
const ROOT = __DIR__.'/../../';

/** Pages are keyed by their path under documentation/, so one run can write to several sections. */
const OUT = __DIR__.'/../';

/** Groups the top-level App\ segments into the four bands the map is drawn in. */
const BANDS = [
    'Blog' => 'domain',
    'Microblog' => 'domain',
    'About' => 'domain',
    'Scratchpad' => 'domain',
    'Pages' => 'domain',
    'Markdown' => 'kernel',
    'Http' => 'delivery',
    'Livewire' => 'delivery',
    'Console' => 'delivery',
    'Mail' => 'delivery',
    'View' => 'delivery',
    'Models' => 'platform',
    'Providers' => 'platform',
];

const BAND_LABELS = [
    'domain' => 'Bounded domains',
    'kernel' => 'Shared kernel',
    'delivery' => 'Delivery layer',
    'platform' => 'Platform',
];

// ---------------------------------------------------------------- scanning --

/**
 * @return array<string, array{fqcn:string, short:string, namespace:string, domain:string, layer:string, role:string, file:string, kind:string, imports:array<string,string>, deps:list<string>, vendor:list<string>, ctor:list<string>, extends:?string}>
 */
function scanClasses(): array
{
    $classes = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT.'app', FilesystemIterator::SKIP_DOTS));

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $parsed = parseFile($file->getPathname());

        if ($parsed !== null) {
            $classes[$parsed['fqcn']] = $parsed;
        }
    }

    ksort($classes);

    return resolveSameNamespaceReferences($classes);
}

/**
 * A class in the same namespace is referenced without a `use` statement and can
 * appear as a return type, a `new`, or a static call rather than an injected
 * parameter. Scan each file for its siblings' names so fan-in is not undercounted.
 *
 * @param  array<string, array>  $classes
 * @return array<string, array>
 */
function resolveSameNamespaceReferences(array $classes): array
{
    $byNamespace = [];

    foreach ($classes as $class) {
        $byNamespace[$class['namespace']][$class['short']] = $class['fqcn'];
    }

    foreach ($classes as $fqcn => $class) {
        $siblings = $byNamespace[$class['namespace']];
        unset($siblings[$class['short']]);

        if ($siblings === []) {
            continue;
        }

        $referenced = [];

        foreach (token_get_all((string) file_get_contents(ROOT.$class['file'])) as $token) {
            if (is_array($token) && $token[0] === T_STRING && isset($siblings[$token[1]])) {
                $referenced[] = $siblings[$token[1]];
            }
        }

        $classes[$fqcn]['deps'] = array_values(array_unique([...$class['deps'], ...$referenced]));
    }

    return $classes;
}

/**
 * @return array{fqcn:string, short:string, namespace:string, domain:string, layer:string, role:string, file:string, kind:string, imports:array<string,string>, deps:list<string>, vendor:list<string>, ctor:list<string>, extends:?string}|null
 */
function parseFile(string $path): ?array
{
    $source = file_get_contents($path);
    $tokens = token_get_all($source);

    $namespace = '';
    $imports = [];
    $short = null;
    $kind = 'class';
    $extends = null;

    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];

        if (! is_array($token)) {
            continue;
        }

        if ($token[0] === T_NAMESPACE) {
            $namespace = readName($tokens, $i);

            continue;
        }

        if ($token[0] === T_USE && $short === null) {
            $name = readName($tokens, $i);

            if ($name !== '') {
                $alias = readAlias($tokens, $i) ?? substr(strrchr($name, '\\') ?: '\\'.$name, 1);
                $imports[$alias] = $name;
            }

            continue;
        }

        if (in_array($token[0], [T_CLASS, T_INTERFACE, T_ENUM, T_TRAIT], true) && $short === null) {
            if ($token[0] === T_CLASS && isConstantFetch($tokens, $i)) {
                continue;
            }

            $kind = match ($token[0]) {
                T_INTERFACE => 'interface',
                T_ENUM => 'enum',
                T_TRAIT => 'trait',
                default => 'class',
            };
            $short = readName($tokens, $i);

            continue;
        }

        if ($token[0] === T_EXTENDS && $short !== null && $extends === null) {
            $extends = readName($tokens, $i);
        }
    }

    if ($short === null || $namespace === '') {
        return null;
    }

    $segments = explode('\\', $namespace);
    $layer = $segments[2] ?? '(root)';

    $deps = [];
    $vendor = [];

    foreach ($imports as $imported) {
        if (str_starts_with($imported, 'App\\')) {
            $deps[] = $imported;
        } else {
            $vendor[] = $imported;
        }
    }

    $domain = $segments[1] ?? 'App';
    $ctor = constructorDependencies($source, $imports, $namespace);

    return [
        'fqcn' => $namespace.'\\'.$short,
        'short' => $short,
        'namespace' => $namespace,
        'domain' => $domain,
        'layer' => $layer,
        'role' => roleOf($short, $layer, $domain, declaresPublicProperty($tokens)),
        'file' => 'app'.substr($path, strlen(ROOT.'app')),
        'kind' => $kind,
        'imports' => $imports,
        // Collaborators in the same namespace need no `use` statement, so the
        // import list alone undercounts. Union it with the injected types.
        'deps' => array_values(array_unique([...$deps, ...$ctor])),
        'vendor' => array_values(array_unique($vendor)),
        'ctor' => $ctor,
        'extends' => $extends === null ? null : ($imports[$extends] ?? $extends),
    ];
}

/** Distinguishes a `Foo::class` constant fetch from a real class declaration. */
/**
 * Whether the class exposes public state, promoted or declared. A `public`
 * modifier that reaches a `$variable` before `function`, `const` or `;` is a
 * property's.
 */
function declaresPublicProperty(array $tokens): bool
{
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        if (! is_array($tokens[$i]) || $tokens[$i][0] !== T_PUBLIC) {
            continue;
        }

        for ($j = $i + 1; $j < $count; $j++) {
            if ($tokens[$j] === ';' || (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_FUNCTION, T_CONST], true))) {
                break;
            }

            if (is_array($tokens[$j]) && $tokens[$j][0] === T_VARIABLE) {
                return true;
            }
        }
    }

    return false;
}

function isConstantFetch(array $tokens, int $i): bool
{
    for ($j = $i - 1; $j >= 0; $j--) {
        if (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        return is_array($tokens[$j]) && $tokens[$j][0] === T_DOUBLE_COLON;
    }

    return false;
}

function readName(array $tokens, int $i): string
{
    $name = '';
    $count = count($tokens);

    for ($j = $i + 1; $j < $count; $j++) {
        $token = $tokens[$j];

        if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            if ($name !== '') {
                break;
            }

            continue;
        }

        if (is_array($token) && in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NS_SEPARATOR], true)) {
            $name .= $token[1];

            continue;
        }

        break;
    }

    return ltrim($name, '\\');
}

function readAlias(array $tokens, int $i): ?string
{
    $count = count($tokens);

    for ($j = $i + 1; $j < $count; $j++) {
        $token = $tokens[$j];

        if (! is_array($token)) {
            if ($token === ';' || $token === ',' || $token === '{') {
                return null;
            }

            continue;
        }

        if ($token[0] === T_AS) {
            return readName($tokens, $j);
        }
    }

    return null;
}

const BUILTIN_TYPES = ['string', 'int', 'float', 'bool', 'array', 'callable', 'iterable', 'object', 'mixed', 'self', 'static', 'null', 'void'];

/**
 * Collaborators reached through injection — constructor promotion first, then
 * the method signatures Laravel resolves out of the container (__invoke, handle,
 * mount, render, boot). Together these are the real wiring graph.
 *
 * @param  array<string,string>  $imports
 * @return list<string>
 */
function constructorDependencies(string $source, array $imports, string $namespace): array
{
    preg_match_all('/function\s+(__construct|__invoke|handle|mount|render|boot|booted)\s*\((.*?)\)\s*(?::|\{)/s', $source, $matches, PREG_SET_ORDER);

    $resolved = [];

    foreach ($matches as $match) {
        preg_match_all('/(?:^|[,(])\s*(?:(?:public|protected|private|readonly)\s+)*\??([A-Za-z_\\\\][A-Za-z0-9_\\\\]*)\s+\$/m', ' '.$match[2], $types);

        foreach ($types[1] as $type) {
            if (in_array(strtolower($type), BUILTIN_TYPES, true)) {
                continue;
            }

            $fqcn = $imports[$type] ?? (str_contains($type, '\\') ? $type : $namespace.'\\'.$type);

            if (str_starts_with($fqcn, 'App\\')) {
                $resolved[] = $fqcn;
            }
        }
    }

    return array_values(array_unique($resolved));
}

/**
 * The job a class does, inferred from its name and sub-namespace. At a domain's
 * root, where no suffix names the job, the house value object is all public
 * data and no behaviour, so a class without public state is a service.
 */
function roleOf(string $short, string $layer, string $domain, bool $holdsData): string
{
    return match (true) {
        $domain === 'Models' => 'Eloquent model',
        $domain === 'Mail' => 'Mailable',
        $layer === 'Components' => 'View component',
        $short === 'Controller' => 'Base controller',
        str_ends_with($short, 'Controller') => 'HTTP entry point',
        str_ends_with($short, 'Command') => 'Console entry point',
        $layer === 'Admin' => 'Livewire entry point',
        str_ends_with($short, 'Factory') => 'Factory',
        str_ends_with($short, 'GetRepository') => 'Read repository',
        str_ends_with($short, 'WriteRepository') => 'Write repository',
        str_ends_with($short, 'Repository') => 'Repository facade',
        str_ends_with($short, 'Source') => 'Source',
        str_ends_with($short, 'Storage') => 'File storage',
        str_ends_with($short, 'Parser') => 'Parser',
        str_ends_with($short, 'Serializer') => 'Serializer',
        str_ends_with($short, 'Converter') => 'Converter',
        str_ends_with($short, 'Extension') => 'CommonMark extension',
        str_ends_with($short, 'Sorter') => 'Sorter',
        str_ends_with($short, 'Filter') => 'Filter',
        str_ends_with($short, 'Calculator') => 'Calculator',
        str_ends_with($short, 'Notifier') => 'Notifier',
        str_ends_with($short, 'Request') => 'Form request',
        str_ends_with($short, 'Provider') => 'Service provider',
        str_ends_with($short, 'Audit') => 'Process adapter',
        str_ends_with($short, 'Result') => 'Result object',
        $layer === '(root)' && $holdsData => 'Value object',
        $layer === '(root)' => 'Domain service',
        default => $layer,
    };
}

// ------------------------------------------------------------------ routes --

/**
 * Parses routes/*.php for the URIs that reach each entry-point class, tracking
 * `->prefix()` groups by brace depth. Deliberately does not boot the framework:
 * the generator must run in a bare worktree with no vendor/ directory.
 *
 * @return array<string, list<string>> short class name => list of "GET /uri"
 */
function routeMap(): array
{
    $routes = [];

    foreach (glob(ROOT.'routes/*.php') ?: [] as $path) {
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $stack = [];
        $depth = 0;
        $pending = null;

        foreach ($lines as $line) {
            if (preg_match('/Route::(get|post|put|patch|delete)\(\s*\'([^\']*)\'\s*,\s*\[?\s*([A-Za-z_][A-Za-z0-9_]*)::class/', $line, $match) === 1) {
                $segments = array_filter(array_map(static fn (string $part): string => trim($part, '/'), [...$stack, $match[2]]));
                $uri = '/'.implode('/', $segments);
                $routes[$match[3]][] = strtoupper($match[1]).' '.$uri;
            }

            if (preg_match('/Schedule::command\(\s*([A-Za-z_][A-Za-z0-9_]*)::class\s*\)->([a-zA-Z]+)\(/', $line, $match) === 1) {
                $routes[$match[1]][] = 'schedule: '.strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $match[2]));
            }

            if (preg_match('/->prefix\(\s*\'([^\']+)\'\s*\)/', $line, $match) === 1 && str_contains($line, '->group(')) {
                $pending = $match[1];
            }

            $stripped = preg_replace('/\'[^\']*\'/', '', $line);
            $depth += substr_count($stripped, '{') - substr_count($stripped, '}');

            foreach (array_keys($stack) as $openedAt) {
                if ($depth < $openedAt) {
                    unset($stack[$openedAt]);
                }
            }

            if ($pending !== null && $depth > 0) {
                $stack[$depth] = $pending;
                $pending = null;
            }
        }
    }

    return $routes;
}

// ----------------------------------------------------------------- helpers --

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES);
}

function shortName(string $fqcn): string
{
    return str_contains($fqcn, '\\') ? substr(strrchr($fqcn, '\\'), 1) : $fqcn;
}

/** A mermaid-safe node id for a fully qualified class or domain name. */
function nodeId(string $name): string
{
    return preg_replace('/[^A-Za-z0-9]/', '_', $name);
}

/** The composer package a vendor import belongs to, or null if uninteresting. */
function vendorPackage(string $fqcn): ?string
{
    return match (true) {
        str_starts_with($fqcn, 'League\\CommonMark') => 'league/commonmark',
        str_starts_with($fqcn, 'Symfony\\Component\\Yaml') => 'symfony/yaml',
        str_starts_with($fqcn, 'Tempest\\Highlight') => 'tempest/highlight',
        str_starts_with($fqcn, 'Livewire') => 'livewire/livewire',
        str_starts_with($fqcn, 'Illuminate') => 'laravel/framework',
        str_starts_with($fqcn, 'Carbon') => 'nesbot/carbon',
        default => null,
    };
}

// ---------------------------------------------------------------- analysis --

/**
 * @param  array<string, array>  $classes
 * @return array<string, list<array>>
 */
function groupByDomain(array $classes): array
{
    $grouped = [];

    foreach ($classes as $class) {
        $grouped[$class['domain']][] = $class;
    }

    uksort($grouped, static fn (string $a, string $b): int => [BANDS[$a] ?? 'zz', $a] <=> [BANDS[$b] ?? 'zz', $b]);

    return $grouped;
}

/**
 * Domain-to-domain edges, weighted by how many imports cross the boundary.
 *
 * @param  array<string, array>  $classes
 * @return array<string, array<string, int>>
 */
function domainEdges(array $classes): array
{
    $edges = [];

    foreach ($classes as $class) {
        foreach ($class['deps'] as $dependency) {
            $target = explode('\\', $dependency)[1] ?? null;

            if ($target === null || $target === $class['domain']) {
                continue;
            }

            $edges[$class['domain']][$target] = ($edges[$class['domain']][$target] ?? 0) + 1;
        }
    }

    return $edges;
}

/**
 * @param  array<string, array>  $classes
 * @return array<string, list<string>>
 */
function dependents(array $classes): array
{
    $inbound = [];

    foreach ($classes as $class) {
        foreach ($class['deps'] as $dependency) {
            $inbound[$dependency][] = $class['fqcn'];
        }
    }

    return $inbound;
}

/**
 * Walks the wiring graph outward from one entry point.
 *
 * @param  array<string, array>  $classes
 * @return list<array{0:string, 1:string}>
 */
function reachableFrom(array $classes, string $entry): array
{
    $edges = [];
    $seen = [];
    $queue = [$entry];

    while ($queue !== []) {
        $current = array_shift($queue);

        if (isset($seen[$current]) || ! isset($classes[$current])) {
            continue;
        }

        $seen[$current] = true;

        foreach ($classes[$current]['ctor'] as $collaborator) {
            $edges[] = [$current, $collaborator];
            $queue[] = $collaborator;
        }
    }

    return $edges;
}

// ----------------------------------------------------------------- render --

/**
 * The documentation's top-level sections, in menu order, keyed by folder. The
 * hand-written pages in decisions/, lessons/ and workflow/ and the hub at
 * documentation/index.html carry the same menu, so a change here goes there too.
 */
const SECTIONS = [
    'architecture' => 'Architecture',
    'specifications' => 'Specifications',
    'decisions' => 'Decisions',
    'lessons' => 'Lessons',
    'workflow' => 'Workflow outline',
];

/** The tabs within the Architecture section. */
const PAGES = [
    'index.html' => 'Overview',
    'modules.html' => 'Module map',
    'anatomy.html' => 'Domain anatomy',
    'flows.html' => 'Flow traces',
    'dependencies.html' => 'Dependency graph',
];

/** What the code pages are generated from. */
const APP_SOURCE = ['label' => 'app/', 'paths' => ['app', 'routes']];

/**
 * The pages are stamped with the last commit that touched the sources they
 * describe, not with HEAD or today's date. Stamping HEAD would dirty every page
 * on any commit anywhere in the repo; this way the output is a pure function of
 * its sources, and a regenerated page only differs when the map really has.
 *
 * @param  array{label:string, paths:list<string>}  $source
 */
function sourceStamp(array $source): string
{
    $paths = implode(' ', array_map(escapeshellarg(...), $source['paths']));
    $commit = trim((string) shell_exec(
        'git -C '.escapeshellarg(ROOT).' log -1 --format=%h%x20%ad --date=short -- '.$paths.' 2>/dev/null'
    )) ?: 'unknown';
    $parts = explode(' ', $commit);

    return ($parts[1] ?? '').' · '.$source['label'].' @ '.$parts[0];
}

/** The menu every documentation page starts with, the current section marked. */
function sectionMenu(string $current): string
{
    $links = '';

    foreach (SECTIONS as $folder => $label) {
        $links .= '<a href="../'.$folder.'/index.html"'.($folder === $current ? ' aria-current="page"' : '')
            .' class="text-muted hover:text-fg aria-[current=page]:text-accent">['.h(strtolower($label)).']</a>';
    }

    return $links;
}

/** The Architecture tabs, or nothing on a page outside that section. */
function architectureTabs(string $section, string $current): string
{
    if ($section !== 'architecture') {
        return '';
    }

    $tabs = '';

    foreach (PAGES as $file => $label) {
        $tabs .= $file === $current
            ? '<span aria-current="page" class="text-accent">['.h(strtolower($label)).']</span>'
            : '<a href="'.$file.'" class="text-muted hover:text-fg">['.h(strtolower($label)).']</a>';
    }

    return "\n      <nav aria-label=\"Architecture\" class=\"flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-xs\">{$tabs}</nav>\n";
}

/**
 * @param  string  $page  the page's path under documentation/, such as architecture/modules.html
 * @param  array{label:string, paths:list<string>}  $source
 */
function shell(string $page, string $heading, string $lede, string $body, array $source = APP_SOURCE): string
{
    $section = dirname($page);
    $file = basename($page);
    $title = $section === 'architecture'
        ? PAGES[$file].' · cedriekvos blog architecture'
        : SECTIONS[$section].' · cedriekvos blog';
    $menu = sectionMenu($section);
    $tabs = architectureTabs($section, $file);

    $stamp = h(sourceStamp($source));
    $paths = array_map(static fn (string $path): string => '<code class="font-mono">'.h($path).'/</code>', $source['paths']);
    $last = array_pop($paths);
    $generatedFrom = $paths === [] ? $last : implode(', ', $paths).' and '.$last;

    return <<<HTML
    <!doctype html>
    <html lang="en">
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{$title}</title>
        <link rel="stylesheet" href="../assets/tokens.css" />
        <link rel="stylesheet" href="../assets/docs.css" />
        <script src="../assets/docs.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
      </head>
      <body class="antialiased flex flex-col min-h-screen">
        <header class="site-header sticky top-0 z-10">
          <div class="max-w-5xl mx-auto px-6 py-4 flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
            <a href="../index.html" class="flex items-center gap-2 font-mono font-semibold text-sm text-fg hover:text-accent transition-colors leading-none"><svg viewBox="0 0 24 24" class="w-[1.1rem] h-[1.1rem] shrink-0" aria-hidden="true"><polygon class="fox-icon-a" points="2,3 9,8 12,5 12,22 3,15" /><polygon class="fox-icon-b" points="22,3 15,8 12,5 12,22 21,15" /></svg>~/documentation<span class="cursor"></span></a>
            <div class="flex items-center gap-4">
              <nav aria-label="Documentation" class="flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-xs">{$menu}</nav>
              <span class="h-[1.1rem] w-px shrink-0 bg-line" aria-hidden="true"></span>
              <div class="theme-switcher relative">
                <button id="theme-toggle" class="theme-toggle icon-hover-scale" type="button" aria-label="Theme" aria-haspopup="listbox" aria-expanded="false"><svg viewBox="0 0 24 24" class="theme-icon w-[1.1rem] h-[1.1rem] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="11" /></svg></button>
                <ul id="theme-menu" class="theme-menu" role="listbox" hidden><li role="option" data-mode="light" tabindex="-1" aria-checked="false" data-label="Light"><span class="theme-option-label">Light</span></li><li role="option" data-mode="dark" tabindex="-1" aria-checked="false" data-label="Dark"><span class="theme-option-label">Dark</span></li><li role="option" data-mode="auto" tabindex="-1" aria-checked="false" data-label="Auto"><span class="theme-option-label">Auto</span></li></ul>
              </div>
            </div>
          </div>
        </header>
        <main class="flex-1 w-full max-w-5xl mx-auto px-6 py-12 sm:py-16 space-y-14">
    {$tabs}
          <header class="space-y-4">
            <div class="flex items-baseline justify-between flex-wrap gap-2">
              <h1 class="font-mono font-bold text-2xl leading-snug">{$heading}</h1>
              <span data-stamp class="font-mono text-xs text-muted">{$stamp}</span>
            </div>
            <p class="text-muted leading-relaxed max-w-3xl">{$lede}</p>
          </header>

    {$body}

          <footer class="border-t border-line pt-6 text-xs text-muted leading-relaxed space-y-1">
            <p>Generated from {$generatedFrom} by <code class="font-mono">documentation/tools/generate.php</code>. Do not hand-edit — re-run the script instead.</p>
          </footer>
        </main>
        <footer class="site-footer">
          <div class="max-w-5xl mx-auto px-6 py-5">
            <p class="font-mono text-xs text-muted"><span class="text-accent">cedriek@blog</span>:<span class="text-hi">~/documentation/{$section}</span>$&nbsp;<span class="opacity-45">exit 0</span></p>
          </div>
        </footer>
      </body>
    </html>
    HTML;
}

/**
 * The website's colour tokens, copied out of resources/css/app.css: every
 * top-level `:root` and `.dark` block. Every documentation page links the
 * result, so a palette change on the site reaches the docs on the next run,
 * and --check fails until it has.
 */
function themeTokens(): string
{
    preg_match_all('/^(?::root|\.dark)\s*\{[^}]*\}/m', (string) file_get_contents(ROOT.'resources/css/app.css'), $blocks);

    return "/* Generated from resources/css/app.css by documentation/tools/generate.php. Do not hand-edit. */\n\n"
        .implode("\n\n", $blocks[0]);
}

/**
 * The stamp names the last commit that touched a page's sources, so it changes
 * the moment a regenerated page is committed. --check compares everything else.
 */
function withoutStamp(string $html): string
{
    return (string) preg_replace('/<span data-stamp[^>]*>[^<]*<\/span>/', '<span data-stamp></span>', $html);
}

function mermaid(string $definition): string
{
    return '<div class="rounded-lg border border-line bg-card p-5 overflow-x-auto"><pre class="mermaid">'.h($definition).'</pre></div>';
}

function section(string $label, string $heading, string $inner): string
{
    return <<<HTML
          <section class="space-y-5">
            <h2 class="lbl text-muted">{$label}</h2>
            <h3 class="font-mono font-bold text-xl text-fg -mt-3">{$heading}</h3>
    {$inner}
          </section>
    HTML;
}

// ------------------------------------------------------------------- entry --

$classes = scanClasses();
$routes = routeMap();

if (in_array('--dump', $argv, true)) {
    foreach ($classes as $class) {
        printf(
            "%-52s %-16s %-22s ctor:%s\n",
            $class['fqcn'],
            $class['domain'],
            $class['role'],
            implode(',', array_map(shortName(...), $class['ctor'])) ?: '-'
        );
    }

    print_r($routes);

    exit(0);
}

require __DIR__.'/pages.php';
require __DIR__.'/features.php';

$pages = array_map(static fn (string $html): string => $html."\n", [...buildPages($classes, $routes), 'assets/tokens.css' => themeTokens()]);

if (in_array('--check', $argv, true)) {
    $stale = array_keys(array_filter(
        $pages,
        static fn (string $html, string $file): bool => ! is_file(OUT.$file) || withoutStamp((string) file_get_contents(OUT.$file)) !== withoutStamp($html),
        ARRAY_FILTER_USE_BOTH,
    ));

    foreach ($stale as $file) {
        echo "out of date: documentation/{$file}\n";
    }

    if ($stale !== []) {
        echo "Run php documentation/tools/generate.php and commit the pages it writes.\n";

        exit(1);
    }

    echo "documentation/architecture, documentation/specifications and documentation/assets/tokens.css are up to date\n";

    exit(0);
}

foreach ($pages as $file => $html) {
    if (! is_dir(dirname(OUT.$file))) {
        mkdir(dirname(OUT.$file), 0755, true);
    }

    file_put_contents(OUT.$file, $html);
    echo "wrote documentation/{$file}\n";
}
