<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Idea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function collect;

class UpdateIdea
{
    public function handle(Idea $idea, array $attributes): void
    {
        $data = collect($attributes)->only([
            'title', 'description', 'status', 'links',
        ])->toArray();

        // Check if user wants to remove the image
        if (isset($attributes['remove_image']) && $attributes['remove_image'] === '1') {
            if ($idea->image_path) {
                Storage::disk('public')->delete($idea->image_path);
            }
            $data['image_path'] = null;
        }

        // Handle new image upload (this replaces any existing image)
        if (isset($attributes['image'])) {
            if ($idea->image_path) {
                Storage::disk('public')->delete($idea->image_path);
            }
            $data['image_path'] = $attributes['image']->store('ideas', 'public');
        }

        DB::transaction(function () use ($idea, $data, $attributes) {
            $idea->update($data);

            if (array_key_exists('steps', $attributes)) {
                $submittedSteps = collect($attributes['steps'] ?? []);

                // Normalize steps: convert strings to objects for consistent handling
                $normalizedSteps = $submittedSteps->map(function ($step) {
                    if (is_string($step)) {
                        return ['id' => null, 'description' => $step, 'completed' => false];
                    }

                    return $step;
                });

                // Get IDs of steps being kept/updated
                $submittedStepIds = $normalizedSteps->pluck('id')->filter();

                // Delete steps that were removed from the form
                $idea->steps()->whereNotIn('id', $submittedStepIds)->delete();

                // Update existing steps or create new ones
                foreach ($normalizedSteps as $stepData) {
                    if (! empty($stepData['id'])) {
                        // Update existing step - preserve completed status from form
                        $idea->steps()->where('id', $stepData['id'])->update([
                            'description' => $stepData['description'],
                            'completed' => $stepData['completed'] ?? false,
                        ]);
                    } else {
                        // Create new step
                        $idea->steps()->create([
                            'description' => $stepData['description'],
                            'completed' => false, // New steps are always incomplete
                        ]);
                    }
                }
            }
        });
    }
}
