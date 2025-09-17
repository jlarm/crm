<?php

declare(strict_types=1);

use App\Enum\ReminderFrequency;
use App\Models\DealerEmail;
use App\Models\DealerEmailTemplate;
use App\Models\Dealership;
use App\Models\EmailTrackingEvent;
use App\Models\SentEmail;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('DealerEmailTemplate Model', function () {
    it('can create a dealer email template with valid data', function () {
        $templateData = [
            'name' => 'Introduction Email',
            'subject' => 'Welcome to our automotive solutions',
            'body' => '<p>Hello {{contact_name}},</p><p>We are excited to introduce our latest automotive CRM solutions.</p>',
            'attachment_path' => '/attachments/brochure.pdf',
            'attachment_name' => 'Product Brochure.pdf',
        ];

        $template = DealerEmailTemplate::create($templateData);

        expect($template)->toBeInstanceOf(DealerEmailTemplate::class)
            ->and($template->name)->toBe('Introduction Email')
            ->and($template->subject)->toBe('Welcome to our automotive solutions')
            ->and($template->body)->toContain('{{contact_name}}')
            ->and($template->attachment_path)->toBe('/attachments/brochure.pdf')
            ->and($template->attachment_name)->toBe('Product Brochure.pdf');
    });

    it('can create a template using factory', function () {
        $template = DealerEmailTemplate::factory()->create([
            'name' => 'Follow-up Email Template',
        ]);

        expect($template->name)->toBe('Follow-up Email Template')
            ->and($template->subject)->not->toBeEmpty()
            ->and($template->body)->not->toBeEmpty();
    });

    it('has many dealer emails relationship', function () {
        $template = DealerEmailTemplate::factory()->create();

        DealerEmail::factory()->count(3)->create([
            'dealer_email_template_id' => $template->id,
        ]);

        expect($template->dealerEmails)->toHaveCount(3)
            ->and($template->dealerEmails->first())->toBeInstanceOf(DealerEmail::class);
    });

    it('has morph to many pdf attachments relationship', function () {
        $template = DealerEmailTemplate::factory()->create();

        // Test the relationship exists (even if empty)
        expect($template->pdfAttachments())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\MorphToMany::class);
    });
});

