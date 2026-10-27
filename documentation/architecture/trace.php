<?php

declare(strict_types=1);

/**
 * Reads the feature trace: each feature followed from its Dutch leesmij, through
 * its Gherkin scenarios, to the acceptance tests that check them. features.php
 * draws it as a page.
 *
 * Nothing links the three but convention. A leesmij and a spec share a path, a
 * test file shares the spec's directory and (in StudlyCase) its name, and each
 * test names its scenario in the comment directly above it:
 *
 *     // post_form.feature — Scenario: Creating a published post
 */

namespace Documentation\Architecture;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

const ACCEPTANCE_SUITES = ['Feature', 'Browser'];

/**
 * @return array{features: array<string, array>, dangling: list<array>, strays: list<string>, testCount: int}
 */
function scanFeatureTrace(string $root): array
{
    $root = rtrim($root, '/').'/';
    $features = [];

    foreach (filesUnder($root, 'documentation/features', 'feature') as $file) {
        $features[substr($file, strlen('documentation/features/'), -strlen('.feature'))]['spec'] = parseGherkin($root.$file);
    }

    foreach (filesUnder($root, 'documentation/leesmij', 'md') as $file) {
        $features[substr($file, strlen('documentation/leesmij/'), -strlen('.md'))]['leesmij'] = parseLeesmij($root.$file);
    }

    ksort($features);

    foreach ($features as $key => $feature) {
        $features[$key] = [...['key' => $key, 'spec' => null, 'leesmij' => null, 'testFiles' => [], 'unlinked' => []], ...$feature];
    }

    foreach ($features as $key => $feature) {
        foreach (array_keys($feature['leesmij']['links'] ?? []) as $name) {
            $features[$key]['leesmij']['links'][$name] = resolveFeatureLink($name, $key, array_keys($features));
        }
    }

    $dangling = [];
    $strays = [];
    $testCount = 0;

    foreach (ACCEPTANCE_SUITES as $suite) {
        foreach (filesUnder($root, 'tests/'.$suite, 'php') as $file) {
            $owner = testFileFeature($suite, $file);
            $tests = parseAcceptanceTests($root, $suite, $file);
            $testCount += count($tests);

            if (isset($features[$owner])) {
                $features[$owner]['testFiles'][] = $file;
            } else {
                $strays[] = $file;
            }

            foreach ($tests as $test) {
                if ($test['link'] === null) {
                    if (isset($features[$owner])) {
                        $features[$owner]['unlinked'][] = $test;
                    }

                    continue;
                }

                $key = siblingFeature($owner, $test['link']['feature']);
                $match = scenarioFor($features[$key]['spec']['scenarios'] ?? [], $test['link']['title']);

                if ($match === null) {
                    $dangling[] = $test;

                    continue;
                }

                [$index, $test['example']] = $match;
                $features[$key]['spec']['scenarios'][$index]['tests'][] = $test;
            }
        }
    }

    return ['features' => $features, 'dangling' => $dangling, 'strays' => $strays, 'testCount' => $testCount];
}

/**
 * Every place the leesmij → spec → test chain breaks, plus the acceptance tests
 * that sit outside it. Only the first kind is broken; every kind is listed, empty
 * or not.
 *
 * @param  array{features: array<string, array>, dangling: list<array>, strays: list<string>, testCount: int}  $trace
 * @return list<array{title:string, note:string, broken:bool, items:list<array{feature:?string, ref:string, detail:string}>}>
 */
