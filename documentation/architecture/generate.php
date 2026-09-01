#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Generates documentation/architecture/*.html from the source in app/.
 *
 * The pages are committed so they open with no build step, but every fact on
 * them — every class, edge, layer and vendor touch point — is parsed out of
 * app/ at generation time. Re-run after a refactor and the map stays honest.
 *
 * Usage: php documentation/architecture/generate.php [--dump]
 */
const ROOT = __DIR__.'/../../';
const OUT = __DIR__.'/';

/** Groups the top-level App\ segments into the four bands the map is drawn in. */
const BANDS = [
    'Blog' => 'domain',
    'Microblog' => 'domain',
    'About' => 'domain',
    'Scratchpad' => 'domain',
    'Security' => 'domain',
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
        'role' => roleOf($short, $layer, $domain, $ctor !== []),
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

/** The job a class does, inferred from its name and sub-namespace. */
function roleOf(string $short, string $layer, string $domain, bool $hasCollaborators): string
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
        $layer === '(root)' && $hasCollaborators => 'Domain service',
        $layer === '(root)' => 'Value object',
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

const PAGES = [
    'index.html' => 'Overview',
    'modules.html' => 'Module map',
    'anatomy.html' => 'Domain anatomy',
    'flows.html' => 'Flow traces',
    'dependencies.html' => 'Dependency graph',
];

function shell(string $current, string $heading, string $lede, string $body): string
{
    $title = PAGES[$current];
    $nav = '';

    foreach (PAGES as $file => $label) {
        $nav .= $file === $current
            ? '<span class="px-2.5 py-1 rounded bg-slate-900 text-white">'.h($label).'</span>'
            : '<a href="'.$file.'" class="px-2.5 py-1 rounded text-slate-500 hover:text-slate-900 hover:bg-slate-200/60 transition">'.h($label).'</a>';
    }

    $parts = explode(' ', SOURCE_COMMIT);
    $stamp = h(($parts[1] ?? '').' · app/ @ '.$parts[0]);

    return <<<HTML
    <!doctype html>
    <html lang="en">
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{$title} · cedriekvos blog architecture</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script type="module">
          import mermaid from "https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs";
          mermaid.initialize({ startOnLoad: true, theme: "neutral", securityLevel: "loose", flowchart: { curve: "basis", useMaxWidth: true } });
        </script>
        <style>
          .lbl { font-size: .625rem; letter-spacing: .12em; text-transform: uppercase; }
          html { scroll-behavior: smooth; }
          .mermaid { display: flex; justify-content: center; }
          .mermaid svg { max-width: 100%; height: auto; }
        </style>
      </head>
      <body class="bg-stone-50 text-slate-900 font-sans antialiased">
        <main class="max-w-5xl mx-auto px-6 py-12 sm:py-16 space-y-14">

          <nav class="flex flex-wrap items-center gap-1 text-xs font-medium -mx-1">{$nav}</nav>

          <header class="space-y-4">
            <div class="flex items-baseline justify-between flex-wrap gap-2">
              <h1 class="font-serif text-4xl tracking-tight">{$heading}</h1>
              <span class="font-mono text-xs text-slate-400">{$stamp}</span>
            </div>
            <p class="text-slate-600 leading-relaxed max-w-3xl">{$lede}</p>
          </header>

    {$body}

          <footer class="border-t border-slate-200 pt-6 text-xs text-slate-400 leading-relaxed space-y-1">
            <p>Generated from <code class="font-mono">app/</code> and <code class="font-mono">routes/</code> by <code class="font-mono">documentation/architecture/generate.php</code>. Do not hand-edit — re-run the script instead.</p>
            <p>Related: <a class="underline underline-offset-2 decoration-slate-300 hover:text-slate-600" href="../decisions/index.html">Architecture Decision Records</a> · <code class="font-mono">documentation/features/</code> · <code class="font-mono">documentation/leesmij/</code></p>
          </footer>
        </main>
      </body>
    </html>
    HTML;
}

function mermaid(string $definition): string
{
    return '<div class="rounded-xl border border-slate-200 bg-white p-5 overflow-x-auto"><pre class="mermaid">'.h($definition).'</pre></div>';
}

function section(string $label, string $heading, string $inner): string
{
    return <<<HTML
          <section class="space-y-5">
            <h2 class="lbl text-slate-400">{$label}</h2>
            <h3 class="font-serif text-2xl tracking-tight text-slate-900 -mt-3">{$heading}</h3>
    {$inner}
          </section>
    HTML;
}

// ------------------------------------------------------------------- entry --

/**
 * The pages are stamped with the last commit that touched the sources they
 * describe, not with HEAD or today's date. Stamping HEAD would dirty every page
 * on any commit anywhere in the repo; this way the output is a pure function of
 * app/ and routes/, and a regenerated page only differs when the map really has.
 */
define('SOURCE_COMMIT', trim((string) shell_exec(
    'git -C '.escapeshellarg(ROOT).' log -1 --format=%h%x20%ad --date=short -- app routes 2>/dev/null'
)) ?: 'unknown');

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

if (! is_dir(OUT)) {
    mkdir(OUT, 0755, true);
}

foreach (buildPages($classes, $routes) as $file => $html) {
    file_put_contents(OUT.$file, $html."\n");
    echo "wrote documentation/architecture/{$file}\n";
}
