<?php

namespace App\Http\Controllers\Pages\Frontend;

use App\Http\Controllers\Controller;
use App\Pages\Page;
use App\Pages\Repositories\PageGetRepository;
use Illuminate\Contracts\View\View;

class ShowController extends Controller
{
    public function __invoke(PageGetRepository $pageGetRepository, string $slug): View
    {
        $page = $pageGetRepository->find($slug);

        abort_if(! $page instanceof Page, 404);

        return view('pages.show', ['page' => $page]);
    }
}
