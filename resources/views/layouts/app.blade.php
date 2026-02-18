<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nederland Crowdfunding') - Branchevereniging Nederland Crowdfunding</title>
    <meta name="description" content="@yield('meta_description', 'Branchevereniging Nederland Crowdfunding - De branchevereniging voor crowdfundingplatforms voor bedrijfsfinanciering.')">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-[#f8fafc] text-gray-800 font-sans antialiased">
    <x-navbar />

    <main class="flex-1">
        @yield('content')
    </main>

    <x-footer />
</body>
</html>