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
                $idea->steps()->delete();

                if ($attributes['steps']) {
                    $idea->steps()->createMany(
                        collect($attributes['steps'])->map(fn ($step) => ['description' => $step])->all()
                    );
                }
            }
        });
    }
}
