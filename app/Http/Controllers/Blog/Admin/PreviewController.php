<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blog\Admin;

use App\Blog\Post;
use App\Blog\PostPreviewFinder;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class PreviewController extends Controller
{
    public function __invoke(PostPreviewFinder $postPreviewFinder, string $slug): View
    {
        $post = $postPreviewFinder->find($slug);

        abort_if(! $post instanceof Post, 404);

        return view('blog.show', ['post' => $post, 'isPreview' => true]);
    }
}
