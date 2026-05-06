<?php

declare(strict_types=1);

use App\Models\User;

it('redirects /admin to /dashboard', function () {
    $this->get('/admin')->assertRedirect('/dashboard');
});

it('redirects /admin/login to /dashboard', function () {
    $this->get('/admin/login')->assertRedirect('/dashboard');
});

it('redirects nested /admin paths to /dashboard', function () {
    $this->get('/admin/dealerships')->assertRedirect('/dashboard');
    $this->get('/admin/users/1/edit')->assertRedirect('/dashboard');
});

it('does not redirect non-admin paths', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/dashboard')->assertOk();
});

it('redirects /development paths to /dashboard', function () {
    $this->get('/development')->assertRedirect('/dashboard');
    $this->get('/development/login')->assertRedirect('/dashboard');
    $this->get('/development/dealerships')->assertRedirect('/dashboard');
});
