<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreIdeaRequest;
use App\Http\Requests\UpdateIdeaRequest;
use App\IdeaStatus;
use App\Models\Idea;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function collect;
use function to_route;
use function view;

class IdeaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $status = IdeaStatus::tryFrom($request->query('status', ''));

        $ideas = Auth::user()
                     ->ideas()
                     ->when($status, fn($query, $status) => $query->where('status', $status->value))
                     ->latest()
                     ->get();

        return view('idea.index', [
            'ideas'        => $ideas,
            'statusCounts' => Idea::statusCounts(Auth::user()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIdeaRequest $request): RedirectResponse
    {
        $idea = Auth::user()->ideas()->create($request->safe()->except('steps'));

        $idea->steps()->createMany(
            collect($request->steps)->map(fn($step) => ['description' => $step])
        );

        return to_route('idea.index')->with('success', 'Idea created!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Idea $idea): View
    {
        
        return view('idea.show', [
            'idea' => $idea,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Idea $idea): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIdeaRequest $request, Idea $idea): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Idea $idea): RedirectResponse
    {
        $idea->delete();

        return to_route('idea.index')
            ->with('success', "You successfully deleted {$idea->title}");
    }
}
