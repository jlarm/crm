<?php

declare(strict_types=1);

use App\Actions\Emails\SanitizeEmailHtml;

it('keeps the formatting the editor offers', function () {
    $html = '<p>Hi <strong>Pat</strong>, <em>quick</em> <u>note</u></p><ul><li><p>One</p></li></ul><ol><li><p>Two</p></li></ol><p><a href="https://armp.test">plans</a></p>';

    expect((new SanitizeEmailHtml)($html))->toBe(
        '<p>Hi <strong>Pat</strong>, <em>quick</em> <u>note</u></p><ul><li>One</li></ul><ol><li>Two</li></ol>'
        .'<p><a href="https://armp.test" rel="noopener noreferrer" target="_blank">plans</a></p>'
    );
});

it('strips scripts, styles, images, event handlers and unsafe links', function () {
    $clean = (new SanitizeEmailHtml)(
        '<p onclick="steal()" style="color:red">Hi</p><script>alert(1)</script><img src="https://x.test/t.gif">'
        .'<a href="javascript:alert(1)">click</a><iframe src="https://x.test"></iframe>'
    );

    expect($clean)
        ->toContain('<p>Hi</p>')
        ->not->toContain('script')
        ->not->toContain('onclick')
        ->not->toContain('style=')
        ->not->toContain('<img')
        ->not->toContain('javascript:')
        ->not->toContain('iframe');
});

it('returns an empty string for an empty editor', function () {
    expect((new SanitizeEmailHtml)('<p></p>'))->toBe('')
        ->and((new SanitizeEmailHtml)('<p> <br> </p>'))->toBe('');
});
