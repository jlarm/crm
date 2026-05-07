<?php

declare(strict_types=1);

use App\Models\EmailTrackingEvent;
use App\Models\SentEmail;

describe('SentEmail tracking helpers with loaded relation', function (): void {
    it('returns true from wasOpened when relation is preloaded with an open event', function (): void {
        $sentEmail = SentEmail::factory()->create();
        EmailTrackingEvent::factory()->opened()->create(['sent_email_id' => $sentEmail->id]);

        $sentEmail->load('trackingEvents');

        expect($sentEmail->wasOpened())->toBeTrue();
    });

    it('returns true from wasClicked when relation is preloaded with a click event', function (): void {
        $sentEmail = SentEmail::factory()->create();
        EmailTrackingEvent::factory()->clicked()->create(['sent_email_id' => $sentEmail->id]);

        $sentEmail->load('trackingEvents');

        expect($sentEmail->wasClicked())->toBeTrue();
    });

    it('returns true from wasBounced when relation is preloaded with a bounce event', function (): void {
        $sentEmail = SentEmail::factory()->create();
        EmailTrackingEvent::factory()->bounced()->create(['sent_email_id' => $sentEmail->id]);

        $sentEmail->load('trackingEvents');

        expect($sentEmail->wasBounced())->toBeTrue();
    });

    it('counts opens from a preloaded relation', function (): void {
        $sentEmail = SentEmail::factory()->create();
        EmailTrackingEvent::factory()->opened()->count(2)->create(['sent_email_id' => $sentEmail->id]);
        EmailTrackingEvent::factory()->clicked()->create(['sent_email_id' => $sentEmail->id]);

        $sentEmail->load('trackingEvents');

        expect($sentEmail->openCount())->toBe(2);
    });

    it('counts clicks from a preloaded relation', function (): void {
        $sentEmail = SentEmail::factory()->create();
        EmailTrackingEvent::factory()->clicked()->count(3)->create(['sent_email_id' => $sentEmail->id]);
        EmailTrackingEvent::factory()->opened()->create(['sent_email_id' => $sentEmail->id]);

        $sentEmail->load('trackingEvents');

        expect($sentEmail->clickCount())->toBe(3);
    });
});
