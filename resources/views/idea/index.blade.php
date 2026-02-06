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
                href="{{ route('idea.index') }}"
                class="btn {{ request()->has('status') ? 'btn-outlined' : '' }}"
            >
                All <span class="text-xs pl-3">{{ $statusCounts['all'] }}</span>
            </a>
            @foreach(IdeaStatus::cases() as $status)
                <a
                    href="{{ route('idea.index', ['status' => $status->value]) }}"
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

        <x-modal name="create-idea" title="Create New Idea">
            <form
                x-data="{
                    status: 'pending'
                }"
                action="{{ route('idea.store') }}"
                method="POST"
            >
                @csrf

                <div class="space-y-6">
                    <x-form.field
                        label="Title"
                        name="title"
                        placeholder="Enter an idea for your title"
                        autofocus
                        required
                    />

                    <div class="space-y-2">
                        <label for="status" class="label">Status</label>

                        <div class="flex gap-x-3">
                            @foreach(IdeaStatus::cases() as $status)
                                <button
                                    type="button"
                                    @click="status = @js($status->value)"
                                    class="btn flex-1 h-10"
                                    :class="{'btn-outlined': status !== @js($status->value)}"
                                >
                                    {{ $status->label() }}
                                </button>
                            @endforeach

                            <input type="hidden" name="status" :value="status">

                            <x-form.error name="status"/>
                        </div>
                    </div>

                    <x-form.field
                        label="Description"
                        name="description"
                        type="textarea"
                        placeholder="Describe your idea..."
                    />

                    <div class="flex justify-end gap-x-5">
                        <button
                            @click="$dispatch('close-modal')"
                            type="button"
                            class="hover:opacity-70 transition-opacity duration-75">
                            Cancel
                        </button>
                        <button type="submit" class="btn">Create</button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>
</x-layout>
