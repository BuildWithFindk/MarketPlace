<?php

namespace Modules\User\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Modules\User\App\Mail\OtpVerificationMail;
use Modules\User\App\Models\PendingRegistration;
use Modules\User\App\Models\User;
use RuntimeException;
use Throwable;

class OtpVerificationController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $token = $request->session()->get('pending_registration_token');

        if (! $token) {
            return redirect()->route('register')->with('error', 'Session expired, please register again.');
        }

        $pending = PendingRegistration::findByToken($token);

        if (! $pending) {
            $request->session()->forget('pending_registration_token');

            return redirect()->route('register')->with('error', 'Session expired, please register again.');
        }

        $emailParts = explode('@', $pending->email);
        $maskedEmail = substr($emailParts[0], 0, 1).str_repeat('*', max(1, strlen($emailParts[0]) - 1)).'@'.$emailParts[1];

        return view('user::auth.verify-otp', [
            'email' => $maskedEmail,
            'token' => $token,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => ['required', 'string'],
        ]);

        $token = $request->session()->get('pending_registration_token');

        if (! $token) {
            \Illuminate\Support\Facades\Log::info('OTP verification failed', [
                'reason' => 'session_missing',
                'pending_id' => null,
            ]);

            return redirect()->route('register')->with('error', 'Your session expired. Please register again.');
        }

        $pending = PendingRegistration::findByToken($token);

        if (! $pending) {
            $request->session()->forget('pending_registration_token');

            \Illuminate\Support\Facades\Log::info('OTP verification failed', [
                'reason' => 'not_found',
                'pending_id' => null,
            ]);

            return redirect()->route('register')->with('error', 'Registration not found. Please register again.');
        }

        if ($pending->isOtpExpired()) {
            \Illuminate\Support\Facades\Log::info('OTP verification failed', [
                'reason' => 'expired',
                'pending_id' => $pending->id ?? null,
            ]);

            return back()->with('error', 'This code has expired. Please request a new one.');
        }

        $submitted = trim($request->input('otp_code'));

        if ((string) $pending->otp_code !== (string) $submitted) {
            \Illuminate\Support\Facades\Log::info('OTP verification failed', [
                'reason' => 'mismatch',
                'pending_id' => $pending->id ?? null,
            ]);

            return back()->with('error', 'Incorrect code. Please check and try again.');
        }

        $username = User::generateUsername(
            $pending->first_name,
            $pending->last_name
        );

        try {
            $user = $pending->convertToUser($username);
        } catch (RuntimeException $exception) {
            return redirect()->route('register')->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'We could not create your account. Please try again.');
        }

        $request->session()->forget(['pending_registration_token', 'otp_failed_attempts', 'otp_resend_count']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard', absolute: false))
            ->with('success', 'Welcome '.$user->first_name.'! Your account has been created successfully.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $token = $request->session()->get('pending_registration_token');

        if (! $token) {
            return redirect()->route('register')->with('error', 'Session expired. Please register again.');
        }

        $pending = PendingRegistration::findByToken($token);

        if (! $pending) {
            $request->session()->forget('pending_registration_token');

            return redirect()->route('register')->with('error', 'Session expired. Please register again.');
        }

        $resends = $request->session()->get('otp_resend_count', 0);

        if ($resends >= 3) {
            return back()->with('error', 'Maximum OTP resend requests reached. Please try again later.');
        }

        $pending->regenerateOtp();
        $request->session()->put('otp_resend_count', $resends + 1);

        try {
            Mail::to($pending->email)->sendNow(new OtpVerificationMail($pending));
        } catch (Throwable $exception) {
            report($exception);

            if (app()->environment('local')) {
                return back()->with('message', 'New OTP generated. Email delivery failed locally, so use the OTP logged in the terminal or storage/logs/laravel.log.');
            }

            return back()->with('error', 'We could not send a new OTP. Please try again.');
        }

        return back()->with('message', 'New OTP sent to your email.');
    }
}
