<?php

namespace App\Livewire\Admin;

use App\Microblog\Message;
use App\Microblog\Repositories\MessagesRepository;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class MessageForm extends Component
{
    public ?string $id = null;

    public string $body = '';

    public function mount(MessagesRepository $messages, ?string $id = null): void
    {
        if ($id === null) {
            return;
        }

        $message = $messages->find($id);
        abort_if(! $message instanceof Message, 404);

        $this->id = $id;
        $this->body = $message->body;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:280'],
        ];
    }

    public function save(MessagesRepository $messages): mixed
    {
        /** @var array{body: string} $data */
        $data = $this->validate();

        if ($this->id === null) {
            $messages->create($data['body']);
            session()->flash('status', 'Message posted.');
        } else {
            $messages->update($this->id, $data['body']);
            session()->flash('status', 'Message updated.');
        }

        return $this->redirectRoute('admin.messages.index', navigate: true);
    }

    #[Layout('components.layouts.admin')]
    #[Title('Compose message — Admin')]
    public function render(): View
    {
        return view('livewire.admin.message-form');
    }
}