describe('DealerEmail Model', function () {
    it('can create a dealer email with valid data', function () {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);
        $template = DealerEmailTemplate::factory()->create();

        $this->actingAs($user);

        $emailData = [
            'dealership_id' => $dealership->id,
            'dealer_email_template_id' => $template->id,
            'customize_email' => true,
            'customize_attachment' => false,
            'recipients' => ['contact@dealership.com', 'manager@dealership.com'],
            'subject' => 'Custom Email Subject',
            'message' => 'Custom email message content',
            'start_date' => '2024-01-15',
            'next_send_date' => '2024-02-15',
            'frequency' => ReminderFrequency::Monthly,
            'paused' => false,
        ];

        $dealerEmail = DealerEmail::create($emailData);

        expect($dealerEmail)->toBeInstanceOf(DealerEmail::class)
            ->and($dealerEmail->user_id)->toBe($user->id)
            ->and($dealerEmail->dealership_id)->toBe($dealership->id)
            ->and($dealerEmail->dealer_email_template_id)->toBe($template->id)
            ->and($dealerEmail->customize_email)->toBeTrue()
            ->and($dealerEmail->customize_attachment)->toBeFalse()
            ->and($dealerEmail->recipients)->toBe(['contact@dealership.com', 'manager@dealership.com'])
            ->and($dealerEmail->frequency)->toBe(ReminderFrequency::Monthly)
            ->and($dealerEmail->paused)->toBeFalse();
    });

    it('can create dealer email using factory', function () {
        $dealerEmail = DealerEmail::factory()->create();

        expect($dealerEmail->user_id)->not->toBeNull()
            ->and($dealerEmail->dealership_id)->not->toBeNull()
            ->and($dealerEmail->dealer_email_template_id)->not->toBeNull()
            ->and($dealerEmail->recipients)->toBeArray()
            ->and($dealerEmail->frequency)->toBeInstanceOf(ReminderFrequency::class);
    });

    it('can create dealer emails with factory states', function () {
        $pausedEmail = DealerEmail::factory()->paused()->create();
        $activeEmail = DealerEmail::factory()->active()->create();
        $immediateEmail = DealerEmail::factory()->immediate()->create();
        $customizedEmail = DealerEmail::factory()->customized()->create();

        expect($pausedEmail->paused)->toBeTrue()
            ->and($activeEmail->paused)->toBeFalse()
            ->and($immediateEmail->frequency)->toBe(ReminderFrequency::Immediate)
            ->and($customizedEmail->customize_email)->toBeTrue()
            ->and($customizedEmail->customize_attachment)->toBeTrue();
    });

    it('casts attributes correctly', function () {
        $dealerEmail = DealerEmail::factory()->create([
            'start_date' => '2024-01-15',
            'last_sent' => '2024-01-20',
            'next_send_date' => '2024-02-15',
            'recipients' => ['test1@example.com', 'test2@example.com'],
            'customize_email' => 1,
            'paused' => 0,
        ]);

        expect($dealerEmail->start_date)->toBeInstanceOf(Carbon\Carbon::class)
            ->and($dealerEmail->start_date->format('Y-m-d'))->toBe('2024-01-15')
            ->and($dealerEmail->last_sent)->toBeInstanceOf(Carbon\Carbon::class)
            ->and($dealerEmail->next_send_date)->toBeInstanceOf(Carbon\Carbon::class)
            ->and($dealerEmail->recipients)->toBeArray()
            ->and($dealerEmail->recipients)->toHaveCount(2)
            ->and($dealerEmail->customize_email)->toBeTrue()
            ->and($dealerEmail->paused)->toBeFalse()
            ->and($dealerEmail->frequency)->toBeInstanceOf(ReminderFrequency::class);
    });

    it('belongs to user, dealership, and template', function () {
        $user = User::factory()->create(['name' => 'Sales Rep']);
        $dealership = Dealership::factory()->create(['name' => 'Prime Motors']);
        $template = DealerEmailTemplate::factory()->create(['name' => 'Welcome Template']);

        $this->actingAs($user);

        $dealerEmail = DealerEmail::withoutEvents(function () use ($user, $dealership, $template) {
            return DealerEmail::factory()->create([
                'user_id' => $user->id,
                'dealership_id' => $dealership->id,
                'dealer_email_template_id' => $template->id,
            ]);
        });

        expect($dealerEmail->user)->toBeInstanceOf(User::class)
            ->and($dealerEmail->user->name)->toBe('Sales Rep')
            ->and($dealerEmail->dealership)->toBeInstanceOf(Dealership::class)
            ->and($dealerEmail->dealership->name)->toBe('Prime Motors')
            ->and($dealerEmail->template)->toBeInstanceOf(DealerEmailTemplate::class)
            ->and($dealerEmail->template->name)->toBe('Welcome Template');
    });

    it('has morph to many pdf attachments relationship', function () {
        $dealerEmail = DealerEmail::factory()->create();

        expect($dealerEmail->pdfAttachments())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\MorphToMany::class);
    });

    it('automatically sets user_id when creating', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dealerEmail = DealerEmail::factory()->create([
            'user_id' => null, // This should be overridden by the boot method
        ]);

        expect($dealerEmail->user_id)->toBe($user->id);
    });
});

