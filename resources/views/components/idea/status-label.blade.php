@php use App\IdeaStatus; @endphp
@props(['status' => 'pending'])
@php
    $classes = "inline-block rounded-full border px-2 py-1 text-xs font-medium";


    if ('pending' === $status) {
        $classes .= ' bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
    } elseif ($status === 'in_progress') {
        $classes .= ' bg-blue-500/10 text-blue-500 border-blue-500/20';
    } elseif ($status === 'completed') {
        $classes .= ' bg-primary/10 text-primary border-primary/20';
    }
@endphp

<div class="mt-1">
    <span
        {{ $attributes(['class' => $classes]) }}
    >
        {{ $slot }}
    </span>
</div>
