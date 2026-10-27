<?php

declare(strict_types=1);

/**
 * The Feature trace page for documentation/architecture/generate.php. trace.php
 * reads the leesmij → spec → test chain; this file only draws it, so the page
 * shows exactly what tests/Architecture/FeatureTraceTest.php enforces.
 */

use function Documentation\Architecture\scanFeatureTrace;
use function Documentation\Architecture\traceGaps;

require_once __DIR__.'/trace.php';

/** What the trace is read from. The page stamp names the last commit that touched any of it. */
const TRACE_SOURCE = [
    'label' => 'specs & tests',
    'paths' => ['documentation/leesmij', 'documentation/features', 'tests/Feature', 'tests/Browser'],
];

const SUITE_CHIPS = [
    'Feature' => 'bg-blue-100 text-blue-800',
    'Browser' => 'bg-amber-100 text-amber-800',
];

// ------------------------------------------------------------------ render --

function buildFeatureTrace(): string
{
    $trace = scanFeatureTrace(ROOT);
    $features = $trace['features'];
    $scenarios = array_merge(...array_values(array_map(static fn (array $feature): array => $feature['spec']['scenarios'] ?? [], $features)));
    $untested = count(array_filter($scenarios, static fn (array $scenario): bool => $scenario['tests'] === []));
    $gaps = array_values(array_filter(traceGaps($trace), static fn (array $gap): bool => $gap['items'] !== []));
    $breaks = array_sum(array_map(static fn (array $gap): int => $gap['broken'] ? count($gap['items']) : 0, $gaps));

    $stats = [
        ['features', (string) count($features), false],
        ['scenarios', (string) count($scenarios), false],
        ['acceptance tests', (string) $trace['testCount'], false],
        ['untested scenarios', (string) $untested, $untested > 0],
    ];

    $statCards = '';

    foreach ($stats as [$label, $value, $alarming]) {
        $statCards .= card('<div class="font-serif text-3xl tracking-tight'.($alarming ? ' text-red-600' : '').'">'.h($value).'</div><div class="lbl text-slate-400 mt-1">'.h($label).'</div>');
    }

    $body = section('At a glance', $breaks === 0 ? 'The chain holds' : counted($breaks, 'break').' in the chain', implode("\n", [
        '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">'.$statCards.'</div>',
        gapCards($gaps),
    ]))."\n\n".section('Explorer', 'Leesmij → scenarios → tests', traceExplorer($features));

    return shell(
        'features.html',
        'Feature trace',
        'Each feature followed from the Dutch leesmij that says why, through the Gherkin scenarios that say what, to the acceptance tests that check it. The only link from a scenario to its test is the comment above the test — '.code('// post_form.feature — Scenario: …').' — so a renamed scenario or a forgotten test shows up here as a break in the chain, and fails '.code('tests/Architecture/FeatureTraceTest.php').'.',
        $body,
        TRACE_SOURCE
    );
}

function counted(int $count, string $noun): string
{
    return $count.' '.$noun.($count === 1 ? '' : 's');
}

function featureLabel(array $feature): string
{
    return ($feature['spec']['title'] ?? '') ?: (($feature['leesmij']['title'] ?? '') ?: basename($feature['key']));
}

// -------------------------------------------------------------------- gaps --

