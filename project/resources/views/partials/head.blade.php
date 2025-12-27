<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ $settings['seo.meta_title'] ?? config('app.name') }}
</title>

<meta name="description" content="{{ $settings['seo.meta_description'] ?? '' }}">

<meta name="keywords" content="{{ $settings['seo.keywords'] ?? '' }}">

@if (!empty($settings['branding.favicon']))
    <link rel="icon" href="{{ asset('storage/' . $settings['branding.favicon']) }}">
@endif

<style>
    :root {
        --color-secondary:
            {{ $settings['colors.secondary'] ?? '#0ea5e9' }};
        --color-accent:
            {{ $settings['colors.accent'] ?? '#22c55e' }};
        --color-background:
            {{ $settings['colors.background'] ?? '#0b1220' }};
    }

    .bg-accent {
        background-color: var(--color-accent);
    }

    .text-secondary {
        color: var(--color-secondary);
    }
</style>


<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance