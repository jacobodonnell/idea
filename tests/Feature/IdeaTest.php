<?php

use App\Models\Idea;
use App\Models\User;

it('belongs to a user', function () {
    $idea = Idea::factory()->create();

    expect($idea->user)->toBeInstanceOf(User::class);
});

it('can have steps', function () {
    $idea = Idea::factory()->create();

    expect($idea->steps)->toBeEmpty();

    $idea->steps()->create([
        'description' => 'do the thing'
    ]);

    expect($idea->fresh()->steps)->toHaveCount(1);
});
