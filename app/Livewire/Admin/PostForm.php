<?php

namespace App\Livewire\Admin;

use App\Blog\DraftSlug;
use App\Blog\Post;
use App\Blog\Repositories\PostsRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class PostForm extends Component
{
    public ?string $originalSlug = null;

    public string $title = '';

    public string $slug = '';

    public string $date = '';

    public string $excerpt = '';

    public string $body = '';

    public bool $isDraft = true;

    public bool $isFeatured = false;

    public function mount(PostsRepository $posts, DraftSlug $draftSlug, ?string $slug = null): void
    {
        if ($slug === null) {
            $this->date = Carbon::now()->format('Y-m-d');

            return;
        }

        $post = $posts->find($slug);
        abort_if(! $post instanceof Post, 404);

        $this->originalSlug = $slug;
        $this->isDraft = $post->is_draft;
        $this->slug = $draftSlug->strip($slug);
        $this->title = $post->title;
        $this->date = $post->date !== '' ? $post->date : Carbon::now()->format('Y-m-d');
        $this->excerpt = $post->excerpt;
        $this->body = $post->body;
        $this->isFeatured = $post->is_featured;
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
            // published post under a draft slug, which reads back as a draft.
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/', Rule::notIn(['draft']), 'doesnt_start_with:'.DraftSlug::PREFIX],
            'date' => ['required', 'date_format:Y-m-d'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string'],
            'isDraft' => ['boolean'],
            'isFeatured' => ['boolean'],
        ];
    }

    public function save(PostsRepository $posts, DraftSlug $draftSlug): mixed
    {
        /** @var array{title: string, slug: string, date: string, excerpt?: string|null, body: string, isDraft: bool, isFeatured: bool} $data */
        $data = $this->validate();

        $finalSlug = $draftSlug->apply($data['slug'], $this->isDraft);
        if ($finalSlug !== $this->originalSlug && $posts->exists($finalSlug)) {
            $this->addError('slug', 'A post with this slug already exists.');

            return null;
        }

        $attrs = [
            'title' => $data['title'],
            'date' => $data['date'],
            'excerpt' => $data['excerpt'] ?? null,
            'featured' => $data['isFeatured'],
        ];

        if ($this->originalSlug === null) {
            $posts->create($attrs, $data['body'], $data['slug'], $this->isDraft);
            session()->flash('status', 'Post created.');
        } else {
            $posts->update($this->originalSlug, $attrs, $data['body'], $data['slug'], $this->isDraft);
            session()->flash('status', 'Post updated.');
        }

        return $this->redirectRoute('admin.posts.index', navigate: true);
    }

    #[Layout('components.layouts.admin')]
    #[Title('Edit post — Admin')]
    public function render(): View
    {
        return view('livewire.admin.post-form');
    }
}
