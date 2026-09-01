<?php

declare(strict_types=1);

/**
 * Page builders for documentation/architecture/generate.php. Every string here is
 * assembled from the parsed model — nothing on the rendered pages is asserted
 * by hand, so a refactor that moves a class shows up on the next run.
 */

/** The slots a bounded domain in this codebase is expected to fill, in pipeline order. */
const CANONICAL_ROLES = [
    'Value object' => 'the immutable thing the domain is about',
    'Factory' => 'builds it from already-parsed data',
    'Repository facade' => 'the single seam the delivery layer is allowed to see',
    'Read repository' => 'queries',
    'Write repository' => 'mutations',
    'Source' => 'loads and assembles every record from storage',
    'File storage' => 'the only class that touches the disk',
    'Parser' => 'file text into structured data',
    'Serializer' => 'structured data back into file text',
    'Converter' => 'markdown or plain text into HTML',
    'Sorter' => 'ordering',
    'Filter' => 'visibility rules',
];

/** Mermaid classDef names are namespaced so they cannot collide with subgraph ids. */
const BAND_STYLES = [
    'bandDomain' => 'fill:#ffffff,stroke:#0f172a,stroke-width:1.5px,color:#0f172a',
    'bandKernel' => 'fill:#ecfdf5,stroke:#059669,stroke-width:1.5px,color:#064e3b',
    'bandDelivery' => 'fill:#eff6ff,stroke:#2563eb,stroke-width:1.5px,color:#1e3a8a',
    'bandPlatform' => 'fill:#f8fafc,stroke:#94a3b8,stroke-width:1.5px,color:#475569',
];

const BAND_CHIPS = [
    'domain' => 'bg-slate-900 text-white',
    'kernel' => 'bg-emerald-100 text-emerald-800',
    'delivery' => 'bg-blue-100 text-blue-800',
    'platform' => 'bg-slate-200 text-slate-600',
];

/**
 * @param  array<string, array>  $classes
 * @param  array<string, list<string>>  $routes
 * @return array<string, string>
 */
function buildPages(array $classes, array $routes): array
{
    $byDomain = groupByDomain($classes);
    $edges = domainEdges($classes);
    $inbound = dependents($classes);

    return [
        'index.html' => buildIndex($classes, $byDomain, $edges),
        'modules.html' => buildModules($classes, $byDomain, $edges),
        'anatomy.html' => buildAnatomy($classes, $byDomain),
        'flows.html' => buildFlows($classes, $routes),
        'dependencies.html' => buildDependencies($classes, $byDomain, $inbound),
    ];
}

// ------------------------------------------------------------------ shared --

/** @return list<string> composer packages required at runtime */
function runtimePackages(): array
{
    $composer = json_decode((string) file_get_contents(ROOT.'composer.json'), true);
    $packages = array_keys($composer['require'] ?? []);

    return array_values(array_filter($packages, static fn (string $name): bool => $name !== 'php'));
}

function card(string $inner, string $extra = ''): string
{
    return '<div class="rounded-xl border border-slate-200 bg-white px-5 py-4 '.$extra.'">'.$inner.'</div>';
}

function code(string $text): string
{
    return '<code class="font-mono text-[0.875em] bg-slate-100 text-slate-800 rounded px-1 py-0.5">'.h($text).'</code>';
}

function chip(string $text, string $classes): string
{
    return '<span class="text-[11px] font-semibold px-2 py-0.5 rounded '.$classes.'">'.h($text).'</span>';
}

function paragraph(string $text): string
{
    return '<p class="text-slate-600 leading-relaxed max-w-3xl">'.$text.'</p>';
}

/** Bands are ranked so an edge can be classified as downward, lateral or upward. */
const BAND_RANK = ['delivery' => 0, 'domain' => 1, 'kernel' => 2, 'platform' => 3];

/**
 * Splits the module edges into the ones that respect the layering and the ones
 * that do not. Nothing here is a verdict — an upward edge may be entirely
 * deliberate — but they are the edges worth knowing about.
 *
 * @param  array<string, array<string, int>>  $edges
 * @return array{downward:list<array{0:string,1:string,2:int}>, lateral:list<array{0:string,1:string,2:int}>, upward:list<array{0:string,1:string,2:int}>}
 */
