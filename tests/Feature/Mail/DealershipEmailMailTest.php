<?php

declare(strict_types=1);

use App\Mail\DealershipEmailMail;
use App\Models\DealershipEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('renders the body escaped, in paragraphs, with links and the rep signature', function () {
    $rep = User::factory()->create(['name' => 'Rita Rep', 'title' => 'Account Manager', 'email' => 'rita@armp.test', 'phone' => '(555) 123-4567']);
    $email = DealershipEmail::factory()->create([
        'user_id' => $rep->id,
        'from_email' => 'rita@armp.test',
        'from_name' => 'Rita Rep',
        'body_text' => "Hi <b>Pat</b>,\n\nSee https://armp.test/plans.",
    ]);

    $html = (new DealershipEmailMail($email))->render();

    expect($html)
        ->toContain('Hi &lt;b&gt;Pat&lt;/b&gt;,')
        ->not->toContain('<b>Pat</b>')
        ->toContain('<a href="https://armp.test/plans"')
        ->toContain('</a>.')
        ->toContain('Rita Rep')
        ->toContain('<div>Account Manager</div>')
        ->toContain('555-123-4567')
        ->toContain('Automotive Risk Management Partners')
        ->toContain('alt="ARMP - Automotive Risk Management Partners"');
});

it('embeds the logo in the sent email', function () {
    $email = DealershipEmail::factory()->create();

    Mail::mailer('array')->send(new DealershipEmailMail($email));

    $sent = Mail::mailer('array')->getSymfonyTransport()->messages()->last()->getOriginalMessage();
    $logo = collect($sent->getAttachments())->first(fn ($part) => $part->getFilename() === 'email-logo.png');

    expect($logo)->not->toBeNull()
        ->and($logo->getDisposition())->toBe('inline')
        ->and($sent->getHtmlBody())->toContain('src="cid:'.$logo->getContentId().'"');
});

it('renders the editor HTML with inline styles for email clients', function () {
    $email = DealershipEmail::factory()->create([
        'body_html' => '<p>Hi <strong>Pat</strong></p><ul><li>One</li></ul><p><a href="https://armp.test" rel="noopener noreferrer" target="_blank">plans</a></p>',
        'body_text' => "Hi Pat\n\n- One\n\nplans (https://armp.test)",
    ]);

    $mail = new DealershipEmailMail($email);

    expect($mail->render())
        ->toContain('<p style="margin:0 0 14px 0;">Hi <strong>Pat</strong></p>')
        ->toContain('<ul style="margin:0 0 14px 0;padding-left:24px;"><li style="margin:0 0 4px 0;">One</li></ul>')
        ->toContain('<a style="color:#1a73e8;" href="https://armp.test"');

    $mail->assertSeeInText('plans (https://armp.test)');
});

it('threads a reply with In-Reply-To and References headers', function () {
    $first = DealershipEmail::factory()->create(['message_id' => 'first@armp.test']);
    $theirs = DealershipEmail::factory()->inbound()->create([
        'dealership_id' => $first->dealership_id,
        'parent_id' => $first->id,
        'message_id' => 'theirs@dealer.test',
    ]);
    $reply = DealershipEmail::factory()->create([
        'dealership_id' => $first->dealership_id,
        'parent_id' => $first->id,
        'in_reply_to' => $theirs->message_id,
    ]);

    $headers = (new DealershipEmailMail($reply))->headers();

    expect($headers->text)->toBe(['In-Reply-To' => '<theirs@dealer.test>'])
        ->and($headers->references)->toBe(['first@armp.test', 'theirs@dealer.test']);
});
