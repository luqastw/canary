<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Feature Flags' }} - {{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2">
                <svg class="h-10 w-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
                <span class="text-2xl font-bold text-gray-900">Feature Flags</span>
            </a>
            <p class="mt-2 text-sm text-gray-600">Multi-tenant feature flag management</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
            @if(session('status'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 border border-green-200">
                    <p class="text-sm text-green-800">{{ session('status') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200">
                    <ul class="text-sm text-red-800 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </div>

        <!-- Footer -->
        <p class="mt-6 text-center text-xs text-gray-500">
            Feature Flags Service v1.0.0
        </p>
    </div>
</body>
</html>
