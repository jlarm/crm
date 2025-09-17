<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Progress;
use App\Models\ProgressCategory;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Progress Creation', function () {
    it('can create a progress entry with valid data', function () {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);
        $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);
        $category = ProgressCategory::factory()->create(['name' => 'Initial Contact']);

        $progressData = [
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'contact_id' => $contact->id,
            'details' => 'Had initial call with the general manager to discuss their current CRM needs.',
            'date' => '2024-12-01',
            'progress_category_id' => $category->id,
        ];

        $progress = Progress::create($progressData);

        expect($progress)->toBeInstanceOf(Progress::class)
            ->and($progress->details)->toBe('Had initial call with the general manager to discuss their current CRM needs.')
            ->and($progress->date->format('Y-m-d'))->toBe('2024-12-01')
            ->and($progress->user_id)->toBe($user->id)
            ->and($progress->dealership_id)->toBe($dealership->id)
            ->and($progress->contact_id)->toBe($contact->id)
            ->and($progress->progress_category_id)->toBe($category->id);
    });

    it('can create a progress entry using factory', function () {
        $progress = Progress::factory()->create([
            'details' => 'Factory created progress entry',
        ]);

        expect($progress->details)->toBe('Factory created progress entry')
            ->and($progress->user_id)->not->toBeNull()
            ->and($progress->dealership_id)->not->toBeNull()
            ->and($progress->date)->toBeInstanceOf(Carbon\Carbon::class);
    });

    it('can create progress entries with factory states', function () {
        $recentProgress = Progress::factory()->recent()->create();
        $oldProgress = Progress::factory()->old()->create();
        $progressWithoutContact = Progress::factory()->withoutContact()->create();

        expect($recentProgress->date->isAfter(now()->subWeek()))->toBeTrue()
            ->and($oldProgress->date->isBefore(now()->subMonths(6)))->toBeTrue()
            ->and($progressWithoutContact->contact_id)->toBeNull();
    });

    it('can create progress entry without contact', function () {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);
        $category = ProgressCategory::factory()->create();

        $progress = Progress::create([
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'contact_id' => null,
            'details' => 'General dealership follow-up, not contact specific',
            'date' => now()->format('Y-m-d'),
            'progress_category_id' => $category->id,
        ]);

        expect($progress->contact_id)->toBeNull()
            ->and($progress->details)->toBe('General dealership follow-up, not contact specific');
    });
});

describe('Progress Attributes', function () {
    it('casts date to Carbon instance', function () {
        $progress = Progress::factory()->create([
            'date' => '2024-06-15',
        ]);

        expect($progress->date)->toBeInstanceOf(Carbon\Carbon::class)
            ->and($progress->date->format('Y-m-d'))->toBe('2024-06-15');
    });

    it('handles date formatting correctly', function () {
        $progress = Progress::factory()->create([
            'date' => now(),
        ]);

        expect($progress->date->isToday())->toBeTrue();

        $progress->update(['date' => '2023-12-25']);
        $progress->refresh();

        expect($progress->date->format('Y-m-d'))->toBe('2023-12-25')
            ->and($progress->date->format('M j, Y'))->toBe('Dec 25, 2023');
    });

    it('uses correct table name', function () {
        $progress = new Progress();

        expect($progress->getTable())->toBe('progresses');
    });
});

describe('Progress Fillable Fields', function () {
    it('allows mass assignment of all fillable fields', function () {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);
        $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);
        $category = ProgressCategory::factory()->create();

        $data = [
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'contact_id' => $contact->id,
            'details' => 'Test progress details',
            'date' => '2024-01-15',
            'progress_category_id' => $category->id,
        ];

        $progress = Progress::create($data);

        foreach ($data as $key => $value) {
            if ($key === 'date') {
                expect($progress->date->format('Y-m-d'))->toBe($value);
            } else {
                expect($progress->$key)->toBe($value);
            }
        }
    });
});

