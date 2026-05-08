<?php

declare(strict_types=1);

test('/admin redirects to /dashboard', function () {
    $this->get('/admin')->assertRedirect('/dashboard');
});

test('/admin/* redirects to /dashboard', function () {
    $this->get('/admin/users')->assertRedirect('/dashboard');
    $this->get('/admin/dealerships/edit/1')->assertRedirect('/dashboard');
});
