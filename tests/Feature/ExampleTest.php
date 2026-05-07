<?php

declare(strict_types=1);

it('redirects unauthenticated visitors to login', function (): void {
    $this->get('/')->assertRedirect(route('login'));
});

it('redirects authenticated visitors to the dashboard', function (): void {
    $this->actingAs(App\Models\User::factory()->create())
        ->get('/')
        ->assertRedirect(route('dashboard'));
});