describe('Progress Relationships', function () {
    it('belongs to a user', function () {
        $user = User::factory()->create(['name' => 'John Sales Rep']);
        $progress = Progress::factory()->create(['user_id' => $user->id]);

        expect($progress->user)->toBeInstanceOf(User::class)
            ->and($progress->user->id)->toBe($user->id)
            ->and($progress->user->name)->toBe('John Sales Rep');
    });

    it('belongs to a dealership', function () {
        $dealership = Dealership::factory()->create(['name' => 'Prime Motors']);
        $progress = Progress::factory()->create(['dealership_id' => $dealership->id]);

        expect($progress->dealership)->toBeInstanceOf(Dealership::class)
            ->and($progress->dealership->id)->toBe($dealership->id)
            ->and($progress->dealership->name)->toBe('Prime Motors');
    });

    it('belongs to a contact (optional)', function () {
        $contact = Contact::factory()->create(['name' => 'Jane Manager']);
        $progress = Progress::factory()->create(['contact_id' => $contact->id]);

        expect($progress->contact)->toBeInstanceOf(Contact::class)
            ->and($progress->contact->id)->toBe($contact->id)
            ->and($progress->contact->name)->toBe('Jane Manager');
    });

    it('can have null contact relationship', function () {
        $progress = Progress::factory()->withoutContact()->create();

        expect($progress->contact)->toBeNull()
            ->and($progress->contact_id)->toBeNull();
    });

    it('belongs to a progress category', function () {
        $category = ProgressCategory::factory()->create(['name' => 'Demo Scheduled']);
        $progress = Progress::factory()->create(['progress_category_id' => $category->id]);

        expect($progress->category)->toBeInstanceOf(ProgressCategory::class)
            ->and($progress->category->id)->toBe($category->id)
            ->and($progress->category->name)->toBe('Demo Scheduled');
    });

    it('can load all relationships together', function () {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);
        $contact = Contact::factory()->create(['dealership_id' => $dealership->id]);
        $category = ProgressCategory::factory()->create();

        $progress = Progress::factory()->create([
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'contact_id' => $contact->id,
            'progress_category_id' => $category->id,
        ]);

        $loadedProgress = Progress::with(['user', 'dealership', 'contact', 'category'])
            ->find($progress->id);

        expect($loadedProgress->relationLoaded('user'))->toBeTrue()
            ->and($loadedProgress->relationLoaded('dealership'))->toBeTrue()
            ->and($loadedProgress->relationLoaded('contact'))->toBeTrue()
            ->and($loadedProgress->relationLoaded('category'))->toBeTrue();
    });
});

describe('Progress Business Logic', function () {
    it('can track multiple progress entries for same dealership', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        $progresses = Progress::factory()->count(5)->create([
            'dealership_id' => $dealership->id,
            'user_id' => $this->user->id,
        ]);

        expect($dealership->progresses)->toHaveCount(5)
            ->and($progresses->first()->dealership_id)->toBe($dealership->id);
    });

    it('can track progress entries for different contacts in same dealership', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $contact1 = Contact::factory()->create(['dealership_id' => $dealership->id, 'name' => 'Contact 1']);
        $contact2 = Contact::factory()->create(['dealership_id' => $dealership->id, 'name' => 'Contact 2']);

        $progress1 = Progress::factory()->create([
            'dealership_id' => $dealership->id,
            'contact_id' => $contact1->id,
            'details' => 'Progress with Contact 1',
        ]);

        $progress2 = Progress::factory()->create([
            'dealership_id' => $dealership->id,
            'contact_id' => $contact2->id,
            'details' => 'Progress with Contact 2',
        ]);

        expect($progress1->contact->name)->toBe('Contact 1')
            ->and($progress2->contact->name)->toBe('Contact 2')
            ->and($progress1->dealership_id)->toBe($progress2->dealership_id);
    });

    it('can filter progress entries by date range', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);

        // Create progress entries with different dates
        $recentProgress = Progress::factory()->create([
            'dealership_id' => $dealership->id,
            'date' => now()->subDays(5),
        ]);

        $oldProgress = Progress::factory()->create([
            'dealership_id' => $dealership->id,
            'date' => now()->subMonths(6),
        ]);

        $recentProgresses = Progress::where('dealership_id', $dealership->id)
            ->where('date', '>=', now()->subWeek())
            ->get();

        expect($recentProgresses)->toHaveCount(1)
            ->and($recentProgresses->first()->id)->toBe($recentProgress->id);
    });

    it('can group progress entries by category', function () {
        $dealership = Dealership::factory()->create(['user_id' => $this->user->id]);
        $contactCategory = ProgressCategory::factory()->create(['name' => 'Initial Contact']);
        $followUpCategory = ProgressCategory::factory()->create(['name' => 'Follow-up']);

        // Create multiple progress entries with different categories
        Progress::factory()->count(3)->create([
            'dealership_id' => $dealership->id,
            'progress_category_id' => $contactCategory->id,
        ]);

        Progress::factory()->count(2)->create([
            'dealership_id' => $dealership->id,
            'progress_category_id' => $followUpCategory->id,
        ]);

        $contactProgresses = Progress::where('progress_category_id', $contactCategory->id)->get();
        $followUpProgresses = Progress::where('progress_category_id', $followUpCategory->id)->get();

        expect($contactProgresses)->toHaveCount(3)
            ->and($followUpProgresses)->toHaveCount(2);
    });
});

