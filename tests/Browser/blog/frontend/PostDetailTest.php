<?php

usesFakePostsRepository();

// post_detail.feature — Scenario: Article prose containing a long link stays inside the page on a narrow screen
it('wraps article prose containing a long link instead of overflowing a narrow screen', function () {
    writePostFile(
        'welcome',
        'Welcome',
        '2026-05-01',
        'Intro',
        'Bronnen: https://x.com/taylorotwell/status/2077863029874503921',
    );

    $page = visit('/blog/welcome')->on()->mobile();

    $page->assertScript(
        "(() => { const p = document.querySelector('.prose p'); return p.scrollWidth <= p.clientWidth; })()",
        true,
    )->assertScript(
        'document.documentElement.scrollWidth <= document.documentElement.clientWidth',
        true,
    );
});
