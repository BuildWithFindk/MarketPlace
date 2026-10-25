<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email - HajurKoAfno</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F3F4F6] min-h-screen flex flex-col items-center justify-center py-10 px-4 sm:px-6 lg:px-8 font-sans text-gray-900 antialiased">
    
    <div class="w-full max-w-[500px]">
        
        <!-- Brand Logo -->
        <div class="mb-8 flex items-center justify-center gap-2 font-bold text-2xl text-[#1e1b4b]">
            <img src="{{ asset('images/logo.png') }}" alt="HajurKoAfno" class="h-8 w-auto object-contain">
            <span>HajurKoAfno</span>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 sm:p-8">
            
            <!-- Headers -->
            <div class="mb-6 text-center">
                <h3 class="text-xs font-semibold text-red-600 tracking-wider uppercase mb-1">Security</h3>
                <h1 class="text-2xl sm:text-[28px] font-bold text-gray-900 leading-tight">Verify your email</h1>
                <p class="mt-2 text-sm text-gray-600">
                    We sent a 6-digit code to {{ $email ?? 'your email address' }}.
                </p>
            </div>

            <!-- Session Messages -->
            @if(session('message'))
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md">
                    <p class="text-sm text-blue-700">{{ session('message') }}</p>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('otp.verify') }}" class="space-y-6" id="otp-form" novalidate>
                @csrf

                <!-- OTP Input -->
                <div>
                    <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-1">Enter 6-digit code</label>
                    <input id="otp_code" name="otp_code" type="text" inputmode="numeric" pattern="[0-9]*" value="{{ old('otp_code') }}" required autofocus placeholder="------" 
                        class="block w-full h-14 px-4 py-3 text-center text-3xl tracking-[0.4em] font-mono text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-300 transition-shadow">
                    @error('otp_code')<p class="mt-2 text-center text-red-600 text-sm">{{ $message }}</p>@enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg h-12 items-center text-base font-semibold text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        Verify Account
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('otp.resend') }}" id="resend-form">
                    @csrf
                    <button type="submit" id="resend-button" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                        Didn't receive code? Resend
                    </button>
                    <p id="resend-timer" class="text-xs text-gray-500 mt-1 hidden">You can resend in <span id="timer-count">60</span>s</p>
                </form>
            </div>
            
            <div class="mt-8 text-center text-sm text-gray-600">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="font-bold text-gray-500 hover:text-gray-800 transition-colors">
                        &larr; Logout and use another account
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500 flex flex-wrap items-center justify-center gap-x-2 gap-y-1">
            <span>© 2026 HajurKoAfno. All rights reserved.</span>
            <span class="hidden sm:inline">·</span>
            <div class="flex items-center gap-2">
                <a href="#" class="hover:text-gray-900 transition-colors">Privacy Policy</a>
                <span>·</span>
                <a href="#" class="hover:text-gray-900 transition-colors">Terms of Service</a>
                <span>·</span>
                <a href="#" class="hover:text-gray-900 transition-colors">Help Center</a>
            </div>
        </div>
    </div>

    <!-- JavaScript for Auto-submit and Timer -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto submit when 6 digits are entered
            const otpInput = document.getElementById('otp_code');
            const otpForm = document.getElementById('otp-form');
            
            otpInput.addEventListener('input', function(e) {
                // Ensure only numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length === 6) {
                    otpForm.submit();
                }
            });

            // Resend timer logic
            const resendButton = document.getElementById('resend-button');
            const resendTimer = document.getElementById('resend-timer');
            const timerCount = document.getElementById('timer-count');
            
            // Check if we just sent an OTP
            const justSent = {{ session('message') ? 'true' : 'false' }};
            
            if (justSent) {
                startTimer();
            }

            document.getElementById('resend-form').addEventListener('submit', function(e) {
                if(resendButton.disabled) {
                    e.preventDefault();
                    return false;
                }
                resendButton.disabled = true;
                resendButton.innerHTML = 'Sending...';
            });

            function startTimer() {
                resendButton.disabled = true;
                resendTimer.classList.remove('hidden');
                
                let timeLeft = 60;
                
                const timer = setInterval(() => {
                    timeLeft--;
                    timerCount.innerText = timeLeft;
                    
                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        resendButton.disabled = false;
                        resendTimer.classList.add('hidden');
                        resendButton.innerHTML = "Didn't receive code? Resend";
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>