describe('SentEmail Model', function () {
    it('can create a sent email with valid data', function () {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);

        $sentEmailData = [
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'recipient' => 'contact@dealership.com',
            'message_id' => 'unique-message-id@mailgun.example.com',
            'subject' => 'Welcome to our CRM solution',
            'tracking_data' => [
                'mailgun_id' => 'mg-12345',
                'sent_at' => now()->toISOString(),
                'tags' => ['automated', 'dealer-email'],
            ],
        ];

        $sentEmail = SentEmail::create($sentEmailData);

        expect($sentEmail)->toBeInstanceOf(SentEmail::class)
            ->and($sentEmail->user_id)->toBe($user->id)
            ->and($sentEmail->dealership_id)->toBe($dealership->id)
            ->and($sentEmail->recipient)->toBe('contact@dealership.com')
            ->and($sentEmail->message_id)->toBe('unique-message-id@mailgun.example.com')
            ->and($sentEmail->subject)->toBe('Welcome to our CRM solution')
            ->and($sentEmail->tracking_data)->toBeArray()
            ->and($sentEmail->tracking_data['mailgun_id'])->toBe('mg-12345');
    });

    it('can create sent email using factory', function () {
        $sentEmail = SentEmail::factory()->create();

        expect($sentEmail->user_id)->not->toBeNull()
            ->and($sentEmail->dealership_id)->not->toBeNull()
            ->and($sentEmail->recipient)->toBeString()
            ->and($sentEmail->message_id)->toContain('@')
            ->and($sentEmail->tracking_data)->toBeArray();
    });

    it('casts tracking_data to array', function () {
        $sentEmail = SentEmail::factory()->create([
            'tracking_data' => ['test' => 'value', 'number' => 123],
        ]);

        expect($sentEmail->tracking_data)->toBeArray()
            ->and($sentEmail->tracking_data['test'])->toBe('value')
            ->and($sentEmail->tracking_data['number'])->toBe(123);
    });

    it('belongs to user and dealership', function () {
        $user = User::factory()->create(['name' => 'Email Sender']);
        $dealership = Dealership::factory()->create(['name' => 'Target Dealership']);

        $sentEmail = SentEmail::factory()->create([
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
        ]);

        expect($sentEmail->user)->toBeInstanceOf(User::class)
            ->and($sentEmail->user->name)->toBe('Email Sender')
            ->and($sentEmail->dealership)->toBeInstanceOf(Dealership::class)
            ->and($sentEmail->dealership->name)->toBe('Target Dealership');
    });

    it('has many tracking events', function () {
        $sentEmail = SentEmail::factory()->create();

        EmailTrackingEvent::factory()->count(3)->create([
            'sent_email_id' => $sentEmail->id,
        ]);

        expect($sentEmail->trackingEvents)->toHaveCount(3)
            ->and($sentEmail->trackingEvents->first())->toBeInstanceOf(EmailTrackingEvent::class);
    });

    it('can check if email was opened', function () {
        $sentEmail = SentEmail::factory()->create();

        // Initially not opened
        expect($sentEmail->wasOpened())->toBeFalse();

        // Add an opened event
        EmailTrackingEvent::factory()->opened()->create([
            'sent_email_id' => $sentEmail->id,
        ]);

        expect($sentEmail->wasOpened())->toBeTrue();
    });

    it('can check if email was clicked', function () {
        $sentEmail = SentEmail::factory()->create();

        expect($sentEmail->wasClicked())->toBeFalse();

        EmailTrackingEvent::factory()->clicked()->create([
            'sent_email_id' => $sentEmail->id,
        ]);

        expect($sentEmail->wasClicked())->toBeTrue();
    });

    it('can check if email was bounced', function () {
        $sentEmail = SentEmail::factory()->create();

        expect($sentEmail->wasBounced())->toBeFalse();

        EmailTrackingEvent::factory()->bounced()->create([
            'sent_email_id' => $sentEmail->id,
        ]);

        expect($sentEmail->wasBounced())->toBeTrue();
    });

    it('can count opens and clicks', function () {
        $sentEmail = SentEmail::factory()->create();

        // Add multiple tracking events
        EmailTrackingEvent::factory()->opened()->count(3)->create([
            'sent_email_id' => $sentEmail->id,
        ]);

        EmailTrackingEvent::factory()->clicked()->count(2)->create([
            'sent_email_id' => $sentEmail->id,
        ]);

        expect($sentEmail->openCount())->toBe(3)
            ->and($sentEmail->clickCount())->toBe(2);
    });
});

