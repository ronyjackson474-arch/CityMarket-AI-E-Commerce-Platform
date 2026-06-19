@php
    $colors = [
        'pending' => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'packed' => 'bg-purple-100 text-purple-700',
        'shipped' => 'bg-indigo-100 text-indigo-700',
        'delivered' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
    $label = ucfirst($status);
    $class = $colors[$status] ?? 'bg-ink-100 text-ink-700';
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $class }}">{{ $label }}</span>
