<?php

/** @noinspection ALL */

declare(strict_types=1);

use App\IdeaStatus;
use App\Models\Idea;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('creates a new idea with all fields', function () {
    $user = User::factory()->create();

    actingAs($user)->post(route('idea.store'), [
        'title' => 'My New Idea',
        'description' => 'A description of my idea.',
        'status' => IdeaStatus::IN_PROGRESS->value,
        'links' => ['https://laracasts.com', 'https://laravel.com'],
        'steps' => ['Step one', 'Step two', 'Step three'],
    ])->assertRedirect(route('idea.index'))
        ->assertSessionHas('success', 'Idea created!');

    $idea = $user->ideas()->first();

    expect($idea)->toMatchArray([
        'title' => 'My New Idea',
        'description' => 'A description of my idea.',
        'status' => 'in_progress',
        'links' => ['https://laracasts.com', 'https://laravel.com'],
    ]);

    // this is
    expect($idea->steps)->toHaveCount(3);
    expect($idea->steps->pluck('description')->toArray())->toBe([
        'Step one',
        'Step two',
        'Step three',
    ]);
});

it('requires authentication to create an idea', function () {
    $this->post(route('idea.store'), [
        'title' => 'Unauthenticated Idea',
        'status' => IdeaStatus::PENDING->value,
    ])->assertRedirect(route('login'));
});

it('validates required fields', function () {
    $user = User::factory()->create();

    actingAs($user)->post(route('idea.store'), [])
        ->assertSessionHasErrors(['title', 'status']);
});

it('edits an existing idea', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()
        ->for($user)
        ->withSteps(1)
        ->create([
            'title' => 'Original Idea',
            'description' => 'Original description.',
            'status' => IdeaStatus::PENDING,
            'links' => ['https://example.com'],
        ]);

    actingAs($user)->patch(route('idea.update', $idea), [
        'title' => 'Updated Idea',
        'description' => 'Updated description.',
        'status' => IdeaStatus::COMPLETED->value,
        'links' => ['https://laravel.com', 'https://laracasts.com'],
        'steps' => ['Step one', 'Step two'],
    ])->assertRedirect(route('idea.index'))
        ->assertSessionHas('success', 'Idea updated!');

    $idea->refresh();

    expect($idea)->toMatchArray([
        'title' => 'Updated Idea',
        'description' => 'Updated description.',
        'status' => 'completed',
        'links' => ['https://laravel.com', 'https://laracasts.com'],
    ]);

    expect($idea->steps)->toHaveCount(2);
    expect($idea->steps->pluck('description')->toArray())->toBe([
        'Step one',
        'Step two',
    ]);
});
