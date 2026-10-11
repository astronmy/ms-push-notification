<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center bg-[var(--bo-bg)] px-4 py-10">
        <a href="{{ route('login') }}" class="mb-6 text-2xl font-semibold tracking-tight text-white">{{ config('app.name') }}</a>
        <div class="w-full sm:max-w-md overflow-hidden rounded-lg bg-white px-6 py-6 shadow-md">
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
</body>
</html>