function traceGaps(array $trace): array
{
    $untested = [];
    $withoutLeesmij = [];
    $withoutSpec = [];
    $deadLinks = [];
    $unlinked = [];

    foreach ($trace['features'] as $key => $feature) {
        foreach ($feature['spec']['scenarios'] ?? [] as $scenario) {
            if ($scenario['tests'] === []) {
                $untested[] = ['feature' => $key, 'ref' => $key, 'detail' => $scenario['title']];
            }
        }

        if ($feature['leesmij'] === null) {
            $withoutLeesmij[] = ['feature' => $key, 'ref' => $key, 'detail' => ''];
        }

        if ($feature['spec'] === null) {
            $withoutSpec[] = ['feature' => $key, 'ref' => $key, 'detail' => ''];
        }

        foreach ($feature['leesmij']['links'] ?? [] as $name => $target) {
            if ($target === null) {
                $deadLinks[] = ['feature' => $key, 'ref' => $key, 'detail' => '[['.$name.']]'];
            }
        }

        foreach ($feature['unlinked'] as $test) {
            $unlinked[] = ['feature' => $key, 'ref' => $test['file'].':'.$test['line'], 'detail' => $test['name']];
        }
    }

    return [
        [
            'title' => 'Scenarios without a test',
            'note' => 'No acceptance test names them in the comment above it.',
            'broken' => true,
            'items' => $untested,
        ],
        [
            'title' => 'Test comments that name no scenario',
            'note' => 'The scenario was renamed or removed, or the comment does not repeat its title exactly.',
            'broken' => true,
            'items' => array_map(static fn (array $test): array => ['feature' => null, 'ref' => $test['file'].':'.$test['line'], 'detail' => (string) $test['comment']], $trace['dangling']),
        ],
        [
            'title' => 'Test files that match no spec',
            'note' => 'Their directory and name point at a feature that has no spec or leesmij.',
            'broken' => true,
            'items' => array_map(static fn (string $file): array => ['feature' => null, 'ref' => $file, 'detail' => ''], $trace['strays']),
        ],
        [
            'title' => 'Specs without a leesmij',
            'note' => 'The scenarios say what, but nothing says why.',
            'broken' => true,
            'items' => $withoutLeesmij,
        ],
        [
            'title' => 'Leesmij without a spec',
            'note' => 'The why is written down, but no scenario pins the behavior.',
            'broken' => true,
            'items' => $withoutSpec,
        ],
        [
            'title' => 'Leesmij links to no feature',
            'note' => 'No spec or leesmij carries that file name.',
            'broken' => true,
            'items' => $deadLinks,
        ],
        [
            'title' => 'Tests with no scenario',
            'note' => 'Checked by an acceptance test, described by no scenario. Often deliberate — but the spec does not record them.',
            'broken' => false,
            'items' => $unlinked,
        ],
    ];
}

/** @return list<string> paths relative to $root, sorted */
function filesUnder(string $root, string $directory, string $extension): array
{
    if (! is_dir($root.$directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.$directory, FilesystemIterator::SKIP_DOTS));

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->getExtension() === $extension) {
            $files[] = substr($file->getPathname(), strlen($root));
        }
    }

    sort($files);

    return $files;
}

/**
 * @return array{title:string, narrative:list<string>, background:list<string>, scenarios:list<array{keyword:string, title:string, label:?string, line:int, steps:list<string>, tests:list<array>}>}
 */
function parseGherkin(string $path): array
{
    $spec = ['title' => '', 'narrative' => [], 'background' => [], 'scenarios' => []];
    $section = 'narrative';
    $label = null;

    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $index => $line) {
        $trimmed = trim($line);

        if (preg_match('/^Feature:\s*(.+)$/', $trimmed, $match) === 1) {
            $spec['title'] = $match[1];

            continue;
        }

        // A comment directly above a scenario is its stable index, e.g. "# Admin post editor 10".
        if (str_starts_with($trimmed, '#')) {
            $label = trim(substr($trimmed, 1));

            continue;
        }

        if (preg_match('/^(Scenario(?: Outline)?):\s*(.+)$/', $trimmed, $match) === 1) {
            $spec['scenarios'][] = ['keyword' => $match[1], 'title' => $match[2], 'label' => $label, 'line' => $index + 1, 'steps' => [], 'tests' => []];
            $section = 'scenario';
            $label = null;

            continue;
        }

        if ($trimmed === 'Background:') {
            $section = 'background';

            continue;
        }

        if ($trimmed === '' && $section === 'narrative') {
            continue;
        }

        // A comment that turned out not to head a scenario belongs to the text it sits in.
        $lines = $label === null || $trimmed === '' ? [$line] : [str_repeat(' ', strlen($line) - strlen(ltrim($line))).'# '.$label, $line];

        if ($trimmed !== '') {
            $label = null;
        }

        foreach ($lines as $text) {
            if ($section === 'narrative') {
                $spec['narrative'][] = trim($text);
            } elseif ($section === 'background') {
                $spec['background'][] = $text;
            } else {
                $spec['scenarios'][array_key_last($spec['scenarios'])]['steps'][] = $text;
            }
        }
    }

    $spec['background'] = dedent($spec['background']);

    foreach ($spec['scenarios'] as $index => $scenario) {
        $spec['scenarios'][$index]['steps'] = dedent($scenario['steps']);
    }

    return $spec;
}

/**
 * Strips the indentation the lines share, and the blank lines around them.
 *
 * @param  list<string>  $lines
 * @return list<string>
 */
function dedent(array $lines): array
{
    while ($lines !== [] && trim($lines[array_key_last($lines)]) === '') {
        array_pop($lines);
    }

    while ($lines !== [] && trim($lines[0]) === '') {
        array_shift($lines);
    }

    $indents = array_map(
        static fn (string $line): int => strlen($line) - strlen(ltrim($line)),
        array_filter($lines, static fn (string $line): bool => trim($line) !== ''),
    );
    $cut = $indents === [] ? 0 : min($indents);

    return array_map(static fn (string $line): string => substr($line, $cut), $lines);
}

