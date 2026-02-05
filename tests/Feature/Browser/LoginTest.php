<?php

declare(strict_types=1);

use App\Models\User;

it('logs in a user', function () {
    $user = User::factory()->create(
        [
            'email' => 'johndoe@gmail.com',
            'password' => bcrypt('password123$'),
        ]);

    visit('/login')
        ->fill('email', 'johndoe@gmail.com')
        ->fill('password', 'password123$')
        ->click('@login-button')
        ->assertPathIs('/');

    $this->assertAuthenticated();
});

it('logs out a user', function () {
    $this->actingAs(User::factory()->create());

    visit('/')
        ->click('@logout-button')
        ->assertPathIs('/');

    $this->assertGuest();
});