function classifyEdges(array $edges): array
{
    $classified = ['downward' => [], 'lateral' => [], 'upward' => []];

    foreach ($edges as $from => $targets) {
        foreach ($targets as $to => $weight) {
            $source = BAND_RANK[BANDS[$from] ?? 'platform'];
            $target = BAND_RANK[BANDS[$to] ?? 'platform'];

            $kind = match (true) {
                $target > $source => 'downward',
                $target === $source => 'lateral',
                default => 'upward',
            };

            $classified[$kind][] = [$from, $to, $weight];
        }
    }

    return $classified;
}

/**
 * The individual imports behind one module-level edge.
 *
 * @param  array<string, array>  $classes
 * @return list<array{0:string, 1:string}>
 */
function crossingsFor(array $classes, string $from, string $to): array
{
    $crossings = [];

    foreach ($classes as $class) {
        if ($class['domain'] !== $from) {
            continue;
        }

        foreach ($class['deps'] as $dependency) {
            if ((explode('\\', $dependency)[1] ?? '') === $to) {
                $crossings[] = [$class['fqcn'], $dependency];
            }
        }
    }

    return $crossings;
}

/** @param array<string, array> $classes */
function entryPoints(array $classes): array
{
    return array_filter($classes, static fn (array $class): bool => str_ends_with($class['role'], 'entry point'));
}

// ------------------------------------------------------------------- index --

function buildIndex(array $classes, array $byDomain, array $edges): string
{
    $domains = array_keys(array_filter($byDomain, static fn (array $_, string $name): bool => (BANDS[$name] ?? null) === 'domain', ARRAY_FILTER_USE_BOTH));
    $packages = runtimePackages();
    $entries = entryPoints($classes);

    $stats = [
        ['classes in app/', (string) count($classes)],
        ['bounded domains', (string) count($domains)],
        ['entry points', (string) count($entries)],
        ['runtime packages', (string) count($packages)],
    ];

    $statCards = '';

    foreach ($stats as [$label, $value]) {
        $statCards .= card('<div class="font-serif text-3xl tracking-tight">'.h($value).'</div><div class="lbl text-slate-400 mt-1">'.h($label).'</div>');
    }

    $guide = [
        ['modules.html', 'Module map', 'What are the pieces, and which one is allowed to know about which?', 'Start here. Domains, the shared kernel, the delivery layer, and every arrow that crosses a boundary.'],
        ['anatomy.html', 'Domain anatomy', 'Once I understand one domain, do I understand them all?', 'Mostly yes — the same value-object → repository → storage slice repeats. The interesting part is the slots each domain leaves empty.'],
        ['flows.html', 'Flow traces', 'What actually happens when someone hits a URL?', 'Every route and scheduled command, traced through the objects the container wires up behind it.'],
        ['dependencies.html', 'Dependency graph', 'What breaks if I change this class or drop this package?', 'Class-level fan-in and fan-out, plus the blast radius of each composer package.'],
    ];

    $guideCards = '';

    foreach ($guide as [$href, $title, $question, $body]) {
        $guideCards .= '<a href="'.$href.'" class="group block rounded-xl border border-slate-200 bg-white px-5 py-4 hover:border-slate-400 hover:shadow-sm transition">'
            .'<h3 class="font-serif text-lg text-slate-900 group-hover:underline decoration-slate-300 underline-offset-4">'.h($title).'</h3>'
            .'<p class="text-sm font-medium text-slate-900 mt-1.5">'.h($question).'</p>'
            .'<p class="text-sm text-slate-600 mt-1 leading-relaxed">'.h($body).'</p></a>';
    }

    $domainList = implode(', ', array_map(static fn (string $name): string => code('App\\'.$name), $domains));
    $packageList = implode(' · ', array_map(code(...), $packages));

    $overview = mermaid(bandDiagram($byDomain, $edges, false));

    $body = section('At a glance', 'A flat-file Laravel application', implode("\n", [
        '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">'.$statCards.'</div>',
        paragraph('There is no content database. Every post, message, bio and scratchpad note is a Markdown file on disk, read and written through a per-domain repository. The '.count($domains).' bounded domains are '.$domainList.'; they share one kernel, '.code('App\Markdown').', and each exposes a single repository facade to the layer above.'),
        paragraph('The whole thing runs on '.count($packages).' runtime packages: '.$packageList.'.'),
        $overview,
    ]))."\n\n".section('Where to go', 'Four views, four questions', '<div class="grid sm:grid-cols-2 gap-3">'.$guideCards.'</div>');

    return shell(
        'index.html',
        'Architecture',
        'A map of this codebase for reading rather than browsing — what the pieces are, how one domain is shaped, what happens on a request, and what depends on what. Every page is generated from the source.',
        $body
    );
}

