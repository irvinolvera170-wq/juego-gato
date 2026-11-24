@props(['partida', 'user' => null])

@php
    [$color, $texto] = $partida->resultadoPara($user);
    $extra = in_array($color, ['warning', 'info']) ? ' text-dark' : '';
@endphp

<span class="badge bg-{{ $color }}{{ $extra }}">{{ $texto }}</span>