<?php

usesFakePostsRepository();
usesFakeMicroblogRepository();

// homepage_microblog_section.feature — Scenario: A message containing a long link stays inside the section on a narrow screen
it('wraps a message containing a long link instead of overflowing a narrow screen', function () {
    postMessage('Bronnen: https://x.com/taylorotwell/status/2077863029874503921', '2026-05-01 14:32');

    $page = visit('/')->on()->mobile();

    $page->assertScript(
        "(() => { const body = document.querySelector('.message-entry p'); return body.scrollWidth <= body.clientWidth; })()",
        true,
    )->assertScript(
        'document.documentElement.scrollWidth <= document.documentElement.clientWidth',
        true,
    );
});
