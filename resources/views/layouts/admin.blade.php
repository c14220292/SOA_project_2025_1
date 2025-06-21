<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin - Restaurant Reservation')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-[#1a1a1a] text-white">

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="successMessage">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="errorMessage">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="errorMessage">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    @stack('scripts')

    <script>
        // Auto hide flash messages
        setTimeout(() => {
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');

            if (successMessage) {
                successMessage.style.opacity = '0';
                setTimeout(() => successMessage.remove(), 300);
            }

            if (errorMessage) {
                errorMessage.style.opacity = '0';
                setTimeout(() => errorMessage.remove(), 300);
            }
        }, 5000);
    </script>
</body>

</html>
