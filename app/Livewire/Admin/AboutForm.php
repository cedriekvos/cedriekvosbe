<?php

namespace App\Livewire\Admin;

use App\About\Repositories\AboutRepository;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class AboutForm extends Component
{
    public string $heading = '';

    public string $bio = '';

    public function mount(AboutRepository $about): void
    {
        $content = $about->get();

        $this->heading = $content->heading;
        $this->bio = $content->bio_as_markdown;
    }

    public function save(AboutRepository $about): mixed
    {
        $about->save($this->heading, $this->bio);
        session()->flash('status', 'About updated.');

        return $this->redirectRoute('admin.posts.index', navigate: true);
    }

    #[Layout('components.layouts.admin')]
    #[Title('Edit about — Admin')]
    public function render(): View
    {
        return view('livewire.admin.about-form');
    }
}
