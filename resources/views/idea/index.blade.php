@php use App\IdeaStatus;use App\Models\Idea; @endphp
@props(['ideas', 'statusCounts'])

<x-layout>
    <div>
        <header class="py-8 md:py-12">
            <h1 class="text-2xl font-bold text-foreground">Ideas</h1>
            <p class="text-muted-foreground text-sm mt-2">Capture your thoughts. Make a plan</p>

            <x-card
                x-data
                @click="$dispatch('open-modal', 'create-idea')"
                is="button"
                type="button"
                aria-label="Create new idea"
                class="mt-10 cursor-pointer h-32 w-full text-left flex"
            >
                <p>What's your new idea?</p>
            </x-card>
        </header>

        <nav aria-label="Filter ideas by status">
            <a
                href="/ideas"
                class="btn {{ request()->has('status') ? 'btn-outlined' : '' }}"
            >
                All <span class="text-xs pl-3">{{ $statusCounts['all'] }}</span>
            </a>
            @foreach(IdeaStatus::cases() as $status)
                <a
                    href="/ideas?status={{ $status->value }}"
                    class="btn {{ request('status') === $status->value ? '' : 'btn-outlined'}}"
                >
                    {{ $status->label() }} <span class="text-xs pl-3">{{ $statusCounts->get($status->value) }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-10 text-muted-foreground">
            <div class="grid md:grid-cols-2 gap-6">
                @forelse($ideas as $idea)
                    @php
                        /** @var Idea $idea */
                    @endphp
                    <x-card href="{{ route('idea.show', $idea) }}">
                        <h3 class="text-foreground text-lg">{{ $idea->title }}</h3>
                        <x-idea.status-label status="{{ $idea->status }}">
                            {{ $idea->status->label() }}
                        </x-idea.status-label>

                        <div class="mt-5 line-clamp-3">{{ $idea->description }}</div>
                        <time class="mt-4 block"
                              datetime="{{ $idea->created_at->toIso8601String() }}">{{ $idea->created_at->diffForHumans() }}</time>
                    </x-card>
                @empty
                    <x-card>
                        <p>No ideas at this time</p>
                    </x-card>
                @endforelse
            </div>
        </div>

        <!-- modal -->
        <x-modal name="create-idea" title="Modal Title">
            <p>I am a modal</p>
        </x-modal>
    </div>
</x-layout>