/** @param list<array{title:string, note:string, broken:bool, items:list<array{feature:?string, ref:string, detail:string}>}> $gaps */
function gapCards(array $gaps): string
{
    $html = '';

    if (array_filter($gaps, static fn (array $gap): bool => $gap['broken']) === []) {
        $html .= '<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900 leading-relaxed">'
            .'Every scenario has a test, every test comment names a real scenario, and every spec has its leesmij.</div>';
    }

    foreach ($gaps as $gap) {
        $list = '<div class="mt-3 space-y-1 text-[13px] text-slate-600 leading-relaxed">'
            .implode('', array_map(static fn (array $item): string => '<div>'.gapItem($item).'</div>', $gap['items']))
            .'</div>';

        $html .= card(
            '<div class="flex items-baseline gap-3 flex-wrap">'
            .'<h4 class="font-serif text-lg text-slate-900">'.h($gap['title']).'</h4>'
            .chip((string) count($gap['items']), $gap['broken'] ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600')
            .'</div>'
            .'<p class="text-sm text-slate-500 mt-1 leading-relaxed">'.h($gap['note']).'</p>'
            .($gap['broken'] ? $list : '<details class="mt-2"><summary class="text-sm text-slate-500 cursor-pointer hover:text-slate-900">Show them</summary>'.$list.'</details>')
        );
    }

    return '<div class="grid gap-3">'.$html.'</div>';
}

/** @param array{feature:?string, ref:string, detail:string} $item */
function gapItem(array $item): string
{
    $classes = 'font-mono text-[13px] text-slate-800 [overflow-wrap:anywhere]';
    $reference = $item['feature'] === null
        ? '<span class="'.$classes.'">'.h($item['ref']).'</span>'
        : '<a href="#'.h($item['feature']).'" class="'.$classes.' underline decoration-slate-300 underline-offset-2 hover:decoration-slate-900">'.h($item['ref']).'</a>';

    return $reference.($item['detail'] === '' ? '' : ' <span class="text-slate-400">—</span> '.h($item['detail']));
}

// ---------------------------------------------------------------- explorer --

/** @param array<string, array> $features */
function traceExplorer(array $features): string
{
    $tree = traceTree($features);
    $panes = overviewPane($features).implode('', array_map(static fn (array $feature): string => featurePane($feature, $features), $features));

    return <<<HTML
    <div class="grid gap-6 md:grid-cols-[14rem_minmax(0,1fr)] items-start">
      <aside class="md:sticky md:top-6 md:max-h-[calc(100vh-3rem)] md:overflow-y-auto space-y-3 md:pr-1">
        <input id="trace-filter" type="search" placeholder="Filter features…" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm placeholder:text-slate-400 focus:outline-none focus:border-slate-900" />
        <nav class="text-sm space-y-4">{$tree}</nav>
      </aside>
      <div class="min-w-0">{$panes}</div>
    </div>
    <script>
      (() => {
        const panes = Array.from(document.querySelectorAll('[data-pane]'));
        const links = Array.from(document.querySelectorAll('[data-key]'));

        const show = (scroll) => {
          const wanted = decodeURIComponent(location.hash.slice(1));
          const pane = panes.find((candidate) => candidate.dataset.pane === wanted) ?? panes.find((candidate) => candidate.dataset.pane === '');
          panes.forEach((candidate) => candidate.classList.toggle('hidden', candidate !== pane));
          links.forEach((link) => link.dataset.key === pane.dataset.pane ? link.setAttribute('aria-current', 'page') : link.removeAttribute('aria-current'));
          if (scroll) {
            pane.scrollIntoView({ block: 'start' });
          }
        };

        window.addEventListener('hashchange', () => show(true));
        show(location.hash.length > 1);

        const filter = document.getElementById('trace-filter');
        filter.addEventListener('input', () => {
          const term = filter.value.trim().toLowerCase();
          links.filter((link) => link.dataset.search !== undefined).forEach((link) => {
            link.classList.toggle('hidden', term !== '' && !link.dataset.search.includes(term));
          });
          document.querySelectorAll('[data-group]').forEach((group) => {
            group.classList.toggle('hidden', !group.querySelector('[data-search]:not(.hidden)'));
          });
        });

        document.querySelectorAll('[data-open-all]').forEach((button) => {
          button.addEventListener('click', () => {
            const scenarios = Array.from(button.closest('[data-pane]').querySelectorAll('details[data-scenario]'));
            const open = scenarios.some((details) => !details.open);
            scenarios.forEach((details) => { details.open = open; });
            button.textContent = open ? 'Close all' : 'Open all';
          });
        });
      })();
    </script>
    HTML;
}

/** @param array<string, array> $features */
function traceTree(array $features): string
{
    $groups = [];

    foreach ($features as $key => $feature) {
        $segments = explode('/', $key);
        $domain = array_shift($segments);
        array_pop($segments);
        $groups[$domain][implode('/', $segments)][] = $feature;
    }

    $html = treeLink('', 'All features', '', '', false);

    foreach ($groups as $domain => $areas) {
        $inner = '';

        foreach ($areas as $area => $members) {
            if ($area !== '') {
                $inner .= '<div class="text-[11px] text-slate-400 px-2.5 pt-1.5">'.h($area).'</div>';
            }

            foreach ($members as $feature) {
                $scenarios = $feature['spec']['scenarios'] ?? [];
                $tested = count(array_filter($scenarios, static fn (array $scenario): bool => $scenario['tests'] !== []));
                $complete = $feature['spec'] !== null && $feature['leesmij'] !== null && $tested === count($scenarios);
                $search = strtolower(implode(' ', [$feature['key'], featureLabel($feature), ...array_column($scenarios, 'title')]));

                $inner .= treeLink(
                    $feature['key'],
                    featureLabel($feature),
                    '<span class="ml-auto shrink-0 font-mono text-[11px] '.($complete ? 'text-slate-400' : 'text-red-500 font-semibold').'">'.$tested.'/'.count($scenarios).'</span>',
                    $search,
                    $area !== '',
                );
            }
        }

        $html .= '<div data-group class="space-y-0.5"><div class="lbl text-slate-400 px-2.5 pb-1">'.h($domain).'</div>'.$inner.'</div>';
    }

    return $html;
}

function treeLink(string $key, string $label, string $badge, string $search, bool $indented): string
{
    return '<a href="#'.h($key).'" data-key="'.h($key).'"'.($search === '' ? '' : ' data-search="'.h($search).'"')
        .' class="flex items-baseline gap-2 rounded '.($indented ? 'pl-4' : 'pl-2.5').' pr-2.5 py-1 text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 aria-[current=page]:bg-slate-900 aria-[current=page]:text-white transition">'
        .'<span class="truncate" title="'.h($label).'">'.h($label).'</span>'.$badge.'</a>';
}

/** @param array<string, array> $features */
function overviewPane(array $features): string
{
    $rows = '';

    foreach ($features as $key => $feature) {
        $scenarios = $feature['spec']['scenarios'] ?? [];
        $untested = count(array_filter($scenarios, static fn (array $scenario): bool => $scenario['tests'] === []));
        $tests = array_sum(array_map(static fn (array $scenario): int => count($scenario['tests']), $scenarios));
        $unlinked = count($feature['unlinked']);
        $number = static fn (int $value, string $alarm): string => '<td class="py-2 pl-4 text-right font-mono text-[13px] '.($value === 0 ? 'text-slate-300' : $alarm).'">'.$value.'</td>';

        $rows .= '<tr class="border-t border-slate-100 align-top">'
            .'<td class="py-2 pr-4"><a href="#'.h($key).'" class="text-sm text-slate-900 hover:underline decoration-slate-300 underline-offset-2">'.h(featureLabel($feature)).'</a>'
            .'<div class="font-mono text-[11px] text-slate-400">'.h($key).'</div></td>'
            .'<td class="py-2 pl-4 text-center text-[13px]">'.($feature['leesmij'] === null ? '<span class="text-red-600 font-semibold">missing</span>' : '<span class="text-emerald-600">✓</span>').'</td>'
            .$number(count($scenarios), 'text-slate-600')
            .$number($untested, 'text-red-600 font-semibold')
            .$number($tests, 'text-slate-600')
            .$number($unlinked, 'text-slate-600')
            .'</tr>';
    }

    return <<<HTML
    <div data-pane="" class="space-y-3 scroll-mt-6">
      <p class="text-sm text-slate-500 leading-relaxed">Pick a feature to follow it from its leesmij to its tests. Each row is one feature; red marks a break in the chain.</p>
      <div class="rounded-xl border border-slate-200 bg-white px-5 py-4 overflow-x-auto">
        <table class="w-full border-collapse">
          <thead><tr>
            <th class="py-2 pr-4 text-left lbl text-slate-400">Feature</th>
            <th class="py-2 pl-4 text-center lbl text-slate-400">Leesmij</th>
            <th class="py-2 pl-4 text-right lbl text-slate-400">Scenarios</th>
            <th class="py-2 pl-4 text-right lbl text-slate-400">Untested</th>
            <th class="py-2 pl-4 text-right lbl text-slate-400">Tests</th>
            <th class="py-2 pl-4 text-right lbl text-slate-400">No scenario</th>
          </tr></thead>
          <tbody>{$rows}</tbody>
        </table>
      </div>
    </div>
    HTML;
}

/** @param array<string, array> $features */
function featurePane(array $feature, array $features): string
{
    $key = $feature['key'];
    $scenarios = $feature['spec']['scenarios'] ?? [];
    $tests = array_sum(array_map(static fn (array $scenario): int => count($scenario['tests']), $scenarios)) + count($feature['unlinked']);

    $testFiles = $feature['testFiles'] === []
        ? '<span class="text-red-600">no acceptance test file</span>'
        : implode(' · ', array_map(h(...), $feature['testFiles']));

    $header = '<header class="space-y-2">'
        .'<div class="font-mono text-xs text-slate-400">'.h(str_replace('/', ' / ', $key)).'</div>'
        .'<div class="flex items-baseline gap-3 flex-wrap">'
        .'<h3 class="font-serif text-2xl tracking-tight text-slate-900">'.h(featureLabel($feature)).'</h3>'
        .chip(counted(count($scenarios), 'scenario'), 'bg-slate-100 text-slate-600')
        .chip(counted($tests, 'test'), 'bg-slate-100 text-slate-600')
        .'</div>'
        .'<div class="font-mono text-[11px] text-slate-400 leading-relaxed [overflow-wrap:anywhere]">'.$testFiles.'</div>'
        .'</header>';

    $openAll = $scenarios === [] ? '' : '<button type="button" data-open-all class="ml-auto text-xs text-slate-500 hover:text-slate-900">Open all</button>';

    $steps = traceStep('Leesmij', 'documentation/leesmij/'.$key.'.md', leesmijCard($feature, $features))
        .traceStep('Spec', 'documentation/features/'.$key.'.feature', specBlock($feature), $openAll);

    if ($feature['unlinked'] !== []) {
        $steps .= traceStep(
            'Tests with no scenario',
            '',
            '<p class="text-sm text-slate-500 leading-relaxed">Checked here, but no scenario in the spec describes them.</p>'
            .'<div class="space-y-1.5">'.implode('', array_map(testDetails(...), $feature['unlinked'])).'</div>'
        );
    }

    return '<article data-pane="'.h($key).'" class="space-y-8 scroll-mt-6">'.$header.$steps.'</article>';
}

function traceStep(string $label, string $path, string $inner, string $action = ''): string
{
    return '<section class="space-y-3">'
        .'<div class="flex items-baseline gap-3 flex-wrap"><h4 class="lbl text-slate-400">'.h($label).'</h4>'
        .($path === '' ? '' : '<span class="font-mono text-[11px] text-slate-400 [overflow-wrap:anywhere]">'.h($path).'</span>')
        .$action.'</div>'
        .$inner
        .'</section>';
}

function missingCard(string $text): string
{
    return '<div class="rounded-xl border border-dashed border-red-300 bg-white px-5 py-4 text-sm text-red-700">'.h($text).'</div>';
}

/** @param array<string, array> $features */
function leesmijCard(array $feature, array $features): string
{
    if ($feature['leesmij'] === null) {
        return missingCard('No leesmij for this feature.');
    }

    $linkedFrom = array_keys(array_filter(
        $features,
        static fn (array $other): bool => in_array($feature['key'], $other['leesmij']['links'] ?? [], true),
    ));

    $backlinks = $linkedFrom === []
        ? ''
        : '<p class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-400">Linked from '
            .implode(', ', array_map(static fn (string $key): string => '<a href="#'.h($key).'" class="text-slate-600 underline decoration-slate-300 underline-offset-2 hover:decoration-slate-900">'.h(featureLabel($features[$key])).'</a>', $linkedFrom))
            .'</p>';

    return card(
        '<div lang="nl" class="space-y-3 text-[15px] text-slate-700 leading-relaxed">'
        .'<h4 class="font-serif text-lg text-slate-900">'.h($feature['leesmij']['title']).'</h4>'
        .leesmijHtml($feature['leesmij'], $features)
        .'</div>'
        .$backlinks
    );
}

/**
 * Renders the small Markdown subset a leesmij uses: paragraphs, one level of
 * bullets with indented continuation lines, and inline marks.
 *
 * @param  array{title:string, body:list<string>, links:array<string, ?string>}  $leesmij
 * @param  array<string, array>  $features
 */
function leesmijHtml(array $leesmij, array $features): string
{
    $blocks = [];

    foreach ($leesmij['body'] as $line) {
        $last = array_key_last($blocks);

        if (trim($line) === '') {
            $blocks[] = null;
        } elseif (str_starts_with($line, '- ')) {
            $blocks[] = ['li', substr($line, 2)];
        } elseif ($last !== null && $blocks[$last] !== null && ($blocks[$last][0] === 'p' || str_starts_with($line, ' '))) {
            $blocks[$last][1] .= ' '.trim($line);
        } else {
            $blocks[] = ['p', trim($line)];
        }
    }

    $html = '';
    $inList = false;

    foreach (array_filter($blocks) as [$type, $text]) {
        if ($type === 'li' && ! $inList) {
            $html .= '<ul class="list-disc pl-5 space-y-1.5 marker:text-slate-300">';
            $inList = true;
        }

        if ($type === 'p' && $inList) {
            $html .= '</ul>';
            $inList = false;
        }

        $inline = leesmijInline($text, $leesmij['links'], $features);
        $html .= $type === 'li' ? '<li>'.$inline.'</li>' : '<p>'.$inline.'</p>';
    }

    return $html.($inList ? '</ul>' : '');
}

/**
 * Code spans are set aside first, so bold or italic marks around them still pair up.
 *
 * @param  array<string, ?string>  $links
 * @param  array<string, array>  $features
 */
function leesmijInline(string $text, array $links, array $features): string
{
    $spans = [];
    $text = (string) preg_replace_callback('/`([^`]+)`/', static function (array $match) use (&$spans): string {
        $spans[] = code($match[1]);

        return "\0".(count($spans) - 1)."\0";
    }, $text);

    $html = h($text);
    $html = (string) preg_replace('/\*\*(.+?)\*\*/', '<strong class="font-semibold text-slate-900">$1</strong>', $html);
    $html = (string) preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
    $html = (string) preg_replace_callback('/\[\[([a-z0-9_]+)\]\]/', static function (array $match) use ($links, $features): string {
        $target = $links[$match[1]] ?? null;

        return $target === null
            ? '<span class="font-mono text-[0.875em] text-red-600 line-through" title="No feature has this name">[['.$match[1].']]</span>'
            : '<a href="#'.h($target).'" title="'.h(featureLabel($features[$target])).'" class="font-mono text-[0.875em] text-slate-800 underline decoration-slate-300 underline-offset-2 hover:decoration-slate-900">'.$match[1].'</a>';
    }, $html);

    return (string) preg_replace_callback('/\0(\d+)\0/', static fn (array $match): string => $spans[(int) $match[1]], $html);
}

function specBlock(array $feature): string
{
    $spec = $feature['spec'];

    if ($spec === null) {
        return missingCard('No spec for this feature.');
    }

    $preamble = '';

    if ($spec['narrative'] !== []) {
        $preamble .= '<p class="text-sm text-slate-500 italic leading-relaxed">'.implode('<br/>', array_map(h(...), $spec['narrative'])).'</p>';
    }

    if ($spec['background'] !== []) {
        $preamble .= '<div class="mt-3"><div class="lbl text-slate-400 mb-1.5">Background</div>'.gherkinHtml($spec['background']).'</div>';
    }

    $scenarios = implode('', array_map(
        static fn (array $scenario): string => scenarioDetails($scenario, basename($feature['key']).'.feature'),
        $spec['scenarios'],
    ));

    return ($preamble === '' ? '' : card($preamble)).'<div class="space-y-2">'.$scenarios.'</div>';
}

/** @param array{keyword:string, title:string, label:?string, line:int, steps:list<string>, tests:list<array>} $scenario */
function scenarioDetails(array $scenario, string $file): string
{
    $tests = $scenario['tests'];

    $chips = $scenario['keyword'] === 'Scenario Outline' ? chip('outline', 'bg-slate-100 text-slate-500') : '';

    if ($tests === []) {
        $chips .= chip('no test', 'bg-red-100 text-red-700');
    }

    foreach (array_count_values(array_column($tests, 'suite')) as $suite => $count) {
        $chips .= chip($suite.($count > 1 ? ' ×'.$count : ''), SUITE_CHIPS[$suite]);
    }

    $meta = '<div class="flex flex-wrap gap-x-3 font-mono text-[11px] text-slate-400">'
        .($scenario['label'] === null ? '' : '<span># '.h($scenario['label']).'</span>')
        .'<span>'.h($file).':'.$scenario['line'].'</span></div>';

    $checkedBy = $tests === []
        ? '<p class="text-sm text-red-700">No acceptance test names this scenario.</p>'
        : '<div class="space-y-1.5">'.implode('', array_map(testDetails(...), $tests)).'</div>';

    return '<details data-scenario class="group/scenario rounded-lg border border-slate-200 bg-white">'
        .'<summary class="flex flex-wrap items-baseline gap-2 px-4 py-2.5 cursor-pointer select-none rounded-lg hover:bg-slate-50">'
        .'<span class="inline-block text-[10px] text-slate-400 transition group-open/scenario:rotate-90">▶</span>'
        .'<span class="text-sm text-slate-900 leading-snug">'.h($scenario['title']).'</span>'
        .'<span class="ml-auto flex shrink-0 gap-1">'.$chips.'</span>'
        .'</summary>'
        .'<div class="border-t border-slate-100 px-4 py-3 space-y-3">'
        .$meta
        .gherkinHtml($scenario['steps'])
        .'<div class="lbl text-slate-400 pt-1">Checked by</div>'
        .$checkedBy
        .'</div>'
        .'</details>';
}

/** @param array{suite:string, file:string, line:int, name:string, code:string, example:?string} $test */
function testDetails(array $test): string
{
    return '<details class="group/test rounded-md border border-slate-200 bg-white">'
        .'<summary class="flex flex-wrap items-baseline gap-2 px-3 py-2 cursor-pointer select-none rounded-md hover:bg-slate-50">'
        .'<span class="inline-block text-[9px] text-slate-400 transition group-open/test:rotate-90">▶</span>'
        .chip($test['suite'], SUITE_CHIPS[$test['suite']])
        .'<span class="text-[13px] text-slate-700 leading-snug">'.h($test['name']).'</span>'
        .($test['example'] === null ? '' : chip($test['example'], 'bg-slate-100 text-slate-600'))
        .'<span class="ml-auto shrink-0 font-mono text-[11px] text-slate-400" title="'.h($test['file']).'">'.h(basename($test['file'])).':'.$test['line'].'</span>'
        .'</summary>'
        .'<pre class="border-t border-slate-100 bg-slate-50 rounded-b-md px-3 py-2.5 font-mono text-[12px] leading-relaxed text-slate-700 overflow-x-auto">'.h($test['code']).'</pre>'
        .'</details>';
}

/** @param list<string> $lines */
function gherkinHtml(array $lines): string
{
    return '<pre class="rounded-md bg-stone-50 border border-stone-200 px-3 py-2.5 font-mono text-[12px] leading-relaxed text-slate-600 overflow-x-auto">'
        .implode("\n", array_map(gherkinLine(...), $lines))
        .'</pre>';
}

function gherkinLine(string $line): string
{
    $html = h($line);
    $trimmed = ltrim($line);

    if (str_starts_with($trimmed, '#')) {
        return '<span class="text-slate-400">'.$html.'</span>';
    }

    if (str_starts_with($trimmed, '|')) {
        return str_replace('|', '<span class="text-slate-300">|</span>', $html);
    }

    $html = (string) preg_replace('/&quot;.*?&quot;/', '<span class="text-emerald-700">$0</span>', $html);
    $html = (string) preg_replace('/&lt;[\w ]+&gt;/', '<span class="text-blue-700">$0</span>', $html);

    return (string) preg_replace('/^(\s*)(Given|When|Then|And|But|Examples:)(?=\s|$)/', '$1<span class="font-semibold text-slate-900">$2</span>', $html);
}
