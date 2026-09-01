<?php

namespace App\Http\Controllers\Blog\Frontend;

use App\Blog\Post;
use App\Blog\Repositories\PostGetRepository;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ShowController extends Controller
{
    public function __invoke(PostGetRepository $postGetRepository, string $slug): View
    {
        $post = $postGetRepository->find($slug);

        abort_if(! $post instanceof Post, 404);

        return view('blog.show', ['post' => $post]);
    }
}
