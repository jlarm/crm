<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('Settings AppearanceController', function () {
    it('renders the appearance settings page', function () {
        get(route('settings.appearance.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('settings/Appearance'));
    });

    it('requires authentication', function () {
        auth()->logout();
        get(route('settings.appearance.edit'))->assertRedirect(route('login'));
    });
});
