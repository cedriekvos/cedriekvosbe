<?php

use App\Livewire\Admin\PageForm;
use App\Pages\Repositories\PagesRepository;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

covers(PageForm::class);

beforeEach(function () {
    Storage::fake('pages');
});

describe('mount', function () {
    it('starts a new page as an empty draft without a preview', function () {
        $component = Livewire::test(PageForm::class);

        $component->assertSet('originalSlug', null)
            ->assertSet('title', '')
            ->assertSet('slug', '')
            ->assertSet('body', '')
            ->assertSet('isDraft', true)
            ->assertViewHas('previewSlug', null);
    });

    it('fills the form with a stored published page', function () {
        writePageFile('about', 'About', 'Who I am');

        $component = Livewire::test(PageForm::class, ['slug' => 'about']);

        $component->assertSet('originalSlug', 'about')
            ->assertSet('title', 'About')
            ->assertSet('slug', 'about')
            ->assertSet('body', "Who I am\n")
            ->assertSet('isDraft', false)
            ->assertViewHas('previewSlug', 'about');
    });

    it('fills the form with a stored draft under its slug without the draft prefix', function () {
        writePageFile('draft-wip', 'Work in progress');

        $component = Livewire::test(PageForm::class, ['slug' => 'wip']);

        $component->assertSet('originalSlug', 'draft-wip')
            ->assertSet('slug', 'wip')
            ->assertSet('isDraft', true)
            ->assertViewHas('previewSlug', 'wip');
    });

    it('aborts with 404 when the page does not exist', function () {
        $component = Livewire::test(PageForm::class, ['slug' => 'missing']);

        $component->assertNotFound();
    });
});

describe('fillSlugFromTitle', function () {
    it('derives the slug from the title while the slug is empty', function () {
        $component = Livewire::test(PageForm::class)
            ->set('title', 'Hello World')
            ->call('fillSlugFromTitle');

        $component->assertSet('slug', 'hello-world');
    });

    it('keeps a slug the editor already filled in', function () {
        $component = Livewire::test(PageForm::class)
            ->set('slug', 'chosen')
            ->set('title', 'Hello World')
            ->call('fillSlugFromTitle');

        $component->assertSet('slug', 'chosen');
    });
});

describe('save', function () {
    it('creates a published page, then navigates to the page list with a status', function () {
        $component = Livewire::test(PageForm::class)
            ->set('title', 'Fresh')
            ->set('slug', 'fresh')
            ->set('body', '# Heading')
            ->set('isDraft', false)
            ->call('save');

        $component->assertRedirectToRoute('admin.pages.index');
        expect($component->effects)->toHaveKey('redirectUsingNavigate', true);
        expect(session('status'))->toBe('Page created.');
        expect(Storage::disk('pages')->files())->toBe(['fresh.md']);
        expect(app(PagesRepository::class)->find('fresh'))
            ->title->toBe('Fresh')
            ->body->toBe("# Heading\n");
    });

    it('stores a new draft under the draft prefix', function () {
        $component = Livewire::test(PageForm::class)
            ->set('title', 'Draftie')
            ->set('slug', 'draftie')
            ->set('body', 'b')
            ->call('save');

        $component->assertRedirectToRoute('admin.pages.index');
        expect(Storage::disk('pages')->files())->toBe(['draft-draftie.md']);
    });

    it('updates a stored page, then navigates to the page list with a status', function () {
        writePageFile('about', 'About', 'Old body');

        $component = Livewire::test(PageForm::class, ['slug' => 'about'])
            ->set('title', 'About me')
            ->set('body', 'New body')
            ->call('save');

        $component->assertRedirectToRoute('admin.pages.index');
        expect($component->effects)->toHaveKey('redirectUsingNavigate', true);
        expect(session('status'))->toBe('Page updated.');
        expect(Storage::disk('pages')->files())->toBe(['about.md']);
        expect(app(PagesRepository::class)->find('about'))
            ->title->toBe('About me')
            ->body->toBe("New body\n");
    });

    it('renames the stored page when the slug changes', function () {
        writePageFile('about', 'About');

        Livewire::test(PageForm::class, ['slug' => 'about'])
            ->set('slug', 'over')
            ->call('save');

        expect(Storage::disk('pages')->files())->toBe(['over.md']);
    });

    it('publishes a stored draft', function () {
        writePageFile('draft-wip', 'Work in progress');

        Livewire::test(PageForm::class, ['slug' => 'wip'])
            ->set('isDraft', false)
            ->call('save');

        expect(Storage::disk('pages')->files())->toBe(['wip.md']);
    });

    it('rejects a slug another page already uses', function () {
        writePageFile('taken', 'Taken', 'Original');

        $component = Livewire::test(PageForm::class)
            ->set('title', 'Another')
            ->set('slug', 'taken')
            ->set('body', 'b')
            ->set('isDraft', false)
            ->call('save');

        $component->assertHasErrors(['slug'])
            ->assertSee('A page with this slug already exists.')
            ->assertNoRedirect();
        expect(Storage::disk('pages')->files())->toBe(['taken.md']);
        expect(app(PagesRepository::class)->find('taken')->title)->toBe('Taken');
    });

    it('rejects invalid input without storing anything', function (string $field, string $value, string $rule) {
        $component = Livewire::test(PageForm::class)
            ->set(['title' => 'Title', 'slug' => 'slug', 'body' => 'b'])
            ->set($field, $value)
            ->call('save');

        $component->assertHasErrors([$field => $rule])
            ->assertNoRedirect();
        expect(Storage::disk('pages')->files())->toBe([]);
    })->with([
        'empty title' => ['title', '', 'required'],
        'title over 255 characters' => ['title', str_repeat('a', 256), 'max'],
        'empty slug' => ['slug', '', 'required'],
        'slug over 255 characters' => ['slug', str_repeat('a', 256), 'max'],
        'slug with uppercase letters' => ['slug', 'Hello', 'regex'],
        'the slug draft' => ['slug', 'draft', 'not_in'],
        'slug with the draft prefix' => ['slug', 'draft-wip', 'doesnt_start_with'],
        'empty body' => ['body', '', 'required'],
    ]);
});
