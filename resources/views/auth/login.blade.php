<x-layouts.guest title="Login">
    <h2 class="text-xl font-bold text-gray-900 text-center mb-6">
        Sign in to your account
    </h2>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <x-input 
            name="email" 
            type="email" 
            label="Email address"
            placeholder="you@example.com"
            required
            autofocus
        />

        <x-input 
            name="password" 
            type="password" 
            label="Password"
            placeholder="Your password"
            required
        />

        <x-checkbox 
            name="remember" 
            label="Remember me"
        />

        <x-button type="primary" class="w-full">
            Sign in
        </x-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
            Register
        </a>
    </p>
</x-layouts.guest>
