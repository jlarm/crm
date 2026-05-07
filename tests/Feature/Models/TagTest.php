<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Tag;
use App\Models\User;

describe('Tag model', function (): void {
    it('belongs to many contacts', function (): void {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['user_id' => $user->id]);
        $contact = Contact::create([
            'dealership_id' => $dealership->id,
            'name' => 'Tagged Contact',
            'email' => 'tagged@example.com',
            'phone' => '555',
            'position' => 'Manager',
            'primary_contact' => false,
        ]);

        $tag = Tag::create(['name' => 'VIP']);
        $tag->contacts()->attach($contact->id);

        expect($tag->contacts)->toHaveCount(1)
            ->and($tag->contacts->first())->toBeInstanceOf(Contact::class)
            ->and($tag->contacts->first()->id)->toBe($contact->id);
    });
});
