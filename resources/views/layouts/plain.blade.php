<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'WACRM' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #06080f; color: #e2e8f0; }
        
        /* Select and Option dropdown dark style styling */
        select {
            background-color: #0d1220 !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        select option {
            background-color: #0d1220 !important;
            color: #f8fafc !important;
        }
    </style>
    @livewireStyles
</head>
<body class="antialiased min-h-screen bg-[#06080f]">
    {{ $slot }}
    @livewireScripts
</body>
</html>