/**
 * Modules that take part in at least one edge. Drawing the isolated ones only
 * pushes the connected graph around, so they are listed in prose instead.
 *
 * @return array<string, list<array>>
 */
function connectedModules(array $byDomain, array $edges): array
{
    $connected = [];

    foreach ($edges as $from => $targets) {
        $connected[$from] = true;

        foreach (array_keys($targets) as $to) {
            $connected[$to] = true;
        }
    }

    return array_filter($byDomain, static fn (array $_, string $name): bool => isset($connected[$name]), ARRAY_FILTER_USE_BOTH);
}

function isolatedNote(array $byDomain, array $edges): string
{
    $isolated = isolatedModules($byDomain, $edges);

    if ($isolated === []) {
        return '';
    }

    $names = implode(', ', array_map(static fn (string $name): string => code('App\\'.$name), $isolated));

    return '<p class="text-sm text-slate-500 leading-relaxed max-w-3xl">Left off the diagram because '
        .(count($isolated) === 1 ? 'it imports' : 'they import').' nothing under '.code('App\\')
        .' and nothing imports '.(count($isolated) === 1 ? 'it' : 'them').': '.$names
        .'. Laravel resolves '.(count($isolated) === 1 ? 'it' : 'them').' directly.</p>';
}

/** Modules with no incoming or outgoing edges at all. */
function isolatedModules(array $byDomain, array $edges): array
{
    return array_keys(array_diff_key($byDomain, connectedModules($byDomain, $edges)));
}

/**
 * Pairs of modules that import each other. A module-level cycle is the one
 * finding on this page that is unambiguously worth knowing about.
 *
 * @return list<array{0:string, 1:string}>
 */
function moduleCycles(array $edges): array
{
    $cycles = [];

    foreach ($edges as $from => $targets) {
        foreach (array_keys($targets) as $to) {
            if (isset($edges[$to][$from]) && ! in_array([$to, $from], $cycles, true)) {
                $cycles[] = [$from, $to];
            }
        }
    }

    return $cycles;
}

/**
 * The four-band overview: delivery on top, domains beneath, kernel and platform
 * below that. With $weighted the arrows carry their import counts.
 */
function bandDiagram(array $byDomain, array $edges, bool $weighted): string
{
    $lines = ['flowchart TD'];
    $bands = [];

    foreach (array_keys(connectedModules($byDomain, $edges)) as $domain) {
        $bands[BANDS[$domain] ?? 'platform'][] = $domain;
    }

    foreach (['delivery', 'domain', 'kernel', 'platform'] as $band) {
        if (! isset($bands[$band])) {
            continue;
        }

        $lines[] = '  subgraph band_'.$band.'["'.BAND_LABELS[$band].'"]';

        foreach ($bands[$band] as $domain) {
            $count = count($byDomain[$domain]);
            $lines[] = '    '.nodeId($domain).'["<b>'.$domain.'</b><br/>'.$count.' '.($count === 1 ? 'class' : 'classes').'"]';
        }

        $lines[] = '  end';
    }

    foreach ($edges as $from => $targets) {
        foreach ($targets as $to => $weight) {
            $arrow = $weighted ? ' -->|'.$weight.'| ' : ' --> ';
            $lines[] = '  '.nodeId($from).$arrow.nodeId($to);
        }
    }

    foreach (array_keys(connectedModules($byDomain, $edges)) as $domain) {
        $lines[] = '  class '.nodeId($domain).' band'.ucfirst(BANDS[$domain] ?? 'platform');
    }

    foreach (BAND_STYLES as $name => $style) {
        $lines[] = '  classDef '.$name.' '.$style;
    }

    return implode("\n", $lines);
}

// ----------------------------------------------------------------- modules --

