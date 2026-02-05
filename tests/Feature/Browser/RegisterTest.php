<?php

declare(strict_types=1);

it('registers a user', function () {
    $password = fake()->password(minLength: 16);

    visit('/register')
        ->fill('name', 'John Doe')
        ->fill('email', 'johndoe@gmail.com')
        ->fill('password', $password)
        ->click('@register-button')
        ->assertPathIs('/');

    $this->assertAuthenticated();

    expect(Auth::user())->toMatchArray([
        'name' => 'John Doe',
        'email' => 'johndoe@gmail.com',
    ]);
});
