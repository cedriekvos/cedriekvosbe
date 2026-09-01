<?php

usesFakePostsRepository();
usesFakeMicroblogRepository();

// message_links.feature — Scenario: A long link stays inside the section on a narrow screen
it('wraps a long link so it stays inside the section on a narrow screen', function () {
    postMessage('Bronnen: https://x.com/taylorotwell/status/2077863029874503921', '2026-05-01 14:32');

    $page = visit('/')->on()->mobile();

    $page->assertScript(
        "(() => { const section = document.querySelector('[data-section=\"microblog\"]'); const link = section.querySelector('a'); return link.scrollWidth <= section.clientWidth; })()",
        true,
    )->assertScript(
        'document.documentElement.scrollWidth <= document.documentElement.clientWidth',
        true,
    );
});
