<?php

/** @noinspection ALL */

declare(strict_types=1);

use App\IdeaStatus;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
    ])->assertRedirect(route('idea.show', $idea))
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

it('creates a new idea with an image', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $image = UploadedFile::fake()->image('test.jpg', 100, 100);

    actingAs($user)->post(route('idea.store'), [
        'title' => 'Idea with Image',
        'status' => IdeaStatus::PENDING->value,
        'image' => $image,
    ])->assertRedirect(route('idea.index'))
        ->assertSessionHas('success', 'Idea created!');

    $idea = $user->ideas()->first();

    expect($idea->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($idea->image_path);
});

it('updates an idea and replaces the existing image', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    // Create idea with existing image
    $existingImage = UploadedFile::fake()->image('existing.jpg');
    $existingPath = $existingImage->store('ideas', 'public');
    $idea = Idea::factory()->for($user)->create(['image_path' => $existingPath]);

    // Upload new image
    $newImage = UploadedFile::fake()->image('new.jpg');

    actingAs($user)->patch(route('idea.update', $idea), [
        'title' => $idea->title,
        'status' => $idea->status->value,
        'image' => $newImage,
    ])->assertRedirect(route('idea.show', $idea))
        ->assertSessionHas('success', 'Idea updated!');

    $idea->refresh();

    // Old image should be deleted
    Storage::disk('public')->assertMissing($existingPath);

    // New image should exist
    expect($idea->image_path)->not->toBe($existingPath);
    Storage::disk('public')->assertExists($idea->image_path);
});

it('removes an image when remove_image flag is set', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    // Create idea with existing image
    $existingImage = UploadedFile::fake()->image('existing.jpg');
    $existingPath = $existingImage->store('ideas', 'public');
    $idea = Idea::factory()->for($user)->create(['image_path' => $existingPath]);

    actingAs($user)->patch(route('idea.update', $idea), [
        'title' => $idea->title,
        'status' => $idea->status->value,
        'remove_image' => '1',
    ])->assertRedirect(route('idea.show', $idea))
        ->assertSessionHas('success', 'Idea updated!');

    $idea->refresh();

    // Image should be deleted from storage
    Storage::disk('public')->assertMissing($existingPath);

    // image_path should be null
    expect($idea->image_path)->toBeNull();
});

it('uploads new image even when remove_image flag is set', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    // Create idea with existing image
    $existingImage = UploadedFile::fake()->image('existing.jpg');
    $existingPath = $existingImage->store('ideas', 'public');
    $idea = Idea::factory()->for($user)->create(['image_path' => $existingPath]);

    // Upload new image with remove_image flag
    $newImage = UploadedFile::fake()->image('new.jpg');

    actingAs($user)->patch(route('idea.update', $idea), [
        'title' => $idea->title,
        'status' => $idea->status->value,
        'remove_image' => '1',
        'image' => $newImage,
    ])->assertRedirect(route('idea.show', $idea))
        ->assertSessionHas('success', 'Idea updated!');

    $idea->refresh();

    // Old image should be deleted
    Storage::disk('public')->assertMissing($existingPath);

    // New image should exist (not null)
    expect($idea->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($idea->image_path);
});

it('keeps existing image when remove_image is 0 or not set', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    // Create idea with existing image
    $existingImage = UploadedFile::fake()->image('existing.jpg');
    $existingPath = $existingImage->store('ideas', 'public');
    $idea = Idea::factory()->for($user)->create(['image_path' => $existingPath]);

    actingAs($user)->patch(route('idea.update', $idea), [
        'title' => 'Updated Title',
        'status' => $idea->status->value,
        'remove_image' => '0',
    ])->assertRedirect(route('idea.show', $idea))
        ->assertSessionHas('success', 'Idea updated!');

    $idea->refresh();

    // Original image should still exist
    Storage::disk('public')->assertExists($existingPath);
    expect($idea->image_path)->toBe($existingPath);
});

