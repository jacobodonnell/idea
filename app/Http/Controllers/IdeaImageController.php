<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Idea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class IdeaImageController extends Controller
{
    /**
     * Remove the image from the specified idea.
     */
    public function destroy(Idea $idea): RedirectResponse
    {
        if ($idea->image_path) {
            Storage::disk('public')->delete($idea->image_path);
            $idea->update(['image_path' => null]);
        }

        return back()->with('success', 'Image removed!');
    }
}