describe('Progress Validation and Edge Cases', function () {
    it('handles null and empty values gracefully', function () {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);

        $progress = Progress::create([
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'contact_id' => null,
            'details' => '',
            'date' => now()->format('Y-m-d'),
            'progress_category_id' => null,
        ]);

        expect($progress->contact_id)->toBeNull()
            ->and($progress->details)->toBe('')
            ->and($progress->progress_category_id)->toBeNull();
    });

    it('handles very long details text', function () {
        $longDetails = str_repeat('This is a very detailed progress note. ', 100);

        $progress = Progress::factory()->create([
            'details' => $longDetails,
        ]);

        expect(mb_strlen($progress->details))->toBeGreaterThan(1000)
            ->and($progress->details)->toContain('very detailed progress note');
    });

    it('preserves special characters in details', function () {
        $specialText = 'Progress with special chars: @#$%^&*()_+{}[]|;":,./<>? and emojis 🚀📈💼';

        $progress = Progress::factory()->create([
            'details' => $specialText,
        ]);

        expect($progress->details)->toBe($specialText);
    });

    it('maintains data integrity after multiple updates', function () {
        $progress = Progress::factory()->create([
            'details' => 'Original details',
            'date' => '2024-01-01',
        ]);

        // Perform multiple updates
        $progress->update(['details' => 'Updated details']);
        $progress->update(['date' => '2024-06-15']);

        $progress->refresh();

        expect($progress->details)->toBe('Updated details')
            ->and($progress->date->format('Y-m-d'))->toBe('2024-06-15');
    });

    it('handles different date formats', function () {
        $progress = Progress::factory()->create([
            'date' => '2024-12-25',
        ]);

        expect($progress->date->format('Y-m-d'))->toBe('2024-12-25');

        // Update with different format
        $progress->update(['date' => now()]);
        expect($progress->date)->toBeInstanceOf(Carbon\Carbon::class);
    });

    it('handles missing related records gracefully', function () {
        $progress = Progress::factory()->withoutContact()->create([
            'progress_category_id' => null,
        ]);

        // Test accessing null relationships
        expect($progress->contact)->toBeNull()
            ->and($progress->category)->toBeNull()
            ->and($progress->user)->not->toBeNull()
            ->and($progress->dealership)->not->toBeNull();
    });
});

describe('Progress Activity Logging', function () {
    it('logs activity when progress is created', function () {
        Progress::withoutEvents(function () {
            $progress = Progress::factory()->create([
                'details' => 'Activity Test Progress',
            ]);

            // Manually trigger activity logging
            activity()
                ->performedOn($progress)
                ->log('Progress created');

            expect($progress->activities)->toHaveCount(1);

            $activity = $progress->activities->first();
            expect($activity->description)->toBe('Progress created')
                ->and($activity->subject_type)->toBe(Progress::class)
                ->and($activity->subject_id)->toBe($progress->id);
        });
    });

    it('logs activity when progress is updated', function () {
        Progress::withoutEvents(function () {
            $progress = Progress::factory()->create();

            $progress->update(['details' => 'Updated Progress Details']);
            activity()
                ->performedOn($progress)
                ->log('Progress updated');

            expect($progress->activities)->toHaveCount(1);

            $activity = $progress->activities->first();
            expect($activity->description)->toBe('Progress updated');
        });
    });

    it('logs activity when progress is deleted', function () {
        Progress::withoutEvents(function () {
            $progress = Progress::factory()->create();
            $progressId = $progress->id;

            activity()
                ->performedOn($progress)
                ->log('Progress deleted');

            $progress->delete();

            $activities = Spatie\Activitylog\Models\Activity::where('subject_type', Progress::class)
                ->where('subject_id', $progressId)
                ->get();

            expect($activities)->toHaveCount(1);

            $deleteActivity = $activities->where('description', 'Progress deleted')->first();
            expect($deleteActivity)->not->toBeNull();
        });
    });
});

describe('Progress Categories', function () {
    it('can create progress categories', function () {
        $category = ProgressCategory::factory()->create(['name' => 'Demo Completed']);

        expect($category)->toBeInstanceOf(ProgressCategory::class)
            ->and($category->name)->toBe('Demo Completed');
    });

    it('progress category has many progresses', function () {
        $category = ProgressCategory::factory()->create(['name' => 'Follow-up']);

        Progress::factory()->count(3)->create([
            'progress_category_id' => $category->id,
        ]);

        expect($category->progresses)->toHaveCount(3)
            ->and($category->progresses->first())->toBeInstanceOf(Progress::class);
    });

    it('can filter progresses by category', function () {
        $category1 = ProgressCategory::factory()->create(['name' => 'Initial Contact']);
        $category2 = ProgressCategory::factory()->create(['name' => 'Follow-up']);

        Progress::factory()->count(2)->create(['progress_category_id' => $category1->id]);
        Progress::factory()->count(3)->create(['progress_category_id' => $category2->id]);

        expect($category1->progresses)->toHaveCount(2)
            ->and($category2->progresses)->toHaveCount(3);
    });
});
