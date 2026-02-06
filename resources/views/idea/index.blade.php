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
                data-test="create-idea-button"
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
                 newLink: '',
                 newStep: '',
                 links: [],
                 steps: [],
                 removeLink(indexToRemove) {
                    this.links.splice(indexToRemove, 1);
                 },
                 removeStep(indexToRemove) {
                    this.steps.splice(indexToRemove, 1);
                 }
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

                    <fieldset class="space-y-2">
                        <legend class="label">Status</legend>

                        <div class="flex gap-x-3">
                            @foreach(IdeaStatus::cases() as $status)
                                <label class="flex-1">
                                    <input
                                        type="radio"
                                        name="status"
                                        value="{{ $status->value }}"
                                        class="peer sr-only"
                                        data-test="button-status-{{ $status->value }}"
                                        @checked($loop->first)
                                    >
                                    <span
                                        class="btn btn-outlined flex items-center justify-center py-5 peer-checked:bg-primary peer-checked:text-primary-foreground peer-checked:border-transparent peer-focus-visible:ring-2 peer-focus-visible:ring-primary peer-focus-visible:ring-offset-2 cursor-pointer">
                                        {{ $status->label() }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <x-form.error name="status"/>
                    </fieldset>

                    <x-form.field
                        label="Description"
                        name="description"
                        type="textarea"
                        placeholder="Describe your idea..."
                    />

                    <div>
                        <fieldset class="space-y-3">
                            <legend class="label">Actionable Steps</legend>

                            <template x-for="(step, index) in steps" :key="step">
                                <div class="flex gap-x-2 items-center">
                                    <input
                                        type="text"
                                        class="input"
                                        name="steps[]"
                                        x-model="step"
                                    >
                                    <button
                                        type="button"
                                        @click="removeStep(index)"
                                        :aria-label="'Remove ' + step"
                                        class="form-muted-icon"
                                    >
                                        <x-icons.close/>
                                    </button>
                                </div>
                            </template>

                            <div class="flex gap-x-2 items-center">
                                <input
                                    x-model="newStep"
                                    type="text"
                                    id="new-step"
                                    data-test="new-step"
                                    placeholder="What needs to be done?"
                                    class="input flex-1"
                                    spellcheck="true"
                                >
                                <button
                                    type="button"
                                    @click="steps.push(newStep.trim()); newStep = ''"
                                    :disabled="newStep.trim().length === 0"
                                    aria-label="Add new step"
                                    class="form-muted-icon"
                                    data-test="add-new-step-button"
                                >
                                    <x-icons.close class="rotate-45"/>
                                </button>
                            </div>
                        </fieldset>
                    </div>

                    <div>
                        <fieldset class="space-y-3">
                            <legend class="label">Links</legend>

                            {{-- Alpine.js x-for directive - IDE may flag as error but is valid syntax --}}
                            <template x-for="(link, index) in links" :key="link">
                                <div class="flex gap-x-2 items-center">
                                    <input
                                        type="text"
                                        class="input"
                                        name="links[]"
                                        x-model="link"
                                    >
                                    <button
                                        type="button"
                                        @click="removeLink(index)"
                                        :aria-label="'Remove ' + link"
                                        class="form-muted-icon"
                                    >
                                        <x-icons.close/>
                                    </button>
                                </div>
                            </template>

                            <div class="flex gap-x-2 items-center">
                                <input
                                    x-model="newLink"
                                    type="url"
                                    id="new-link"
                                    data-test="new-link"
                                    placeholder="https://example.com"
                                    autocomplete="url"
                                    class="input flex-1"
                                    spellcheck="false"
                                >
                                <button
                                    type="button"
                                    @click="links.push(newLink.trim()); newLink = ''"
                                    :disabled="newLink.trim().length === 0"
                                    aria-label="Add new link"
                                    class="form-muted-icon"
                                    data-test="add-new-link-button"
                                >
                                    <x-icons.close class="rotate-45"/>
                                </button>
                            </div>
                        </fieldset>
                    </div>

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
