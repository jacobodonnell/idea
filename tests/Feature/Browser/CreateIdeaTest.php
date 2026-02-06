<?php

declare(strict_types=1);

use App\Models\User;
use function Pest\Laravel\{actingAs, assertDatabaseHas};

it('creates a new idea', function () {
    actingAs(User::factory()->create());

    visit('/ideas')
        ->click('@create-idea-button')
        ->fill('title', 'My New Idea')
        ->click('In Progress')
        ->fill('description', 'A description of my idea.')
        ->click('Create')
        ->assertPathIs('/ideas')
        ->assertSee('My New Idea');

    assertDatabaseHas('ideas', [
        'title'       => 'My New Idea',
        'description' => 'A description of my idea.',
        'status'      => 'in_progress',
    ]);
});
