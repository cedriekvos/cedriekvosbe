<?php

namespace App\Livewire\Admin;

use App\Microblog\Repositories\MessagesRepository;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class MessageIndex extends Component
{
    public function delete(MessagesRepository $messages, string $id): void
    {
        $messages->delete($id);

        session()->flash('status', 'Message deleted.');
    }

    #[Layout('components.layouts.admin')]
    #[Title('Messages — Admin')]
    public function render(MessagesRepository $messages): View
    {
        return view('livewire.admin.message-index', [
            'messages' => $messages->all(),
        ]);
    }
}
