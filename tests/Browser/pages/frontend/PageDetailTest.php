<?php

// The mobile line-wrap check needs a real rendered viewport width, which an
// HTTP feature test cannot observe — see PostDetailTest's browser test for
// the same reasoning on posts.

usesFakePagesRepository();

// page_detail.feature — Scenario: Page prose containing a long link stays inside the page on a narrow screen
it('wraps page prose containing a long link instead of overflowing a narrow screen', function () {
    writePageFile(
        'about',
        'About',
        'Bronnen: https://x.com/taylorotwell/status/2077863029874503921',
    );

    $page = visit('/about')->on()->mobile();

    $page->assertScript(
        "(() => { const p = document.querySelector('.prose p'); return p.scrollWidth <= p.clientWidth; })()",
        true,
    )->assertScript(
        'document.documentElement.scrollWidth <= document.documentElement.clientWidth',
        true,
    );
});
