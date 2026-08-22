<?php

namespace Modules\User\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\User\App\Http\Requests\LoginRequest;
use Modules\User\App\Models\User;
use Modules\User\App\Support\AuthRedirector;

class LoginController extends Controller
{
    public function __construct(
        private AuthRedirector $redirector,
    ) {}

    public function create(Request $request): View
    {
        $redirectTo = $this->redirector->rememberQueryTarget($request);

        return view('user::auth.login', [
            'redirectTo' => $redirectTo,
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $this->redirector->rememberInputTarget($request);

        if (
            ! Auth::attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ], $request->boolean('remember'))
        ) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.'])
                ->with('error', 'Invalid email or password.');
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('error', 'Unable to sign in with this account.');
        }

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('register')
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is not verified. Please register again to get a new OTP.'])
                ->with('error', 'Your account is not verified. Please register again to get a new OTP.');
        }

        $status = (string) ($user->status?->value ?? $user->status ?? '');

        if ($status === 'banned') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Your account has been banned. Please contact support.');
        }

        if ($status === 'suspended') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Your account is temporarily suspended. Please try again later.');
        }

        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is not active. Please contact support.'])
                ->with('error', 'Your account is not active. Please contact support.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
