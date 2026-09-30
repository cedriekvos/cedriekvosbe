<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pages\Admin;

use App\Http\Controllers\Controller;
use App\Pages\Page;
use App\Pages\PagePreviewFinder;
use Illuminate\Contracts\View\View;

final class PreviewController extends Controller
{
    public function __invoke(PagePreviewFinder $pagePreviewFinder, string $slug): View
    {
        $page = $pagePreviewFinder->find($slug);

        abort_if(! $page instanceof Page, 404);

        return view('pages.show', ['page' => $page, 'isPreview' => true]);
    }
}
