<?php

namespace Modules\User\App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckIfVerified
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User && ! $user->hasVerifiedEmail()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('register')
                ->with('error', 'Account not verified. Please register and verify your OTP.');
        }

        return $next($request);
    }
}
