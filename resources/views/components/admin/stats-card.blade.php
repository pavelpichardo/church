@props(['label', 'value', 'color' => 'blue'])

@php
$borderColors = [
    'blue'   => 'border-blue-500',
    'green'  => 'border-green-500',
    'yellow' => 'border-yellow-500',
    'red'    => 'border-red-500',
    'indigo' => 'border-indigo-500',
    'purple' => 'border-purple-500',
];
$border = $borderColors[$color] ?? $borderColors['blue'];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 border-t-4 {{ $border }} p-5">
    <p class="text-3xl font-bold text-gray-800">{{ $value }}</p>
    <p class="text-sm text-gray-500 mt-1">{{ $label }}</p>
</div>
