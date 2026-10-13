<?php

declare(strict_types=1);

use App\Actions\Emails\ConvertEmailHtmlToText;

it('turns the editor HTML into readable plain text', function () {
    $html = '<p>Hi Pat &amp; team,</p><p>Line one<br>Line two</p><ul><li>Apples</li><li>Pears</li></ul>'
        .'<ol><li>First</li><li>Second</li></ol><p>See <a href="https://armp.test/plans">our plans</a> or https://armp.test.</p>';

    expect((new ConvertEmailHtmlToText)($html))->toBe(
        "Hi Pat & team,\n\nLine one\nLine two\n\n- Apples\n- Pears\n\n1. First\n2. Second\n\nSee our plans (https://armp.test/plans) or https://armp.test."
    );
});

it('does not repeat a link whose text is its address', function () {
    expect((new ConvertEmailHtmlToText)('<p><a href="https://armp.test">https://armp.test</a></p>'))->toBe('https://armp.test');
});