function buildModules(array $classes, array $byDomain, array $edges): string
{
    $inboundWeights = [];

    foreach ($edges as $from => $targets) {
        foreach ($targets as $to => $weight) {
            $inboundWeights[$to] = ($inboundWeights[$to] ?? 0) + $weight;
        }
    }

    $graph = mermaid(bandDiagram($byDomain, $edges, true));

    $bandSections = '';

    foreach (['domain', 'kernel', 'delivery', 'platform'] as $band) {
        $members = array_filter($byDomain, static fn (array $_, string $name): bool => (BANDS[$name] ?? 'platform') === $band, ARRAY_FILTER_USE_BOTH);

        if ($members === []) {
            continue;
        }

        $cards = '';

        foreach ($members as $domain => $domainClasses) {
            $out = array_sum($edges[$domain] ?? []);
            $in = $inboundWeights[$domain] ?? 0;
            $roles = array_values(array_unique(array_column($domainClasses, 'role')));
            sort($roles);

            $outgoing = $edges[$domain] ?? [];
            arsort($outgoing);

            $dependsOn = $outgoing === []
                ? '<span class="text-slate-400">nothing outside itself</span>'
                : implode(' ', array_map(
                    static fn (string $target, int $weight): string => chip($target.' ×'.$weight, 'bg-slate-100 text-slate-600'),
                    array_keys($outgoing),
                    $outgoing
                ));

            $cards .= card(
                '<div class="flex items-baseline gap-3 flex-wrap">'
                .'<h4 class="font-serif text-lg text-slate-900">App\\'.h($domain).'</h4>'
                .chip(count($domainClasses).' '.(count($domainClasses) === 1 ? 'class' : 'classes'), 'bg-slate-100 text-slate-600')
                .'<span class="font-mono text-[11px] text-slate-400 ml-auto">fan-out '.$out.' · fan-in '.$in.'</span>'
                .'</div>'
                .'<div class="mt-3 lbl text-slate-400">Depends on</div>'
                .'<div class="mt-1 flex flex-wrap gap-1">'.$dependsOn.'</div>'
                .'<div class="mt-3 lbl text-slate-400">Roles present</div>'
                .'<div class="mt-1 flex flex-wrap gap-1">'.implode(' ', array_map(static fn (string $role): string => chip($role, 'bg-white border border-slate-200 text-slate-600'), $roles)).'</div>'
            );
        }

        $bandSections .= section(
            BAND_LABELS[$band],
            bandHeading($band),
            '<div class="grid gap-3">'.$cards.'</div>'
        )."\n\n";
    }

    $crossings = crossingTable($classes);

    $body = section('The map', 'Every module and every arrow', implode("\n", [
        paragraph('Each node is a top-level namespace under '.code('App\\').'. An arrow means at least one class in the source module names a class in the target — an import, an injected constructor parameter, or a type hint; the number is how many distinct class-to-class references exist.'),
        $graph,
        isolatedNote($byDomain, $edges),
        layeringCallout($classes, $edges),
    ]))."\n\n".$bandSections.section('Boundary crossings', 'Every reference that leaves its module', implode("\n", [
        paragraph('The complete list, so a new arrow on the diagram above can always be traced to a line of code.'),
        $crossings,
    ]));

    return shell(
        'modules.html',
        'Module map',
        'What the pieces are and which one is allowed to know about which. Modules are the top-level namespaces under '.code('App\\').'; edges are real import statements, counted.',
        $body
    );
}

/**
 * Names the edges that do not run delivery → domain → kernel, and points at the
 * exact imports responsible. Generated, so it cannot drift from the diagram.
 */
function layeringCallout(array $classes, array $edges): string
{
    $classified = classifyEdges($edges);
    $notable = [...$classified['lateral'], ...$classified['upward']];
    $cycles = moduleCycles($edges);

    $cycleNote = '';

    foreach ($cycles as [$from, $to]) {
        $cycleNote .= '<div class="rounded-lg border border-red-200 bg-red-50/70 px-5 py-4 text-sm text-red-900 mt-3">'
            .'<strong class="font-semibold">App\\'.h($from).' and App\\'.h($to).' import each other.</strong> '
            .'A module-level cycle: neither can be understood, moved or tested without the other.'
            .'<div class="mt-2 font-mono text-[12px] text-red-900/70 space-y-0.5">'
            .implode('', array_map(
                static fn (array $pair): string => '<div>'.h(shortName($pair[0])).' imports '.h(shortName($pair[1])).'</div>',
                [...crossingsFor($classes, $from, $to), ...crossingsFor($classes, $to, $from)]
            ))
            .'</div></div>';
    }

    if ($notable === []) {
        return '<div class="rounded-lg border border-emerald-200 bg-emerald-50/70 px-5 py-4 text-sm leading-relaxed text-emerald-900">'
            .'<strong class="font-semibold">Every arrow points downward.</strong> The delivery layer knows about domains, domains know about the kernel, and nothing points back.'
            .'</div>'.$cycleNote;
    }

    $items = '';

    foreach ($notable as [$from, $to, $weight]) {
        $kind = (BANDS[$from] ?? '') === (BANDS[$to] ?? '') ? 'sideways, between two modules in the same band' : 'upward, out of its band';
        $crossings = crossingsFor($classes, $from, $to);

        $items .= '<li class="leading-relaxed">'
            .'<span class="font-mono text-[13px] font-semibold">App\\'.h($from).' &rarr; App\\'.h($to).'</span> '
            .'<span class="text-amber-800/70">('.h($kind).')</span>'
            .'<div class="mt-1 font-mono text-[12px] text-amber-900/70 space-y-0.5">'
            .implode('', array_map(
                static fn (array $pair): string => '<div>'.h(shortName($pair[0])).' imports '.h(shortName($pair[1])).'</div>',
                $crossings
            ))
            .'</div></li>';
    }

    return '<div class="rounded-lg border border-amber-200 bg-amber-50/70 px-5 py-4 text-sm text-amber-900">'
        .'<strong class="font-semibold">'.count($notable).' of the '.(count($classified['downward']) + count($notable)).' arrows do not point straight down.</strong> '
        .'Not necessarily wrong — but these are the couplings that make two modules hard to move independently.'
        .'<ul class="mt-3 space-y-2.5 list-disc pl-5">'.$items.'</ul>'
        .'</div>'.$cycleNote;
}

