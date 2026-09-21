<?php

namespace App\Livewire\Admin;

use App\Pages\DraftSlug;
use App\Pages\Page;
use App\Pages\Repositories\PagesRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class PageForm extends Component
{
    public ?string $originalSlug = null;

    public string $title = '';

    public string $slug = '';

    public string $body = '';

    public bool $isDraft = true;

    public function mount(PagesRepository $pages, DraftSlug $draftSlug, ?string $slug = null): void
    {
        if ($slug === null) {
            return;
        }

        $page = $pages->find($slug);
        abort_if(! $page instanceof Page, 404);

        $this->originalSlug = $slug;
        $this->isDraft = $page->is_draft;
        $this->slug = $draftSlug->strip($slug);
        $this->title = $page->title;
        $this->body = $page->body;
    }

    public function fillSlugFromTitle(): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($this->title);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // The draft prefix is owned by storage: accepting it here would file a
            // published page under a draft slug, which reads back as a draft.
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/', Rule::notIn(['draft']), 'doesnt_start_with:'.DraftSlug::PREFIX],
            'body' => ['required', 'string'],
            'isDraft' => ['boolean'],
        ];
    }

    public function save(PagesRepository $pages, DraftSlug $draftSlug): mixed
    {
        /** @var array{title: string, slug: string, body: string, isDraft: bool} $data */
        $data = $this->validate();

        $finalSlug = $draftSlug->apply($data['slug'], $this->isDraft);
        if ($finalSlug !== $this->originalSlug && $pages->exists($finalSlug)) {
            $this->addError('slug', 'A page with this slug already exists.');

            return null;
        }

        $attrs = [
            'title' => $data['title'],
        ];

        if ($this->originalSlug === null) {
            $pages->create($attrs, $data['body'], $data['slug'], $this->isDraft);
            session()->flash('status', 'Page created.');
        } else {
            $pages->update($this->originalSlug, $attrs, $data['body'], $data['slug'], $this->isDraft);
            session()->flash('status', 'Page updated.');
        }

        return $this->redirectRoute('admin.pages.index', navigate: true);
    }

    #[Layout('components.layouts.admin')]
    #[Title('Edit page — Admin')]
    public function render(): View
    {
        return view('livewire.admin.page-form');
    }
}
