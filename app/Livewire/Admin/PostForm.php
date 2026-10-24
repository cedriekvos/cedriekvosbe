<?php

namespace App\Livewire\Admin;

use App\Blog\DraftSlug;
use App\Blog\Post;
use App\Blog\PostPreviewFinder;
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

    public string $title = ''; // @pest-mutate-ignore: EmptyStringToNotEmpty

    public string $slug = ''; // @pest-mutate-ignore: EmptyStringToNotEmpty

    public string $date = ''; // @pest-mutate-ignore: EmptyStringToNotEmpty

    public string $excerpt = ''; // @pest-mutate-ignore: EmptyStringToNotEmpty

    public string $body = ''; // @pest-mutate-ignore: EmptyStringToNotEmpty

    public bool $isDraft = true; // @pest-mutate-ignore: TrueToFalse

    public bool $isFeatured = false; // @pest-mutate-ignore: FalseToTrue

    public function mount(PostPreviewFinder $postPreviewFinder, DraftSlug $draftSlug, ?string $slug = null): void
    {
        if ($slug === null) {
            $this->date = Carbon::now()->format('Y-m-d');

            return;
        }

        $post = $postPreviewFinder->find($slug);
        abort_if(! $post instanceof Post, 404);

        $this->originalSlug = $post->slug;
        $this->isDraft = $post->is_draft;
        $this->slug = $draftSlug->strip($post->slug);
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
            'title' => ['required', 'max:255'],
            // The draft prefix is owned by storage: accepting it here would file a
            // published post under a draft slug, which reads back as a draft.
            'slug' => ['required', 'max:255', 'regex:/^[a-z0-9\-]+$/', Rule::notIn(['draft']), 'doesnt_start_with:'.DraftSlug::PREFIX],
            'date' => ['required', 'date_format:Y-m-d'],
            'excerpt' => ['max:300'],
            'body' => ['required'],
        ];
    }

    public function save(PostsRepository $posts): mixed
    {
        /** @var array{title: string, slug: string, date: string, excerpt: string, body: string} $data */
        $data = $this->validate();

        if ($posts->isSlugTaken($data['slug'], $this->isDraft, $this->originalSlug)) {
            $this->addError('slug', 'A post with this slug already exists.');

            return null;
        }

        $attrs = [
            'title' => $data['title'],
            'date' => $data['date'],
            'excerpt' => $data['excerpt'],
            'featured' => $this->isFeatured,
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
    public function render(DraftSlug $draftSlug): View
    {
        return view('livewire.admin.post-form', [
            'previewSlug' => $this->originalSlug === null ? null : $draftSlug->strip($this->originalSlug),
        ]);
    }
}
