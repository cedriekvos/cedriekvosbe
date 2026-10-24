<?php

use App\Blog\Repositories\PostsRepository;
use App\Livewire\Admin\PostForm;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(PostForm::class);

beforeEach(function () {
    Storage::fake('posts');
});

describe('mount', function () {
    it('starts a new post as an empty, unfeatured draft dated today without a preview', function () {
        $this->travelTo(Carbon::parse('2026-05-13 10:00:00'));

        $component = Livewire::test(PostForm::class);

        $component->assertSet('originalSlug', null)
            ->assertSet('title', '')
            ->assertSet('slug', '')
            ->assertSet('date', '2026-05-13')
            ->assertSet('excerpt', '')
            ->assertSet('body', '')
            ->assertSet('isDraft', true)
            ->assertSet('isFeatured', false)
            ->assertViewHas('previewSlug', null);
    });

    it('fills the form with a stored published post', function () {
        writePostFile('hello', 'Hello', '2026-01-01', excerpt: 'Short', body: 'Long', isFeatured: true);

        $component = Livewire::test(PostForm::class, ['slug' => 'hello']);

        $component->assertSet('originalSlug', 'hello')
            ->assertSet('title', 'Hello')
            ->assertSet('slug', 'hello')
            ->assertSet('date', '2026-01-01')
            ->assertSet('excerpt', 'Short')
            ->assertSet('body', "Long\n")
            ->assertSet('isDraft', false)
            ->assertSet('isFeatured', true)
            ->assertViewHas('previewSlug', 'hello');
    });

    it('fills the form with a stored draft under its slug without the draft prefix', function () {
        writePostFile('draft-wip', 'Work in progress', '2026-01-01');

        $component = Livewire::test(PostForm::class, ['slug' => 'wip']);

        $component->assertSet('originalSlug', 'draft-wip')
            ->assertSet('slug', 'wip')
            ->assertSet('isDraft', true)
            ->assertViewHas('previewSlug', 'wip');
    });

    it('dates a stored post without a date today', function () {
        $this->travelTo(Carbon::parse('2026-05-13 10:00:00'));
        writePostFile('undated', 'Undated', '');

        $component = Livewire::test(PostForm::class, ['slug' => 'undated']);

        $component->assertSet('date', '2026-05-13');
    });

    it('aborts with 404 when the post does not exist', function () {
        $component = Livewire::test(PostForm::class, ['slug' => 'missing']);

        $component->assertNotFound();
    });
});

describe('fillSlugFromTitle', function () {
    it('derives the slug from the title while the slug is empty', function () {
        $component = Livewire::test(PostForm::class)
            ->set('title', 'Hello World')
            ->call('fillSlugFromTitle');

        $component->assertSet('slug', 'hello-world');
    });

    it('keeps a slug the editor already filled in', function () {
        $component = Livewire::test(PostForm::class)
            ->set('slug', 'chosen')
            ->set('title', 'Hello World')
            ->call('fillSlugFromTitle');

        $component->assertSet('slug', 'chosen');
    });
});

