<?php

use App\Markdown\FrontMatterDocument;

covers(FrontMatterDocument::class);

it('exposes the front matter and the body it was built with', function () {
    $document = new FrontMatterDocument(['title' => 'Hello'], "# Body\n");

    expect($document->frontMatter)->toBe(['title' => 'Hello']);
    expect($document->body)->toBe("# Body\n");
});
