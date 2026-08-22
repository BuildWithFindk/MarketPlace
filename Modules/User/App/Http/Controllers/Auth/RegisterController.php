<?php

namespace Modules\User\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\User\App\Http\Requests\RegisterRequest;
use Modules\User\App\Mail\OtpVerificationMail;
use Modules\User\App\Models\PendingRegistration;
use Modules\User\App\Models\User;
use Modules\User\App\Support\AuthRedirector;
use Throwable;

class RegisterController extends Controller
{
    public function __construct(
        private AuthRedirector $redirector,
    ) {}

    public function create(Request $request): View
    {
        $redirectTo = $this->redirector->rememberQueryTarget($request);

        return view('user::auth.register', [
            'redirectTo' => $redirectTo,
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $this->redirector->rememberInputTarget($request);

        $email = Str::lower($request->string('email')->toString());
        $phone = $request->string('phone')->toString();

        if (User::query()->where('email', $email)->exists()) {
            return back()->withInput()->withErrors(['email' => 'Email already registered.']);
        }

        if (User::query()->where('phone', $phone)->exists()) {
            return back()->withInput()->withErrors(['phone' => 'Phone number already registered.']);
        }

        try {
            $pending = PendingRegistration::createPending([
                'first_name' => $request->string('first_name')->toString(),
                'last_name' => $request->string('last_name')->toString(),
                'email' => $email,
                'phone' => $phone,
                'country_code' => $request->input('country_code', '+977'),
                'password' => $request->string('password')->toString(),
                'gender' => $request->input('gender'),
                'dob' => $request->string('dob')->toString(),
                'country' => $request->string('country')->toString(),
                'district' => $request->string('district')->toString(),
                'city' => $request->string('city')->toString(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['email' => 'We could not process your registration. Please try again.']);
        }

        $request->session()->put('pending_registration_token', $pending->token);

        try {
            Mail::to($pending->email)->sendNow(
                new OtpVerificationMail($pending)
            );
        } catch (Throwable $exception) {
            report($exception);

            if (! app()->environment('local')) {
                return redirect()
                    ->route('otp.verify')
                    ->withErrors(['email' => 'Registration saved, but we could not send an OTP. Please request a new OTP.']);
            }

            return redirect()
                ->route('otp.verify')
                ->with('message', 'OTP generated. Email delivery failed locally — check storage/logs/laravel.log.');
        }

        return redirect()->route('otp.verify')->with('message', 'OTP sent to '.$pending->email);
    }
}
