<?php

use App\Livewire\Admin\MessageForm;
use App\Livewire\Admin\MessageIndex;
use Livewire\Livewire;

usesFakeMicroblogRepository();
usesFakePostsRepository();
signsInAsEditor();

/**
 * The links in a rendered admin screen that point at `$href`, so a scenario can
 * single out a link that came from a message body among the screen's own
 * navigation, edit and create links.
 *
 * @return array<int, array{href: string, text: string, target: string, rel: string}>
 */
function adminLinksTo(string $html, string $href): array
{
    return array_values(array_filter(
        htmlLinks($html),
        fn (array $link): bool => $link['href'] === $href,
    ));
}

// message_links.feature — Scenario: A bare URL in the message list is rendered as a clickable link
it('renders a bare URL in the admin message list as a clickable link', function () {
    $html = renderedMessageInAdminList('Bronnen: https://php.net/manual');

    $links = adminLinksTo($html, 'https://php.net/manual');

    expect($links)->toHaveCount(1);
    expect($links[0]['text'])->toBe('https://php.net/manual');
    expect(htmlTextOutsideLinks($html))->toContain('Bronnen:');
});

// message_links.feature — Scenario: A message link does not replace the row's own edit and delete controls
it('keeps the edit and delete controls on a row whose message contains a link', function () {
    $id = postMessage('Zie https://php.net');

    Livewire::test(MessageIndex::class)
        ->assertSee('[edit]')
        ->assertSee('[delete]')
        ->assertSee(route('admin.messages.edit', ['id' => $id]));
});

// message_links.feature — Scenario: The composer shows the raw body, never a rendered link
it('shows the raw body in the composer rather than a rendered link', function () {
    $id = postMessage('Zie https://php.net');

    $component = Livewire::test(MessageForm::class, ['id' => $id])
        ->assertSet('body', 'Zie https://php.net');

    expect(adminLinksTo($component->html(), 'https://php.net'))->toBeEmpty();
});

// message_links.feature — Scenario Outline: The 280 character limit still counts the raw body, URL characters included (280)
it('posts a message of exactly 280 raw characters ending in a URL', function () {
    Livewire::test(MessageForm::class)
        ->set('body', bodyOfLengthEndingInUrl(280))
        ->call('save')
        ->assertHasNoErrors();

    expect(session('status'))->toBe('Message posted.');
});

// message_links.feature — Scenario Outline: The 280 character limit still counts the raw body, URL characters included (281)
it('rejects a message of 281 raw characters ending in a URL', function () {
    Livewire::test(MessageForm::class)
        ->set('body', bodyOfLengthEndingInUrl(281))
        ->call('save')
        ->assertHasErrors(['body']);
});

/**
 * Build a message body of exactly `$length` characters that ends in a URL, so
 * the limit is exercised against a body whose tail is a link.
 */
function bodyOfLengthEndingInUrl(int $length): string
{
    $url = 'https://php.net/manual';
    $body = str_repeat('a', $length - mb_strlen($url) - 1).' '.$url;

    expect(mb_strlen($body))->toBe($length);

    return $body;
}
