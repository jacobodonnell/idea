<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Idea</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground">
    <x-layouts.nav/>
    <main class="max-w-7xl mx-auto px-6">
        {{ $slot }}
    </main>

    @session('success')
    <div
        class="bg-primary px-4 py-3 fixed bottom-4 right-4 rounded-lg"
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition.duration.500ms
    >
        {{ $value }}
    </div>
    @endsession
</body>
</html>