function bandHeading(string $band): string
{
    return match ($band) {
        'domain' => 'Self-contained, file-backed, and unaware of HTTP',
        'kernel' => 'Shared by every domain that stores Markdown',
        'delivery' => 'The only layer that knows about requests',
        default => 'Framework wiring',
    };
}

function crossingTable(array $classes): string
{
    $rows = '';

    foreach ($classes as $class) {
        foreach ($class['deps'] as $dependency) {
            $target = explode('\\', $dependency)[1] ?? '';

            if ($target === $class['domain']) {
                continue;
            }

            $rows .= '<tr class="border-t border-slate-100">'
                .'<td class="py-1.5 pr-4 font-mono text-[13px] text-slate-700">'.h($class['fqcn']).'</td>'
                .'<td class="py-1.5 pr-4 text-slate-300">&rarr;</td>'
                .'<td class="py-1.5 font-mono text-[13px] text-slate-700">'.h($dependency).'</td>'
                .'</tr>';
        }
    }

    return '<div class="rounded-xl border border-slate-200 bg-white px-5 py-4 overflow-x-auto"><table class="w-full text-left"><tbody>'.$rows.'</tbody></table></div>';
}

// ----------------------------------------------------------------- anatomy --

function buildAnatomy(array $classes, array $byDomain): string
{
    $reference = 'Blog';
    $slice = mermaid(domainWiringDiagram($classes, $reference));

    $matrixDomains = array_keys(array_filter(
        $byDomain,
        static fn (array $_, string $name): bool => in_array(BANDS[$name] ?? '', ['domain', 'kernel'], true),
        ARRAY_FILTER_USE_BOTH
    ));

    $head = '<th class="py-2 pr-3 text-left lbl text-slate-400 w-[9rem]">Slot</th>';

    foreach ($matrixDomains as $domain) {
        $band = (BANDS[$domain] ?? '') === 'kernel' ? '<span class="block font-normal normal-case tracking-normal text-[10px] text-emerald-600">shared kernel</span>' : '';
        $head .= '<th class="py-2 px-2 text-left lbl text-slate-500 align-bottom">'.h($domain).$band.'</th>';
    }

    $rows = '';

    foreach (CANONICAL_ROLES as $role => $purpose) {
        $rows .= '<tr class="border-t border-slate-100 align-top">'
            .'<td class="py-2 pr-3"><div class="font-medium text-[13px] text-slate-900 leading-snug">'.h($role).'</div><div class="text-[11px] text-slate-500 leading-snug">'.h($purpose).'</div></td>';

        foreach ($matrixDomains as $domain) {
            $filled = array_values(array_filter($byDomain[$domain], static fn (array $class): bool => $class['role'] === $role));

            $rows .= '<td class="py-2 px-2 text-[11px] font-mono leading-tight break-words">'
                .($filled === []
                    ? '<span class="text-slate-300">&mdash;</span>'
                    : implode('<br/>', array_map(static fn (array $class): string => '<span class="text-slate-700">'.h($class['short']).'</span>', $filled)))
                .'</td>';
        }

        $rows .= '</tr>';
    }

    $matrix = '<div class="xl:-mx-24"><div class="rounded-xl border border-slate-200 bg-white px-5 py-4 overflow-x-auto"><table class="w-full border-collapse table-fixed"><thead><tr>'.$head.'</tr></thead><tbody>'.$rows.'</tbody></table></div></div>';

    $deviations = '';

    foreach ($matrixDomains as $domain) {
        $present = array_unique(array_column($byDomain[$domain], 'role'));
        $missing = array_values(array_diff(array_keys(CANONICAL_ROLES), $present));
        $extra = array_values(array_diff($present, array_keys(CANONICAL_ROLES)));

        $deviations .= card(
            '<h4 class="font-serif text-lg text-slate-900">App\\'.h($domain).'</h4>'
            .'<div class="mt-3 grid sm:grid-cols-2 gap-3 text-sm">'
            .'<div><div class="lbl text-slate-400 mb-1">Slots left empty</div>'
            .($missing === [] ? '<span class="text-emerald-700">fills every slot</span>' : '<div class="flex flex-wrap gap-1">'.implode(' ', array_map(static fn (string $role): string => chip($role, 'bg-slate-100 text-slate-500'), $missing)).'</div>')
            .'</div>'
            .'<div><div class="lbl text-slate-400 mb-1">Beyond the pattern</div>'
            .($extra === [] ? '<span class="text-slate-400">nothing</span>' : '<div class="flex flex-wrap gap-1">'.implode(' ', array_map(static fn (string $role): string => chip($role, 'bg-amber-100 text-amber-800'), $extra)).'</div>')
            .'</div></div>'
        );
    }

    $perDomain = '';

    foreach ($matrixDomains as $domain) {
        $perDomain .= '<div class="space-y-2"><h4 class="font-serif text-lg text-slate-900">App\\'.h($domain).'</h4>'
            .mermaid(domainWiringDiagram($classes, $domain)).'</div>';
    }

    $body = section('The pattern', 'One slice, repeated five times', implode("\n", [
        paragraph('Every file-backed domain is built from the same parts, wired by constructor injection. '.code('App\Blog').' is the most complete instance, so read it as the reference: the facade is the only class the delivery layer touches, and the storage class is the only one that touches disk.'),
        $slice,
    ]))."\n\n".section('The matrix', 'Which domain fills which slot', implode("\n", [
        paragraph('Rows are the slots in the pattern above; columns are the modules that implement it. Empty cells are the point of this table — they show where a domain is simpler than the reference, not where it is missing something.'),
        $matrix,
    ]))."\n\n".section('Deviations', 'Where each domain departs from the pattern', '<div class="grid gap-3">'.$deviations.'</div>')
    ."\n\n".section('Per module', 'The same diagram for every domain', '<div class="space-y-8">'.$perDomain.'</div>');

    return shell(
        'anatomy.html',
        'Domain anatomy',
        'Learn one domain and you have mostly learned all of them. This page shows the repeated slice, then exactly where each module deviates from it.',
        $body
    );
}

