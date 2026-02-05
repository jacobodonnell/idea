@props(['name', 'title'])
<div
    x-data="{ show: false, name: @js($name) }"
    x-show="show"
    @open-modal.window="if ($event.detail === name) show = true"
    @keydown.esc.window="if (show) show = false"
    x-transition:enter="ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-4 -translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0 translate-y-0"
    x-transition:leave="ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0 -translate-y-4 -translate-x-4"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs"
    role="dialog"
    aria-modal="true"
    :aria-hidden="!show"
    aria-labelledby="modal-{{ $name }}-title"
    tabindex="-1"
>
    <x-card @click.outside="show = false">
        <div>
            <h2 id="modal-{{ $name }}-title" class="text-xl font-bold">{{ $title }}</h2>
        </div>

        <div>
            {{ $slot }}
        </div>
    </x-card>
</div>
