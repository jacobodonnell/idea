@props(['label' => false, 'name', 'type' => 'text'])
<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="label">{{ $label }}</label>
    @endif

    @if($type === 'textarea')
        <textarea
            class="textarea"
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $attributes }}
        >{{ old($name) }}</textarea>
    @else
        <input
            type="{{ $type }}"
            class="input"
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $attributes }}
            value="{{ old($name) }}"
        >
    @endif

    <x-form.error name="{{ $name }}"/>
</div>