/** Intra-module wiring, drawn from constructor injection only. */
function domainWiringDiagram(array $classes, string $domain): string
{
    $members = array_filter($classes, static fn (array $class): bool => $class['domain'] === $domain);
    $lines = ['flowchart LR'];

    foreach ($members as $class) {
        $lines[] = '  '.nodeId($class['fqcn']).'["'.$class['short'].'<br/><i>'.$class['role'].'</i>"]';
    }

    foreach ($members as $class) {
        foreach ($class['ctor'] as $collaborator) {
            if (! isset($classes[$collaborator])) {
                continue;
            }

            if ($classes[$collaborator]['domain'] !== $domain) {
                $lines[] = '  '.nodeId($collaborator).'["'.$classes[$collaborator]['short'].'<br/><i>App\\\\'.$classes[$collaborator]['domain'].'</i>"]';
                $lines[] = '  class '.nodeId($collaborator).' external';
            }

            $lines[] = '  '.nodeId($class['fqcn']).' --> '.nodeId($collaborator);
        }
    }

    $lines[] = '  classDef external fill:#ecfdf5,stroke:#059669,color:#064e3b';

    return implode("\n", array_unique($lines));
}

// ------------------------------------------------------------------- flows --

function buildFlows(array $classes, array $routes): string
{
    $groups = [
        'HTTP entry point' => ['Public web', 'A visitor hits a URL. The controller is invoked, the container resolves its collaborators, and a Blade view is returned.'],
        'Livewire entry point' => ['Admin', 'An authenticated editor loads a Livewire component. The component holds no domain logic of its own — it injects a repository facade and hands off.'],
        'Console entry point' => ['Scheduled', 'No request involved — the scheduler invokes the command.'],
    ];

    $body = '';

    foreach ($groups as $role => [$heading, $lede]) {
        $entries = array_filter($classes, static fn (array $class): bool => $class['role'] === $role);

        if ($entries === []) {
            continue;
        }

        $traces = '';

        foreach ($entries as $entry) {
            $edges = reachableFrom($classes, $entry['fqcn']);
            $uris = $routes[$entry['short']] ?? [];

            $uriChips = $uris === []
                ? '<span class="text-xs text-slate-400">no route found in routes/</span>'
                : implode(' ', array_map(static fn (string $uri): string => '<span class="font-mono text-[11px] px-2 py-0.5 rounded bg-slate-900 text-white">'.h($uri).'</span>', $uris));

            $touched = [];

            foreach ($edges as [$from, $to]) {
                $touched[$to] = true;
            }

            $diagram = $edges === []
                ? '<p class="text-sm text-slate-500 leading-relaxed">Injects nothing — it renders a view or issues a redirect on its own.</p>'
                : mermaid(traceDiagram($classes, $entry['fqcn'], $edges));

            $traces .= card(
                '<div class="flex items-baseline gap-3 flex-wrap">'
                .'<h4 class="font-serif text-lg text-slate-900">'.h($entry['short']).'</h4>'
                .$uriChips
                .'<span class="font-mono text-[11px] text-slate-400 ml-auto">'.h($entry['file']).'</span>'
                .'</div>'
                .'<p class="text-sm text-slate-500 mt-2">Reaches '.count($touched).' '.(count($touched) === 1 ? 'class' : 'classes').' through injection.</p>'
                .'<div class="mt-3">'.$diagram.'</div>'
            );
        }

        $body .= section($heading, $heading.' entry points', implode("\n", [paragraph($lede), '<div class="grid gap-3">'.$traces.'</div>']))."\n\n";
    }

    return shell(
        'flows.html',
        'Flow traces',
        'What actually happens when someone hits a URL. Each trace starts at an entry point and follows constructor and method injection outward — this is the object graph the container builds, not a sketch of it.',
        rtrim($body)
    );
}

