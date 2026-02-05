@props(['label', 'name', 'type' => 'text'])
<div class="space-y-1">
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <input type="{{ $type }}" class="input" id="{{ $name }}" name="{{ $name }}"
           {{ $attributes }} value="{{ old($name) }}">

    @error($name)
    <p class="error">{{ $message }}</p>
    @enderror
</div>
