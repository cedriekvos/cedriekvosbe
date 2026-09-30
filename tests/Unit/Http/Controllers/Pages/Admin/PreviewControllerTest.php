<?php

use App\Http\Controllers\Pages\Admin\PreviewController;
use App\Pages\DraftSlug;
use App\Pages\PagePreviewFinder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

covers(PreviewController::class);

beforeEach(function () {
    Storage::fake('pages');
    $this->finder = new PagePreviewFinder(pageGetRepository(), new DraftSlug);
});

it('returns the pages.show view flagged as a preview, drafts included', function () {
    writePageFile('draft-welcome', 'T');

    $response = (new PreviewController)($this->finder, 'welcome');

    expect($response)
        ->toBeInstanceOf(View::class)
        ->and($response->name())->toBe('pages.show')
        ->and($response->getData()['page']->slug)->toBe('draft-welcome')
        ->and($response->getData()['isPreview'])->toBeTrue();
});

it('aborts with a 404 when the page does not exist', function () {
    expect(fn () => (new PreviewController)($this->finder, 'missing'))
        ->toThrow(NotFoundHttpException::class);
});
