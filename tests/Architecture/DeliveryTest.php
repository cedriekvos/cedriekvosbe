<?php

use Illuminate\Console\Command;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Mail\Mailable;
use Illuminate\View\Component;
use Livewire\Component as LivewireComponent;

/*
 * The delivery layer (documentation/architecture/modules.html): the only layer that
 * knows about requests. Entry points stay thin — they resolve a domain collaborator
 * and hand back a view, a redirect or an exit code.
 */

arch('controllers are suffixed and extend the base controller')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring('App\Http\Controllers\Controller');

arch('frontend controllers are single-action')
    ->expect('App\Http\Controllers\Blog\Frontend')
    ->toBeInvokable();

arch('form requests are suffixed and extend FormRequest')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request')
    ->toExtend(FormRequest::class);

arch('livewire components extend the framework component and render a view')
    ->expect('App\Livewire')
    ->toExtend(LivewireComponent::class)
    ->toHaveMethod('render');

arch('console commands are final, strictly typed and suffixed')
    ->expect('App\Console\Commands')
    ->toBeFinal()
    ->toUseStrictTypes()
    ->toHaveSuffix('Command')
    ->toExtend(Command::class);

arch('mailables are final, strictly typed and extend Mailable')
    ->expect('App\Mail')
    ->toBeFinal()
    ->toUseStrictTypes()
    ->toExtend(Mailable::class);

arch('view components extend the framework component')
    ->expect('App\View\Components')
    ->toExtend(Component::class);
