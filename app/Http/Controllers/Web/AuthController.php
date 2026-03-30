<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Contracts\Services\AuthServiceInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterTenantRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {}

    /**
     * Show login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Show registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(RegisterTenantRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $result = $this->authService->registerTenant(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
        );

        Auth::login($result['user']);

        return redirect()->route('dashboard')
            ->with('success', 'Registration successful! Welcome to Feature Flags.');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
