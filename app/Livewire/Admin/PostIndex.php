<?php

namespace App\Livewire\Admin;

use App\Blog\Repositories\PostsRepository;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class PostIndex extends Component
{
    public function delete(PostsRepository $posts, string $slug): void
    {
        $posts->delete($slug);

        session()->flash('status', "Post [{$slug}] deleted.");
    }

    #[Layout('components.layouts.admin')]
    #[Title('Posts — Admin')]
    public function render(PostsRepository $posts): View
    {
        return view('livewire.admin.post-index', [
            'posts' => $posts->allIncludeDrafts(),
        ]);
    }
}