it('rejects non-image file uploads', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $pdfFile = UploadedFile::fake()->create('document.pdf', 100);

    actingAs($user)->post(route('idea.store'), [
        'title' => 'Idea with PDF',
        'status' => IdeaStatus::PENDING->value,
        'image' => $pdfFile,
    ])->assertSessionHasErrors('image');
});

it('rejects images larger than 5MB', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $largeImage = UploadedFile::fake()->image('large.jpg')->size(6144); // 6MB

    actingAs($user)->post(route('idea.store'), [
        'title' => 'Idea with Large Image',
        'status' => IdeaStatus::PENDING->value,
        'image' => $largeImage,
    ])->assertSessionHasErrors('image');
});

it('rejects invalid remove_image values', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->for($user)->create();

    actingAs($user)->patch(route('idea.update', $idea), [
        'title' => $idea->title,
        'status' => $idea->status->value,
        'remove_image' => 'true', // invalid - must be '0' or '1'
    ])->assertSessionHasErrors('remove_image');
});

it('updates existing step description', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->for($user)->withSteps(2)->create();
    $existingSteps = $idea->steps;

    actingAs($user)->patch(route('idea.update', $idea), [
        'title' => $idea->title,
        'status' => $idea->status->value,
        'steps' => [
            [
                'id' => $existingSteps[0]->id,
                'description' => 'Updated first step',
                'completed' => false,
            ],
            [
                'id' => $existingSteps[1]->id,
                'description' => 'Updated second step',
                'completed' => false,
            ],
        ],
    ])->assertRedirect();

    $idea->refresh();

    expect($idea->steps)->toHaveCount(2);
    expect($idea->steps[0]->id)->toBe($existingSteps[0]->id);
    expect($idea->steps[0]->description)->toBe('Updated first step');
    expect($idea->steps[1]->id)->toBe($existingSteps[1]->id);
    expect($idea->steps[1]->description)->toBe('Updated second step');
});

it('adds new steps while keeping existing ones', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->for($user)->withSteps(1)->create();
    $existingStep = $idea->steps->first();

    actingAs($user)->patch(route('idea.update', $idea), [
        'title' => $idea->title,
        'status' => $idea->status->value,
        'steps' => [
            [
                'id' => $existingStep->id,
                'description' => $existingStep->description,
                'completed' => false,
            ],
            [
                'id' => null,
                'description' => 'Brand new step',
                'completed' => false,
            ],
        ],
    ])->assertRedirect();

    $idea->refresh();

    expect($idea->steps)->toHaveCount(2);
    expect($idea->steps->pluck('description')->toArray())->toBe([
        $existingStep->description,
        'Brand new step',
    ]);
});

it('handles adding updating and deleting steps simultaneously', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->for($user)->withSteps(3)->create();
    $originalSteps = $idea->steps;

    actingAs($user)->patch(route('idea.update', $idea), [
        'title' => $idea->title,
        'status' => $idea->status->value,
        'steps' => [
            // Keep and update first step
            [
                'id' => $originalSteps[0]->id,
                'description' => 'Updated first step',
                'completed' => true,
            ],
            // Delete second step (by omitting it)
            // Keep third step unchanged
            [
                'id' => $originalSteps[2]->id,
                'description' => $originalSteps[2]->description,
                'completed' => false,
            ],
            // Add new fourth step
            [
                'id' => null,
                'description' => 'Brand new fourth step',
                'completed' => false,
            ],
        ],
    ])->assertRedirect(route('idea.show', $idea));

    $idea->refresh();

    expect($idea->steps)->toHaveCount(3);
    expect($idea->steps[0]->description)->toBe('Updated first step');
    expect($idea->steps[0]->completed)->toBeTruthy();
    expect($idea->steps[1]->id)->toBe($originalSteps[2]->id);
    expect($idea->steps[2]->description)->toBe('Brand new fourth step');
});
