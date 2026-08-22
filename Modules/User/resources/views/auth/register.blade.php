<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create account - HajurKoAfno</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Hide the default password reveal icon in Edge/IE to prevent double eye icons */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
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
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-red-600 tracking-wider uppercase mb-1">Account</h3>
                <h1 class="text-2xl sm:text-[28px] font-bold text-gray-900 leading-tight">Create account</h1>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6" novalidate>
                @csrf

                @if(filled($redirectTo ?? ''))
                    <input type="hidden" name="redirect" value="{{ $redirectTo }}">
                @endif

                <!-- Social buttons -->
                <div class="space-y-3">
                    <a href="#" class="w-full flex items-center justify-center gap-3 h-12 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-200" aria-label="Continue with Google">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="font-medium text-gray-700">Continue with Google</span>
                    </a>
                    <a href="#" class="w-full flex items-center justify-center gap-3 h-12 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-200" aria-label="Continue with Facebook">
                        <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span class="font-medium text-gray-700">Continue with Facebook</span>
                    </a>
                </div>

                <!-- Divider -->
                <div class="relative flex items-center">
                    <div class="flex-grow border-t border-gray-200"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-400 text-sm">or</span>
                    <div class="flex-grow border-t border-gray-200"></div>
                </div>

                <!-- Name fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First name</label>
                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" autocomplete="given-name" required placeholder="First name" 
                            class="block w-full h-12 px-4 py-3 text-base text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-shadow">
                        @error('first_name')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last name</label>
                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" autocomplete="family-name" required placeholder="Last name" 
                            class="block w-full h-12 px-4 py-3 text-base text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-shadow">
                        @error('last_name')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Phone and DOB -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <div class="flex relative rounded-lg border border-gray-300 bg-white focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 overflow-hidden transition-shadow">
                            <span class="inline-flex items-center px-3 border-r border-gray-300 bg-gray-50 text-gray-600 text-sm font-medium">
                                <span class="mr-1.5 text-base">🇳🇵</span> +977
                            </span>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="98XXXXXXXX" maxlength="10" required 
                                class="flex-1 min-w-0 block w-full px-3 py-3 h-full text-base text-gray-900 bg-transparent border-0 focus:outline-none focus:ring-0 placeholder-gray-400">
                        </div>
                        @error('phone')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="dob" class="block text-sm font-medium text-gray-700 mb-1">DOB</label>
                        <input id="dob" name="dob" type="date" value="{{ old('dob') }}" required 
                            class="block w-full h-12 px-4 py-3 text-base text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-shadow">
                        @error('dob')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                    <div class="flex gap-3">
                        <label class="flex-1 text-center border border-gray-300 rounded-lg p-3 cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 hover:bg-gray-50 transition-colors">
                            <input type="radio" name="gender" value="male" class="sr-only" {{ old('gender') == 'male' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-900">Male</span>
                        </label>
                        <label class="flex-1 text-center border border-gray-300 rounded-lg p-3 cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 hover:bg-gray-50 transition-colors">
                            <input type="radio" name="gender" value="female" class="sr-only" {{ old('gender') == 'female' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-900">Female</span>
                        </label>
                        <label class="flex-1 text-center border border-gray-300 rounded-lg p-3 cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 hover:bg-gray-50 transition-colors">
                            <input type="radio" name="gender" value="other" class="sr-only" {{ old('gender') == 'other' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-900">Other</span>
                        </label>
                    </div>
                    @error('gender')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                </div>

                <!-- Location dropdowns -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <div class="relative">
                            <select id="country" name="country" required class="block w-full h-12 pl-4 pr-10 py-3 text-base text-gray-900 bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                                <option value="Nepal" {{ old('country')=='Nepal' ? 'selected' : '' }}>Nepal</option>
                                <option value="India" {{ old('country')=='India' ? 'selected' : '' }}>India</option>
                                <option value="Other" {{ old('country')=='Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('country')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="district" class="block text-sm font-medium text-gray-700 mb-1">District</label>
                        <div class="relative">
                            <select id="district" name="district" required class="block w-full h-12 pl-4 pr-10 py-3 text-base text-gray-900 bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                                <option value="" disabled {{ old('district') ? '' : 'selected' }}>District</option>
                                <option value="Kathmandu" {{ old('district')=='Kathmandu' ? 'selected' : '' }}>Kathmandu</option>
                                <option value="Lalitpur" {{ old('district')=='Lalitpur' ? 'selected' : '' }}>Lalitpur</option>
                                <option value="Bhaktapur" {{ old('district')=='Bhaktapur' ? 'selected' : '' }}>Bhaktapur</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('district')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <div class="relative">
                            <select id="city" name="city" required class="block w-full h-12 pl-4 pr-10 py-3 text-base text-gray-900 bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                                <option value="" disabled {{ old('city') ? '' : 'selected' }}>City</option>
                                <option value="Kathmandu" {{ old('city')=='Kathmandu' ? 'selected' : '' }}>Kathmandu</option>
                                <option value="Patan" {{ old('city')=='Patan' ? 'selected' : '' }}>Patan</option>
                                <option value="Dhapakhel" {{ old('city')=='Dhapakhel' ? 'selected' : '' }}>Dhapakhel</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('city')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required placeholder="Enter your email" 
                        class="block w-full h-12 px-4 py-3 text-base text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-shadow">
                    @error('email')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required placeholder="Create a password" autocomplete="new-password" 
                            class="block w-full h-12 px-4 py-3 pr-12 text-base text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition-shadow">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors" aria-label="Toggle password visibility">
                            <!-- Eye Icon -->
                            <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <!-- Eye Slash Icon (Hidden by default) -->
                            <svg id="eye-slash-icon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a17.6 17.6 0 012.815-3.513M6.22 6.223A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror
                </div>

                <!-- Terms -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" name="terms" type="checkbox" value="1" @checked(old('terms')) required 
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-2 text-sm">
                        <label for="terms" class="text-gray-700">I accept the <a href="#" class="text-[#1e1b4b] font-semibold hover:underline">Terms and Conditions</a></label>
                    </div>
                </div>
                @error('terms')<p class="mt-1 text-red-600 text-sm">{{ $message }}</p>@enderror

                <button type="submit" class="w-full h-12 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors mt-6">
                    Create account
                </button>
            </form>
            
            <div class="mt-4 text-center text-sm text-gray-600">
                Already have an account? 
                <a href="{{ route('login', array_filter(['redirect' => $redirectTo ?? ''])) }}" class="font-bold text-red-600 hover:text-red-700 transition-colors">Sign in</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500 flex flex-wrap items-center justify-center gap-x-2 gap-y-1">
            <span>© 2024 HajurKoAfno. All rights reserved.</span>
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

    <!-- Minimal JavaScript for Password Toggle -->
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeSlashIcon = document.getElementById('eye-slash-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