/**
 * @param  list<array{0:string, 1:string}>  $edges
 */
function traceDiagram(array $classes, string $entry, array $edges): string
{
    $lines = ['flowchart LR'];
    $nodes = [$entry => true];

    foreach ($edges as [$from, $to]) {
        $nodes[$from] = true;
        $nodes[$to] = true;
    }

    foreach (array_keys($nodes) as $fqcn) {
        $label = isset($classes[$fqcn])
            ? $classes[$fqcn]['short'].'<br/><i>'.$classes[$fqcn]['role'].'</i>'
            : shortName($fqcn);

        $lines[] = '  '.nodeId($fqcn).'["'.$label.'"]';
    }

    foreach ($edges as [$from, $to]) {
        $lines[] = '  '.nodeId($from).' --> '.nodeId($to);
    }

    $lines[] = '  class '.nodeId($entry).' entry';
    $lines[] = '  classDef entry fill:#0f172a,stroke:#0f172a,color:#ffffff';

    foreach (array_keys($nodes) as $fqcn) {
        if (isset($classes[$fqcn]) && $classes[$fqcn]['role'] === 'File storage') {
            $lines[] = '  class '.nodeId($fqcn).' disk';
        }
    }

    $lines[] = '  classDef disk fill:#fef3c7,stroke:#d97706,color:#78350f';

    return implode("\n", array_unique($lines));
}

// ------------------------------------------------------------ dependencies --

