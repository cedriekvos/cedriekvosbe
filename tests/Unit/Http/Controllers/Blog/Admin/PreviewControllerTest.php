<?php

use App\Blog\DraftSlug;
use App\Blog\PostPreviewFinder;
use App\Blog\Repositories\PostGetRepository;
use App\Http\Controllers\Blog\Admin\PreviewController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

covers(PreviewController::class);

beforeEach(function () {
    Storage::fake('posts');
    $this->finder = new PostPreviewFinder(app(PostGetRepository::class), new DraftSlug);
});

it('returns the blog.show view flagged as a preview, drafts included', function () {
    writePostFile('draft-welcome', 'T', '2026-05-01');

    $response = (new PreviewController)($this->finder, 'welcome');

    expect($response)
        ->toBeInstanceOf(View::class)
        ->and($response->name())->toBe('blog.show')
        ->and($response->getData()['post']->slug)->toBe('draft-welcome')
        ->and($response->getData()['isPreview'])->toBeTrue();
});

it('aborts with a 404 when the post does not exist', function () {
    expect(fn () => (new PreviewController)($this->finder, 'missing'))
        ->toThrow(NotFoundHttpException::class);
});
