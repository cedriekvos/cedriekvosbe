<?php

usesFakePostsRepository();
usesFakeMicroblogRepository();

// homepage_microblog_section.feature — Scenario: Messages are listed newest first in their own section
it('lists messages newest first in their own "tail -n 100 messages.log" section', function () {
    postMessagesInOrder(['First message', 'Second message', 'Third message']);

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('tail -n 100 messages.log')
        ->assertSeeInOrder(['Third message', 'Second message', 'First message']);
});

// homepage_microblog_section.feature — Scenario: Each message shows its full body text and posted date
it('shows the full message body and its posted timestamp', function () {
    postMessage('Just shipped a new feature!', '2026-05-01 14:32');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Just shipped a new feature!')
        ->assertSee('01/05/2026 14:32');
});

// homepage_microblog_section.feature — Scenario: The messages section is separate from the blog posts list
it('keeps the messages section separate from the blog posts list', function () {
    writePostFile('big-announcement', 'Big announcement', '2026-01-01');
    postMessage('Small update');

    $body = $this->get('/')->assertSuccessful()->getContent();

    expect($body)->toContain('Big announcement');

    $section = homepageSectionHtml($body, 'microblog');
    expect($section)->toContain('Small update');

    $outsideSection = str_replace($section, '', $body);
    expect($outsideSection)->not->toContain('Small update');
});

// homepage_microblog_section.feature — Scenario: No messages have been posted yet
it('shows an empty state inside the section when no messages exist', function () {
    $body = $this->get('/')->assertSuccessful()->getContent();

    $section = homepageSectionHtml($body, 'microblog');
    expect($section)->toContain('Nog geen berichten.');
});

// homepage_microblog_section.feature — Scenario: The messages section appears even when no blog posts exist
it('shows the messages section even when there are no blog posts', function () {
    postMessage('Still here');

    $body = $this->get('/')->assertSuccessful()->getContent();

    $section = homepageSectionHtml($body, 'microblog');
    expect($section)->toContain('Still here');
});
