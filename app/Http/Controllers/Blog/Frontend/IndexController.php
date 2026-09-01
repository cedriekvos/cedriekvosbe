<?php

namespace App\Http\Controllers\Blog\Frontend;

use App\About\Repositories\AboutRepository;
use App\Blog\Repositories\PostGetRepository;
use App\Http\Controllers\Controller;
use App\Microblog\Repositories\MessagesRepository;
use Illuminate\Contracts\View\View;

class IndexController extends Controller
{
    public function __invoke(PostGetRepository $postGetRepository, AboutRepository $aboutRepository, MessagesRepository $messagesRepository): View
    {
        return view('home', [
            'posts' => $postGetRepository->getAllExcludingDrafts(),
            'about' => $aboutRepository->get(),
            'messages' => $messagesRepository->all(),
        ]);
    }
}
