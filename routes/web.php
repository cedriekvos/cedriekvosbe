<?php

use App\Http\Controllers\Blog\Frontend\BlogRedirectController;
use App\Http\Controllers\Blog\Frontend\IndexController;
use App\Http\Controllers\Blog\Frontend\ShowController;
use App\Http\Controllers\Pages\Frontend\ShowController as PageShowController;
use App\Livewire\Admin\AboutForm;
use App\Livewire\Admin\MessageForm;
use App\Livewire\Admin\MessageIndex;
use App\Livewire\Admin\PageForm;
use App\Livewire\Admin\PageIndex;
use App\Livewire\Admin\PostForm;
use App\Livewire\Admin\PostIndex;
use App\Livewire\Admin\ScratchpadForm;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexController::class);
Route::get('/blog', BlogRedirectController::class);

// Slugs and ids address files on disk, so they are constrained to the shapes the
// admin forms can produce — a path separator or a dot never reaches storage.
Route::get('/blog/{slug}', ShowController::class)->where('slug', '[a-z0-9\-]+');

Route::redirect('/dashboard', '/admin')->middleware('auth')->name('dashboard');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', PostIndex::class)->name('posts.index');
    Route::get('/posts/new', PostForm::class)->name('posts.create');
    Route::get('/posts/{slug}/edit', PostForm::class)->name('posts.edit')->where('slug', '[a-z0-9\-]+');
    Route::get('/pages', PageIndex::class)->name('pages.index');
    Route::get('/pages/new', PageForm::class)->name('pages.create');
    Route::get('/pages/{slug}/edit', PageForm::class)->name('pages.edit')->where('slug', '[a-z0-9\-]+');
    Route::get('/about/edit', AboutForm::class)->name('about.edit');
    Route::get('/scratchpad/edit', ScratchpadForm::class)->name('scratchpad.edit');
    Route::get('/messages', MessageIndex::class)->name('messages.index');
    Route::get('/messages/new', MessageForm::class)->name('messages.create');
    Route::get('/messages/{id}/edit', MessageForm::class)->name('messages.edit')->where('id', '[0-9A-Za-z]+');
});

require __DIR__.'/auth.php';

// Pages are reachable at a root-level slug, so this must be registered after
// every other route (including auth.php's) or it would shadow /login, /admin,
// /blog, etc. instead of the other way around.
Route::get('/{slug}', PageShowController::class)->where('slug', '[a-z0-9\-]+');
