<?php

namespace App\Livewire\Admin;

use App\Scratchpad\Repositories\ScratchpadRepository;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ScratchpadForm extends Component
{
    public string $content = '';

    public function mount(ScratchpadRepository $scratchpad): void
    {
        $this->content = $scratchpad->get()->content;
    }

    public function save(ScratchpadRepository $scratchpad): mixed
    {
        $scratchpad->save($this->content);
        session()->flash('status', 'Scratchpad updated.');

        return $this->redirectRoute('admin.posts.index', navigate: true);
    }

    #[Layout('components.layouts.admin')]
    #[Title('Edit scratchpad — Admin')]
    public function render(): View
    {
        return view('livewire.admin.scratchpad-form');
    }
}
