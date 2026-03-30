<x-layouts.guest title="Register">
    <h2 class="text-xl font-bold text-gray-900 text-center mb-6">
        Create your account
    </h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <x-input 
            name="name" 
            type="text" 
            label="Organization name"
            placeholder="Acme Inc."
            hint="This will be your tenant name"
            required
            autofocus
        />

        <x-input 
            name="email" 
            type="email" 
            label="Email address"
            placeholder="admin@acme.com"
            required
        />

        <x-input 
            name="password" 
            type="password" 
            label="Password"
            placeholder="At least 8 characters"
            hint="Minimum 8 characters"
            required
        />

        <x-input 
            name="password_confirmation" 
            type="password" 
            label="Confirm password"
            placeholder="Repeat your password"
            required
        />

        <x-button type="primary" class="w-full">
            Create account
        </x-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
            Sign in
        </a>
    </p>
</x-layouts.guest>
