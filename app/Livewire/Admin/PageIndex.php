<?php

namespace App\Livewire\Admin;

use App\Pages\Repositories\PagesRepository;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class PageIndex extends Component
{
    public function delete(PagesRepository $pages, string $slug): void
    {
        $pages->delete($slug);

        session()->flash('status', "Page [{$slug}] deleted.");
    }

    #[Layout('components.layouts.admin')]
    #[Title('Pages — Admin')]
    public function render(PagesRepository $pages): View
    {
        return view('livewire.admin.page-index', [
            'pages' => $pages->allIncludeDrafts(),
        ]);
    }
}