describe('EmailTrackingEvent Model', function () {
    it('can create an email tracking event with valid data', function () {
        $sentEmail = SentEmail::factory()->create();

        $eventData = [
            'sent_email_id' => $sentEmail->id,
            'event_type' => EmailTrackingEvent::EVENT_OPENED,
            'message_id' => 'msg-12345@mailgun.example.com',
            'recipient_email' => 'contact@dealership.com',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'ip_address' => '192.168.1.1',
            'mailgun_data' => [
                'id' => 'event-12345',
                'timestamp' => now()->timestamp,
                'event' => 'opened',
            ],
            'event_timestamp' => now(),
        ];

        $event = EmailTrackingEvent::create($eventData);

        expect($event)->toBeInstanceOf(EmailTrackingEvent::class)
            ->and($event->sent_email_id)->toBe($sentEmail->id)
            ->and($event->event_type)->toBe(EmailTrackingEvent::EVENT_OPENED)
            ->and($event->message_id)->toBe('msg-12345@mailgun.example.com')
            ->and($event->recipient_email)->toBe('contact@dealership.com')
            ->and($event->mailgun_data)->toBeArray()
            ->and($event->event_timestamp)->toBeInstanceOf(Carbon\Carbon::class);
    });

    it('can create tracking events using factory', function () {
        $event = EmailTrackingEvent::factory()->create();

        expect($event->sent_email_id)->not->toBeNull()
            ->and($event->event_type)->toBeString()
            ->and($event->mailgun_data)->toBeArray()
            ->and($event->event_timestamp)->toBeInstanceOf(Carbon\Carbon::class);
    });

    it('can create events with factory states', function () {
        $sentEmail = SentEmail::factory()->create();

        $openedEvent = EmailTrackingEvent::factory()->opened()->create(['sent_email_id' => $sentEmail->id]);
        $clickedEvent = EmailTrackingEvent::factory()->clicked()->create(['sent_email_id' => $sentEmail->id]);
        $bouncedEvent = EmailTrackingEvent::factory()->bounced()->create(['sent_email_id' => $sentEmail->id]);
        $deliveredEvent = EmailTrackingEvent::factory()->delivered()->create(['sent_email_id' => $sentEmail->id]);

        expect($openedEvent->event_type)->toBe(EmailTrackingEvent::EVENT_OPENED)
            ->and($openedEvent->url)->toBeNull()
            ->and($clickedEvent->event_type)->toBe(EmailTrackingEvent::EVENT_CLICKED)
            ->and($clickedEvent->url)->not->toBeNull()
            ->and($bouncedEvent->event_type)->toBe(EmailTrackingEvent::EVENT_BOUNCED)
            ->and($deliveredEvent->event_type)->toBe(EmailTrackingEvent::EVENT_DELIVERED);
    });

    it('belongs to sent email', function () {
        $sentEmail = SentEmail::factory()->create();
        $event = EmailTrackingEvent::factory()->create(['sent_email_id' => $sentEmail->id]);

        expect($event->sentEmail)->toBeInstanceOf(SentEmail::class)
            ->and($event->sentEmail->id)->toBe($sentEmail->id);
    });

    it('has scopes for different event types', function () {
        $sentEmail = SentEmail::factory()->create();

        EmailTrackingEvent::factory()->opened()->count(2)->create(['sent_email_id' => $sentEmail->id]);
        EmailTrackingEvent::factory()->clicked()->count(1)->create(['sent_email_id' => $sentEmail->id]);
        EmailTrackingEvent::factory()->bounced()->count(1)->create(['sent_email_id' => $sentEmail->id]);
        EmailTrackingEvent::factory()->delivered()->count(1)->create(['sent_email_id' => $sentEmail->id]);

        $openedEvents = EmailTrackingEvent::opened()->get();
        $clickedEvents = EmailTrackingEvent::clicked()->get();
        $bouncedEvents = EmailTrackingEvent::bounced()->get();
        $deliveredEvents = EmailTrackingEvent::delivered()->get();

        expect($openedEvents)->toHaveCount(2)
            ->and($clickedEvents)->toHaveCount(1)
            ->and($bouncedEvents)->toHaveCount(1)
            ->and($deliveredEvents)->toHaveCount(1);
    });

    it('defines event type constants', function () {
        expect(EmailTrackingEvent::EVENT_DELIVERED)->toBe('delivered')
            ->and(EmailTrackingEvent::EVENT_OPENED)->toBe('opened')
            ->and(EmailTrackingEvent::EVENT_CLICKED)->toBe('clicked')
            ->and(EmailTrackingEvent::EVENT_BOUNCED)->toBe('bounced')
            ->and(EmailTrackingEvent::EVENT_COMPLAINED)->toBe('complained')
            ->and(EmailTrackingEvent::EVENT_UNSUBSCRIBED)->toBe('unsubscribed');
    });
});

