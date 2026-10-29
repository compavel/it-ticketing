@props(['status'])

@php
    $styles = [
        \App\TicketStatus::Open->value => 'bg-blue-100 text-blue-800',
        \App\TicketStatus::InProgress->value => 'bg-amber-100 text-amber-800',
        \App\TicketStatus::Resolved->value => 'bg-green-100 text-green-800',
        \App\TicketStatus::Closed->value => 'bg-gray-100 text-gray-600',
    ];

    $labels = [
        \App\TicketStatus::Open->value => 'Open',
        \App\TicketStatus::InProgress->value => 'In Progress',
        \App\TicketStatus::Resolved->value => 'Resolved',
        \App\TicketStatus::Closed->value => 'Closed',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.$styles[$status->value]]) }}>
    {{ $labels[$status->value] }}
</span>
