<?php

declare(strict_types=1);

use App\IdeaStatus;
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

    expect($idea->steps)->toHaveCount(3);
    expect($idea->steps->pluck('description')->toArray())->toBe([
        'Step one',
        'Step two',
        'Step three',
    ]);
});

it('creates a new idea with minimal fields', function () {
    $user = User::factory()->create();

    actingAs($user)->post(route('idea.store'), [
        'title' => 'Minimal Idea',
        'status' => IdeaStatus::PENDING->value,
    ])->assertRedirect(route('idea.index'));

    $idea = $user->ideas()->first();

    expect($idea)->toMatchArray([
        'title' => 'Minimal Idea',
        'description' => null,
        'status' => 'pending',
    ]);

    expect($idea->links)->toBeEmpty();
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
