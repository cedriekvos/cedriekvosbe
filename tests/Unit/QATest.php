<?php

use App\QA;

covers(QA::class);

test('it returns true', function (): void {
    $qa = new QA;
    expect($qa->isTrue())->toBeTrue();
});
