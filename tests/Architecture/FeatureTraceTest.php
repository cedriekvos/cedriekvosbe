<?php

use function Documentation\Architecture\scanFeatureTrace;
use function Documentation\Architecture\traceGaps;

require_once __DIR__.'/../../documentation/architecture/trace.php';

/*
 * Holds the leesmij → spec → test chain the Feature trace page draws
 * (documentation/architecture/features.html): every scenario is named by an
 * acceptance test's comment, every such comment names a real scenario, and every
 * spec has its leesmij. Tests that name no scenario are allowed — the page lists
 * them, but they break nothing.
 */

it('reads the specs, leesmij and acceptance tests it guards', function () {
    $trace = scanFeatureTrace(dirname(__DIR__, 2));

    expect($trace['features'])->not->toBeEmpty()
        ->and($trace['testCount'])->toBeGreaterThan(0);
});

it('leaves no break in the feature trace', function (array $gap) {
    $report = array_map(
        static fn (array $item): string => '  '.$item['ref'].($item['detail'] === '' ? '' : ' — '.$item['detail']),
        $gap['items'],
    );

    expect($gap['items'])->toBeEmpty($gap['title'].': '.$gap['note']."\n".implode("\n", $report));
})->with(function (): array {
    $broken = array_filter(traceGaps(scanFeatureTrace(dirname(__DIR__, 2))), static fn (array $gap): bool => $gap['broken']);

    return array_combine(array_column($broken, 'title'), array_map(static fn (array $gap): array => [$gap], $broken));
});
