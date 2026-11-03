#!/usr/bin/env php
<?php

declare(strict_types=1);

use Dom\HTMLDocument;

/**
 * Checks documentation/lessons/ against the repository.
 *
 * Snippets are quoted by hand and checked, not generated from source like the
 * architecture pages: a lesson's prose explains particular lines, so when the
 * code changes someone has to reread the prose, not just get a new snippet
 * under it. Fragments are matched by content, not line numbers, so an
 * unrelated edit elsewhere in the file does not fail a lesson that is still
 * right.
 *
 * A lesson quotes real code. Every snippet that names its file in data-source
 * must still be in that file: each fragment between `// …` elision lines has
 * to match a run of whole lines, in order. Only current lessons are checked.
 * A retired lesson shows the code as it was at the commit it names, and is
 * left alone. The index must link every lesson, whatever its status.
 *
 * Usage: php documentation/tools/check-lessons.php
 *
 * Exits non-zero, listing every problem, when a lesson no longer matches.
 */
const ROOT = __DIR__.'/../../';
const LESSONS = __DIR__.'/../lessons/';

/** A line holding only this marker stands for code the snippet leaves out. */
const ELISION = '// …';

const STATUS_CURRENT = 'current';
const STATUS_RETIRED = 'retired';

/**
 * @return list<string> problems found in one lesson, empty when it is sound
 */
function checkLesson(string $path): array
{
    $name = 'documentation/lessons/'.basename($path);
    $document = HTMLDocument::createFromFile($path, LIBXML_NOERROR);
    $status = $document->querySelector('meta[name="lesson-status"]')?->getAttribute('content');

    if ($status === STATUS_RETIRED) {
        $verifiedAt = $document->querySelector('meta[name="lesson-verified-at"]')?->getAttribute('content') ?? '';

        return $verifiedAt === ''
            ? ["{$name}: a retired lesson must name the commit its snippets were last true at in <meta name=\"lesson-verified-at\">"]
            : [];
    }

    if ($status !== STATUS_CURRENT) {
        return ["{$name}: <meta name=\"lesson-status\"> must be \"".STATUS_CURRENT.'" or "'.STATUS_RETIRED.'"'];
    }

    $problems = [];

    foreach ($document->querySelectorAll('[data-source]') as $snippet) {
        $problems = [...$problems, ...checkSnippet($name, $snippet->getAttribute('data-source') ?? '', $snippet->textContent ?? '')];
    }

    return $problems;
}

/**
 * @return list<string>
 */
function checkSnippet(string $lesson, string $source, string $quoted): array
{
    $file = realpath(ROOT.$source);

    if ($source === '' || $file === false || ! is_file($file) || ! str_starts_with($file, (string) realpath(ROOT))) {
        return ["{$lesson}: data-source \"{$source}\" is not a file in the repository"];
    }

    $lines = linesOf((string) file_get_contents($file));
    $cursor = 0;

    foreach (fragmentsOf($quoted) as $fragment) {
        $found = findRun($lines, $fragment, $cursor);

        if ($found === null) {
            return ["{$lesson}: {$source} no longer contains the quoted lines starting with:\n    ".trim($fragment[0])];
        }

        $cursor = $found + count($fragment);
    }

    return [];
}

/**
 * The quoted code split at elision lines, each fragment without its leading or
 * trailing blank lines.
 *
 * @return list<non-empty-list<string>>
 */
function fragmentsOf(string $quoted): array
{
    $fragments = [[]];

    foreach (linesOf($quoted) as $line) {
        if (trim($line) === ELISION) {
            $fragments[] = [];

            continue;
        }

        $fragments[array_key_last($fragments)][] = $line;
    }

    return array_values(array_filter(array_map(trimBlankLines(...), $fragments)));
}

/**
 * @param  list<string>  $lines
 * @return list<string>
 */
function trimBlankLines(array $lines): array
{
    while ($lines !== [] && $lines[0] === '') {
        array_shift($lines);
    }

    while ($lines !== [] && $lines[array_key_last($lines)] === '') {
        array_pop($lines);
    }

    return $lines;
}

/**
 * @return list<string>
 */
function linesOf(string $text): array
{
    return array_map(rtrim(...), explode("\n", str_replace("\r\n", "\n", $text)));
}

/**
 * The index of the first line, at or after $from, where $run appears as
 * consecutive whole lines.
 *
 * @param  list<string>  $lines
 * @param  list<string>  $run
 */
function findRun(array $lines, array $run, int $from): ?int
{
    $last = count($lines) - count($run);

    for ($start = $from; $start <= $last; $start++) {
        if (array_slice($lines, $start, count($run)) === $run) {
            return $start;
        }
    }

    return null;
}

/**
 * @param  list<string>  $lessons
 * @return list<string>
 */
function checkIndex(array $lessons): array
{
    $index = LESSONS.'index.html';

    if (! is_file($index)) {
        return ['documentation/lessons/index.html is missing'];
    }

    $document = HTMLDocument::createFromFile($index, LIBXML_NOERROR);
    $linked = [];

    foreach ($document->querySelectorAll('a[href]') as $link) {
        $linked[] = $link->getAttribute('href');
    }

    $unlinked = array_diff(array_map(basename(...), $lessons), $linked);

    return array_values(array_map(
        static fn (string $lesson): string => "documentation/lessons/index.html does not link {$lesson}",
        $unlinked,
    ));
}

// ------------------------------------------------------------------- entry --

$lessons = glob(LESSONS.'[0-9][0-9]-*.html') ?: [];

$problems = [
    ...array_merge(...array_map(checkLesson(...), $lessons)),
    ...checkIndex($lessons),
];

foreach ($problems as $problem) {
    echo $problem."\n";
}

if ($problems !== []) {
    echo "Update the lesson if it still teaches the same thing, or retire it (.ai/rules/lessons.md).\n";

    exit(1);
}

echo 'documentation/lessons is up to date ('.count($lessons).(count($lessons) === 1 ? ' lesson' : ' lessons').")\n";

exit(0);