function buildDependencies(array $classes, array $byDomain, array $inbound): string
{
    $packages = [];

    foreach ($classes as $class) {
        foreach ($class['vendor'] as $import) {
            $package = vendorPackage($import);

            if ($package !== null) {
                $packages[$package][$class['fqcn']] = true;
            }
        }
    }

    ksort($packages);

    $blast = '';

    foreach ($packages as $package => $users) {
        $names = array_keys($users);
        sort($names);

        $blast .= card(
            '<div class="flex items-baseline gap-3 flex-wrap">'
            .'<h4 class="font-mono text-sm font-semibold text-slate-900">'.h($package).'</h4>'
            .chip(count($names).' '.(count($names) === 1 ? 'class' : 'classes'), 'bg-slate-100 text-slate-600')
            .'</div>'
            .'<div class="mt-2 font-mono text-[13px] text-slate-600 leading-relaxed space-y-0.5">'
            .implode('', array_map(static fn (string $name): string => '<div>'.h($name).'</div>', $names))
            .'</div>'
        );
    }

    $rows = '';

    foreach ($classes as $class) {
        $out = count($class['deps']);
        $dependedOnBy = $inbound[$class['fqcn']] ?? [];
        sort($dependedOnBy);
        $in = count($dependedOnBy);

        $rows .= '<tr class="border-t border-slate-100 align-top" data-row="'.h(strtolower($class['fqcn'].' '.$class['role'])).'">'
            .'<td class="py-2 pr-4 font-mono text-[13px] text-slate-800 whitespace-nowrap">'.h($class['fqcn']).'</td>'
            .'<td class="py-2 pr-4 text-[13px] text-slate-500 whitespace-nowrap">'.h($class['role']).'</td>'
            .'<td class="py-2 pr-4 text-[13px] font-mono text-slate-500 text-right">'.$in.'</td>'
            .'<td class="py-2 pr-4 text-[13px] font-mono text-slate-500 text-right">'.$out.'</td>'
            .'<td class="py-2 font-mono text-[12px] text-slate-500 leading-relaxed">'
            .($dependedOnBy === []
                ? '<span class="text-slate-300">&mdash;</span>'
                : implode('<br/>', array_map(static fn (string $name): string => h(shortName($name)), $dependedOnBy)))
            .'</td></tr>';
    }

    $table = <<<HTML
    <div class="space-y-3">
      <input id="filter" type="search" placeholder="Filter by class name or role…" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-mono placeholder:font-sans placeholder:text-slate-400 focus:outline-none focus:border-slate-900" />
      <div class="rounded-xl border border-slate-200 bg-white px-5 py-4 overflow-x-auto">
        <table class="w-full border-collapse">
          <thead><tr>
            <th class="py-2 pr-4 text-left lbl text-slate-400">Class</th>
            <th class="py-2 pr-4 text-left lbl text-slate-400">Role</th>
            <th class="py-2 pr-4 text-right lbl text-slate-400">In</th>
            <th class="py-2 pr-4 text-right lbl text-slate-400">Out</th>
            <th class="py-2 text-left lbl text-slate-400">Depended on by</th>
          </tr></thead>
          <tbody id="rows">{$rows}</tbody>
        </table>
      </div>
    </div>
    <script>
      const filter = document.getElementById('filter');
      const rows = Array.from(document.querySelectorAll('#rows tr'));
      filter.addEventListener('input', () => {
        const term = filter.value.trim().toLowerCase();
        rows.forEach((row) => {
          row.hidden = term !== '' && !row.dataset.row.includes(term);
        });
      });
    </script>
    HTML;

    $orphans = array_values(array_filter(
        $classes,
        static fn (array $class): bool => ! isset($inbound[$class['fqcn']]) && ! str_ends_with($class['role'], 'entry point')
    ));

    $orphanList = $orphans === []
        ? paragraph('Every class is imported by at least one other, or is an entry point.')
        : '<div class="rounded-xl border border-slate-200 bg-white px-5 py-4"><div class="font-mono text-[13px] text-slate-600 space-y-0.5">'
            .implode('', array_map(static fn (array $class): string => '<div>'.h($class['fqcn']).' <span class="text-slate-400">— '.h($class['role']).'</span></div>', $orphans))
            .'</div></div>';

    $orphanCount = count($orphans);

    $body = section('Blast radius', 'What a package upgrade can reach', implode("\n", [
        paragraph('Every composer package, and the exact classes that import from it. A package with one importing class can be swapped behind that class; one that appears everywhere cannot.'),
        '<div class="grid gap-3">'.$blast.'</div>',
    ]))."\n\n".section('Class graph', 'Fan-in and fan-out, per class', implode("\n", [
        paragraph('<strong class="font-semibold text-slate-900">In</strong> is how many classes name this one — the cost of changing its signature. <strong class="font-semibold text-slate-900">Out</strong> is how many it names itself — how much it has to know to do its job.'),
        $table,
    ]))."\n\n".section('Nothing references these', 'Reached only through the framework', implode("\n", [
        paragraph('No other class in '.code('app/').' names '.($orphanCount === 1 ? 'this one' : 'these').'. That is not dead code — Laravel resolves them itself, from a route, a service provider, a Blade view or an Eloquent call. It is, though, the list to check first if you suspect something really is unused.'),
        $orphanList,
    ]));

    return shell(
        'dependencies.html',
        'Dependency graph',
        'What breaks if you change this class, and what a package upgrade can reach. Derived from the import statements in '.code('app/').'.',
        $body
    );
}
