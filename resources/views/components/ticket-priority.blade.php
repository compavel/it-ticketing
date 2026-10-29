@props(['priority'])

@php
    $styles = [
        \App\TicketPriority::Low->value => 'bg-gray-100 text-gray-700',
        \App\TicketPriority::Medium->value => 'bg-amber-100 text-amber-800',
        \App\TicketPriority::High->value => 'bg-red-100 text-red-800',
    ];

    $labels = [
        \App\TicketPriority::Low->value => 'Low',
        \App\TicketPriority::Medium->value => 'Medium',
        \App\TicketPriority::High->value => 'High',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.$styles[$priority->value]]) }}>
    {{ $labels[$priority->value] }}
</span>
