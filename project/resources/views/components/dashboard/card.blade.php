@props(['title', 'value'])

<div class="bg-white rounded-lg shadow p-4">
    <p class="text-sm text-gray-500">{{ __($title) }}</p>
    <p class="text-2xl font-bold mt-1">{{ $value }}</p>
</div>
