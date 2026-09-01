<?php

use App\Blog\PostReadTimeCalculator;

covers(PostReadTimeCalculator::class);

beforeEach(function () {
    $this->calculator = new PostReadTimeCalculator;
});

it('rounds the read time up to the nearest whole minute', function (int $wordCount, int $readTime) {
    $content = '<p>'.trim(str_repeat('word ', $wordCount)).'</p>';

    expect($this->calculator->calculateMinutes($content))->toBe($readTime);
})->with([
    '1 word' => [1, 1],
    '199 words' => [199, 1],
    '200 words' => [200, 1],
    '201 words' => [201, 2],
    '400 words' => [400, 2],
    '401 words' => [401, 3],
    '1000 words' => [1000, 5],
]);

it('floors at a minimum of 1 minute for empty content', function () {
    expect($this->calculator->calculateMinutes(''))->toBe(1);
});

it('strips html tags before counting words', function () {
    expect($this->calculator->calculateMinutes('<p><strong>one</strong> two</p>'))->toBe(1);
});

it('excludes tag attribute text such as image alt text from the word count', function () {
    $altText = trim(str_repeat('word ', 250));

    expect($this->calculator->calculateMinutes("<p><img src=\"image.png\" alt=\"{$altText}\" /></p><p>Short post</p>"))->toBe(1);
});

it('collapses whitespace between words', function () {
    expect($this->calculator->calculateMinutes("<p>one\n\ntwo   three</p>"))->toBe(1);
});
