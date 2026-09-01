<?php

namespace App\Http\Controllers\Blog\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class BlogRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect('/', 302);
    }
}
