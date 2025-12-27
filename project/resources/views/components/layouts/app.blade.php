<x-layouts.app.sidebar :title="$title ?? null">
    <x-toast />
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