/**
 * @return array{title:string, body:list<string>, links:array<string, ?string>}
 */
function parseLeesmij(string $path): array
{
    $title = '';
    $body = [];

    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        if ($title === '' && str_starts_with($line, '# ')) {
            $title = substr($line, 2);

            continue;
        }

        $body[] = $line;
    }

    preg_match_all('/\[\[([a-z0-9_]+)\]\]/', implode("\n", $body), $matches);

    return ['title' => $title, 'body' => $body, 'links' => array_fill_keys($matches[1], null)];
}

/**
 * A [[name]] link names a feature by file name alone. When two domains share a
 * name — microblog has message_links in both admin/ and frontend/ — the one
 * closest to the linking feature wins.
 *
 * @param  list<string>  $keys
 */
function resolveFeatureLink(string $name, string $from, array $keys): ?string
{
    $candidates = array_values(array_filter($keys, static fn (string $key): bool => basename($key) === $name));

    usort($candidates, static fn (string $a, string $b): int => sharedSegments($b, $from) <=> sharedSegments($a, $from));

    return $candidates[0] ?? null;
}

function sharedSegments(string $a, string $b): int
{
    $shared = 0;

    foreach (array_map(null, explode('/', $a), explode('/', $b)) as [$left, $right]) {
        if ($left !== $right) {
            break;
        }

        $shared++;
    }

    return $shared;
}

/** tests/Feature/blog/admin/PostFormTest.php belongs to blog/admin/post_form. */
function testFileFeature(string $suite, string $file): string
{
    if (! str_ends_with($file, 'Test.php')) {
        return $file;
    }

    $relative = substr($file, strlen('tests/'.$suite.'/'), -strlen('Test.php'));
    $name = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', basename($relative)));

    return siblingFeature($relative, $name);
}

/** The feature called $name in the same directory as $key. */
function siblingFeature(string $key, string $name): string
{
    $directory = dirname($key);

    return $directory === '.' ? $name : $directory.'/'.$name;
}

/**
 * Every top-level it() or test() in an acceptance test file, with the scenario
 * named in the comment directly above it, if any. A test runs from its opening
 * line to the first unindented line that closes a statement, which takes in a
 * trailing ->with() dataset.
 *
 * @return list<array{suite:string, file:string, line:int, name:string, code:string, comment:?string, link:?array{feature:string, title:string}, example:?string}>
 */
function parseAcceptanceTests(string $root, string $suite, string $file): array
{
    $lines = file($root.$file, FILE_IGNORE_NEW_LINES) ?: [];
    $count = count($lines);
    $tests = [];

    for ($start = 0; $start < $count; $start++) {
        if (preg_match('/^(?:it|test)\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1/', $lines[$start], $opening) !== 1) {
            continue;
        }

        $end = $start;

        while ($end < $count - 1 && preg_match('/^\S.*;\s*$/', $lines[$end]) !== 1) {
            $end++;
        }

        $comment = $start > 0 && str_starts_with($lines[$start - 1], '// ') ? substr($lines[$start - 1], 3) : null;
        $link = null;

        if ($comment !== null && str_contains($comment, '.feature')) {
            // A comment that mentions a spec but does not parse still counts as a link — one that leads nowhere.
            $link = preg_match('/^([a-z0-9_]+)\.feature — Scenario(?: Outline)?: (.+?)\s*$/u', $comment, $match) === 1
                ? ['feature' => $match[1], 'title' => $match[2]]
                : ['feature' => '', 'title' => $comment];
        }

        $tests[] = [
            'suite' => $suite,
            'file' => $file,
            'line' => $start + 1,
            'name' => stripslashes($opening[2]),
            'code' => implode("\n", array_slice($lines, $start, $end - $start + 1)),
            'comment' => $comment,
            'link' => $link,
            'example' => null,
        ];

        $start = $end;
    }

    return $tests;
}

/**
 * The scenario a test comment names. An Outline tested one row at a time
 * carries the row after the title, as in "…cannot exceed 280 characters (281 chars)".
 *
 * @param  list<array{title:string}>  $scenarios
 * @return array{0:int, 1:?string}|null scenario index and example row
 */
function scenarioFor(array $scenarios, string $title): ?array
{
    foreach ($scenarios as $index => $scenario) {
        if ($scenario['title'] === $title) {
            return [$index, null];
        }
    }

    foreach ($scenarios as $index => $scenario) {
        if (str_starts_with($title, $scenario['title'].' (') && str_ends_with($title, ')')) {
            return [$index, substr($title, strlen($scenario['title']) + 2, -1)];
        }
    }

    return null;
}
