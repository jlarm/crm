<?php

declare(strict_types=1);

use App\Models\User;

describe('FortifyServiceProvider register view', function (): void {
    it('redirects to the login page when at least one user already exists', function (): void {
        User::factory()->create();

        $this->get(route('register'))->assertRedirect(route('login'));
    });

    it('renders the register Inertia page when no users exist', function (): void {
        $this->get(route('register'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/Register'));
    });
});
