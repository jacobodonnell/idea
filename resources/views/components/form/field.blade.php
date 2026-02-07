@props(['label' => false, 'name', 'type' => 'text', 'value' => ''])
<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="label">{{ $label }}</label>
    @endif

    @if($type === 'textarea')
        <textarea
            class="textarea"
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $attributes->except('value') }}
        >{{ old($name, $value) }}</textarea>
    @else
        <input
            type="{{ $type }}"
            class="input"
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $attributes->except('value') }}
            value="{{ old($name, $value) }}"
        >
    @endif

    <x-form.error name="{{ $name }}"/>
</div>
