<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

it('creates a new idea', function () {
    actingAs($user = User::factory()->create());

    visit('/ideas')
        ->click('@create-idea-button')
        ->fill('title', 'My New Idea')
        ->click('In Progress')
        ->fill('description', 'A description of my idea.')
        ->fill('@new-link', 'https://laracasts.com')
        ->click('@add-new-link-button')
        ->fill('@new-link', 'https://laravel.com')
        ->click('@add-new-link-button')
        ->click('Create')
        ->assertPathIs('/ideas')
        ->assertSee('My New Idea');

    expect($user->ideas()->first())->toMatchArray([
        'title' => 'My New Idea',
        'description' => 'A description of my idea.',
        'status' => 'in_progress',
        'links' => ['https://laracasts.com', 'https://laravel.com'],
    ]);
});
