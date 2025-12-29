<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.app-head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">


    {{ $slot }}

    @fluxScripts

    {{-- Footer Script --}}
    {!! $settings['scripts.footer'] ?? ''  !!}
</body>

</html>