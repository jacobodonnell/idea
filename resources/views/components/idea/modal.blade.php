@php use App\IdeaStatus; @endphp
@props(['idea' => new App\Models\Idea()])
<x-modal
    name="{{ $idea->exists ? 'edit-idea' : 'create-idea' }}"
    title="{{ $idea->exists ? 'Edit Idea' : 'Create New Idea' }}"
>
    <form
        x-data="{
                 newLink: '',
                 newStep: '',
                 links: @js(old('links', $idea->links ?? [])),
                 steps: @js(old('steps', $idea->exists ? $idea->steps->pluck('description')->toArray() : [])),
                 imagePreview: null,
                 imageRemoved: false,
                 removeLink(indexToRemove) {
                    this.links.splice(indexToRemove, 1);
                 },
                 removeStep(indexToRemove) {
                    this.steps.splice(indexToRemove, 1);
                 },
                 previewImage(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.imagePreview = URL.createObjectURL(file);
                        this.imageRemoved = false;
                    }
                 },
                 removeImage() {
                    this.imagePreview = null;
                    this.imageRemoved = true;
                    this.$refs.imageInput.value = '';
                 }
                }"
        action="{{ $idea->exists ? route('idea.update', $idea) : route('idea.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf
        @if($idea->exists)
            @method('PATCH')
        @endif

        <div class="space-y-6">
            <x-form.field
                label="Title"
                name="title"
                data-test="title"
                placeholder="Enter an idea for your title"
                autofocus
                required
                :value="$idea->title"
            />

            <fieldset class="space-y-2">
                <legend class="label">Status</legend>

                @php
                    $selectedStatus = old('status', $idea->status?->value);
                @endphp
                <div class="flex gap-x-3">
                    @foreach(IdeaStatus::cases() as $status)
                        <label class="flex-1" data-text="label-status-{{ $status->value }}">
                            <input
                                type="radio"
                                name="status"
                                value="{{ $status->value }}"
                                class="peer sr-only"
                                data-test="button-status-{{ $status->value }}"
                                @checked($selectedStatus ? $selectedStatus == $status->value : $loop->first)
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
                data-test="description"
                type="textarea"
                placeholder="Describe your idea..."
                :value="$idea->description"
            />

            <div class="space-y-2">
                <label for="image" class="label">Featured Image</label>

                {{-- Show new image preview if uploaded --}}
                <div x-show="imagePreview" class="mb-2">
                    <div class="relative inline-block">
                        <img :src="imagePreview" alt="New image preview" class="max-w-xs rounded">
                        <button
                            type="button"
                            @click="removeImage()"
                            class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors"
                            aria-label="Remove image"
                        >
                            <x-icons.trash class="h-4 w-4"/>
                        </button>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">New image preview</p>
                </div>

                {{-- Show current image if exists and not removed --}}
                @if($idea->image_path)
                    <div x-show="!imagePreview && !imageRemoved" class="mb-2">
                        <div class="relative inline-block">
                            <img src="{{ asset('storage/' . $idea->image_path) }}" alt="Current featured image" class="max-w-xs rounded">
                            <button
                                type="button"
                                @click="removeImage()"
                                class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors"
                                aria-label="Remove current image"
                            >
                                <x-icons.trash class="h-4 w-4"/>
                            </button>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">Current image</p>
                    </div>
                @endif

                <input type="file" name="image" accept="image/*" @change="previewImage($event)" x-ref="imageInput">
                <input type="hidden" name="remove_image" :value="imageRemoved ? '1' : '0'">
                <x-form.error name="image"/>
            </div>

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
                <button type="submit" class="btn" data-test="create-idea-submit">Create</button>
            </div>
        </div>
    </form>
</x-modal>