describe('Email Models Integration', function () {
    it('can track complete email lifecycle', function () {
        // Create a dealer email
        $dealerEmail = DealerEmail::factory()->create([
            'subject' => 'Welcome to our CRM',
            'recipients' => ['manager@dealership.com'],
        ]);

        // Simulate sending the email
        $sentEmail = SentEmail::factory()->create([
            'user_id' => $dealerEmail->user_id,
            'dealership_id' => $dealerEmail->dealership_id,
            'recipient' => $dealerEmail->recipients[0],
            'subject' => $dealerEmail->subject,
        ]);

        // Track email events
        EmailTrackingEvent::factory()->delivered()->create(['sent_email_id' => $sentEmail->id]);
        EmailTrackingEvent::factory()->opened()->create(['sent_email_id' => $sentEmail->id]);
        EmailTrackingEvent::factory()->clicked()->create(['sent_email_id' => $sentEmail->id]);

        expect($sentEmail->wasOpened())->toBeTrue()
            ->and($sentEmail->wasClicked())->toBeTrue()
            ->and($sentEmail->wasBounced())->toBeFalse()
            ->and($sentEmail->openCount())->toBe(1)
            ->and($sentEmail->clickCount())->toBe(1);
    });

    it('can manage email templates and customizations', function () {
        $template = DealerEmailTemplate::factory()->create([
            'name' => 'Monthly Newsletter',
            'subject' => 'Your Monthly Update',
            'body' => '<p>Hello {{contact_name}}, here is your monthly update!</p>',
        ]);

        $customizedEmail = DealerEmail::factory()->customized()->create([
            'dealer_email_template_id' => $template->id,
            'subject' => 'Custom Subject Override',
            'message' => 'Custom message content',
        ]);

        $standardEmail = DealerEmail::factory()->create([
            'dealer_email_template_id' => $template->id,
            'customize_email' => false,
        ]);

        expect($customizedEmail->customize_email)->toBeTrue()
            ->and($customizedEmail->subject)->toBe('Custom Subject Override')
            ->and($standardEmail->customize_email)->toBeFalse()
            ->and($template->dealerEmails)->toHaveCount(2);
    });

    it('can handle different reminder frequencies', function () {
        $frequencies = [
            ReminderFrequency::Immediate,
            ReminderFrequency::Daily,
            ReminderFrequency::Weekly,
            ReminderFrequency::Monthly,
            ReminderFrequency::Quarterly,
        ];

        foreach ($frequencies as $frequency) {
            $dealerEmail = DealerEmail::factory()->create(['frequency' => $frequency]);
            expect($dealerEmail->frequency)->toBe($frequency)
                ->and($dealerEmail->frequency->getLabel())->not->toBeEmpty();
        }
    });
});

describe('Email Models Activity Logging', function () {
    it('logs activity for dealer email template operations', function () {
        DealerEmailTemplate::withoutEvents(function () {
            $template = DealerEmailTemplate::factory()->create();

            activity()
                ->performedOn($template)
                ->log('Dealer Email Template created');

            expect($template->activities)->toHaveCount(1);

            $activity = $template->activities->first();
            expect($activity->description)->toBe('Dealer Email Template created');
        });
    });

    it('logs activity for dealer email operations', function () {
        DealerEmail::withoutEvents(function () {
            $dealerEmail = DealerEmail::factory()->create();

            activity()
                ->performedOn($dealerEmail)
                ->log('Dealer Email created');

            expect($dealerEmail->activities)->toHaveCount(1);

            $activity = $dealerEmail->activities->first();
            expect($activity->description)->toBe('Dealer Email created');
        });
    });

    it('logs activity for sent email operations', function () {
        SentEmail::withoutEvents(function () {
            $sentEmail = SentEmail::factory()->create();

            activity()
                ->performedOn($sentEmail)
                ->log('Email created');

            expect($sentEmail->activities)->toHaveCount(1);

            $activity = $sentEmail->activities->first();
            expect($activity->description)->toBe('Email created');
        });
    });
});
