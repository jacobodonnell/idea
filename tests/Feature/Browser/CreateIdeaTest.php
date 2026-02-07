<?php

declare(strict_types=1);

use App\Models\User;

it('creates a new idea', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit('/ideas')
        ->click('@create-idea-button')
        ->fill('title', 'My New Idea')
//        ->click('@label-status-completed')
        ->fill('description', 'A description of my idea.')
        ->fill('@new-link', 'https://laracasts.com')
        ->click('@add-new-link-button')
        ->fill('@new-link', 'https://laravel.com')
        ->click('@add-new-link-button')
        ->click('@create-idea-submit')
        ->assertPathIs('/ideas');
    //        ->assertSee('My New Idea');

    expect($user->ideas()->first())->toMatchArray([
        'title' => 'My New Idea',
        'description' => 'A description of my idea.',
        'status' => 'in_progress',
        'links' => ['https://laracasts.com', 'https://laravel.com'],
    ]);
})->skip();
