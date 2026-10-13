<?php

declare(strict_types=1);

use App\Actions\Emails\FindEmailSlop;

it('passes a plain, specific email', function () {
    expect((new FindEmailSlop)("Hi Pat,\n\nOption B covers both rooftops. Does Tuesday at 10 work for a call?\n\nThanks,\nRita"))->toBe([]);
});

it('flags slop', function (string $text, string $expected) {
    expect(implode(' ', (new FindEmailSlop)($text)))->toContain($expected);
})->with([
    'banned word' => ['We can streamline your renewals.', '"streamline"'],
    'stock opener' => ['I hope this email finds you well.', 'i hope this email finds you'],
    'checking in' => ['Just checking in on the proposal.', 'just checking in'],
    'contrast' => ["It's not just compliance, but peace of mind.", 'contrast'],
    'em dash' => ['Option B — the cheaper one.', 'dashes'],
    'placeholder' => ['Talk on [day] at [time].', 'placeholders'],
    'bullets' => ["Options:\n- A\n- B", 'no bullet lists'],
]);
