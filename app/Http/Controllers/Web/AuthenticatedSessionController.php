<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are not correct.',
            ]);
        }

        $user = $request->user();
        $tenant = $user?->tenant;

        if ($user && ! $user->isMaster() && $tenant && $tenant->is_active === false) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This workspace is inactive. Please contact PayChat support.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($request->user()->isMaster()
            ? route('master.dashboard')
            : route('tenant.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