describe('save', function () {
    it('creates a published post, then navigates to the post list with a status', function () {
        $component = Livewire::test(PostForm::class)
            ->set('title', 'Fresh')
            ->set('slug', 'fresh')
            ->set('date', '2026-05-13')
            ->set('excerpt', 'Short')
            ->set('body', '# Heading')
            ->set('isDraft', false)
            ->set('isFeatured', true)
            ->call('save');

        $component->assertRedirectToRoute('admin.posts.index');
        expect($component->effects)->toHaveKey('redirectUsingNavigate', true);
        expect(session('status'))->toBe('Post created.');
        expect(Storage::disk('posts')->files())->toBe(['fresh.md']);
        expect(app(PostsRepository::class)->find('fresh'))
            ->title->toBe('Fresh')
            ->date->toBe('2026-05-13')
            ->excerpt->toBe('Short')
            ->body->toBe("# Heading\n")
            ->is_featured->toBeTrue();
    });

    it('stores a new draft under the draft prefix', function () {
        $component = Livewire::test(PostForm::class)
            ->set('title', 'Draftie')
            ->set('slug', 'draftie')
            ->set('date', '2026-05-13')
            ->set('body', 'b')
            ->call('save');

        $component->assertRedirectToRoute('admin.posts.index');
        expect(Storage::disk('posts')->files())->toBe(['draft-draftie.md']);
    });

    it('updates a stored post, then navigates to the post list with a status', function () {
        writePostFile('hello', 'Hello', '2026-01-01', excerpt: 'Short', body: 'Old body', isFeatured: true);

        $component = Livewire::test(PostForm::class, ['slug' => 'hello'])
            ->set('title', 'Hello again')
            ->set('date', '2026-02-02')
            ->set('excerpt', 'Shorter')
            ->set('body', 'New body')
            ->set('isFeatured', false)
            ->call('save');

        $component->assertRedirectToRoute('admin.posts.index');
        expect($component->effects)->toHaveKey('redirectUsingNavigate', true);
        expect(session('status'))->toBe('Post updated.');
        expect(Storage::disk('posts')->files())->toBe(['hello.md']);
        expect(app(PostsRepository::class)->find('hello'))
            ->title->toBe('Hello again')
            ->date->toBe('2026-02-02')
            ->excerpt->toBe('Shorter')
            ->body->toBe("New body\n")
            ->is_featured->toBeFalse();
    });

    it('renames the stored post when the slug changes', function () {
        writePostFile('hello', 'Hello', '2026-01-01');

        Livewire::test(PostForm::class, ['slug' => 'hello'])
            ->set('slug', 'hi')
            ->call('save');

        expect(Storage::disk('posts')->files())->toBe(['hi.md']);
    });

    it('publishes a stored draft', function () {
        writePostFile('draft-wip', 'Work in progress', '2026-01-01');

        Livewire::test(PostForm::class, ['slug' => 'wip'])
            ->set('isDraft', false)
            ->call('save');

        expect(Storage::disk('posts')->files())->toBe(['wip.md']);
    });

    it('rejects a slug another post already uses', function () {
        writePostFile('taken', 'Taken', '2026-01-01');

        $component = Livewire::test(PostForm::class)
            ->set('title', 'Another')
            ->set('slug', 'taken')
            ->set('date', '2026-01-02')
            ->set('body', 'b')
            ->set('isDraft', false)
            ->call('save');

        $component->assertHasErrors(['slug'])
            ->assertSee('A post with this slug already exists.')
            ->assertNoRedirect();
        expect(Storage::disk('posts')->files())->toBe(['taken.md']);
        expect(app(PostsRepository::class)->find('taken')->title)->toBe('Taken');
    });

    it('rejects invalid input without storing anything', function (string $field, string $value, string $rule) {
        $component = Livewire::test(PostForm::class)
            ->set(['title' => 'Title', 'slug' => 'slug', 'date' => '2026-01-01', 'body' => 'b'])
            ->set($field, $value)
            ->call('save');

        $component->assertHasErrors([$field => $rule])
            ->assertNoRedirect();
        expect(Storage::disk('posts')->files())->toBe([]);
    })->with([
        'empty title' => ['title', '', 'required'],
        'title over 255 characters' => ['title', str_repeat('a', 256), 'max'],
        'empty slug' => ['slug', '', 'required'],
        'slug over 255 characters' => ['slug', str_repeat('a', 256), 'max'],
        'slug with uppercase letters' => ['slug', 'Hello', 'regex'],
        'the slug draft' => ['slug', 'draft', 'not_in'],
        'slug with the draft prefix' => ['slug', 'draft-wip', 'doesnt_start_with'],
        'empty date' => ['date', '', 'required'],
        'date as day-month-year' => ['date', '01-05-2026', 'date_format'],
        'excerpt over 300 characters' => ['excerpt', str_repeat('a', 301), 'max'],
        'empty body' => ['body', '', 'required'],
    ]);
});
