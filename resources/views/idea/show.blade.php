<x-layout>
    <div class="py-8 max-w-4xl mx-auto">
        <div class="flex justify-between items-center">
            <a href="{{ route('idea.index') }}"
               class="flex items-center gap-x-2 text-sm font-medium hover:text-foreground/75 transition-colors duration-300"
            >
                <x-icons.arrow-back />
                Back to Ideas
            </a>

            <div class="flex gap-x-3 items-center">
                <button class="btn btn-outlined">
                    <x-icons.edit class=""/>
                    Edit Idea
                </button>
                <form action="{{ route('idea.destroy', $idea) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button
                        class="btn btn-outlined text-red-500"
                        type="submit"
                    >
                        <x-icons.trash class=""/>
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-8 space-y-6">
            <h1 class="font-bold text-4xl">
                {{ $idea->title }}
            </h1>

            <div class="flex gap-x-3 items-center">
                <x-idea.status-label :status="$idea->status->value">
                    {{ $idea->status->label() }}
                </x-idea.status-label>

                <div class="text-muted-foreground text-sm">{{ $idea->created_at->diffForHumans() }}</div>
            </div>

            <x-card>
                <div class="text-foreground max-w-none cursor-pointer">
                    {{ $idea->description }}
                </div>
            </x-card>

            @if ($idea->links->count())
                <div>
                    <h3 class="font-bold text-xl mt-6">Links</h3>

                    <div class="mt-4 space-y-2">
                        @foreach($idea->links as $link)
                            <x-card
                                :href="$link"
                                class="text-primary font-medium flex gap-x-3 items-center"
                            >
                                <x-icons.external/> {{ $link }}
                            </x-card>
                        @endforeach
                    </div>

                </div>
            @endif
        </div>
    </div>
</x-layout>
