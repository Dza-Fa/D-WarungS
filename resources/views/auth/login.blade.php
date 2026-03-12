<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Selamat Datang!</h1>
        <p class="text-gray-600 text-sm mt-1">Masuk ke akun Anda untuk melanjutkan</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="font-medium text-gray-900" />
            <x-text-input id="email" class="block mt-2 w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="anda@contoh.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-sm" />
        </div>
        @if(config('services.recaptcha'))
        <div class="flex justify-center pt-3 mb-0">
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
        </div>
        @endif
        <x-input-error :messages="$errors->get('recaptcha')" class="mt-2" />

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi')" class="font-medium text-gray-900" />
            <x-text-input id="password" class="block mt-2 w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 text-sm" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-orange-500 shadow-sm focus:ring-orange-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
            
            @if (Route::has('password.request'))
                <a class="text-sm text-orange-600 hover:text-orange-700 font-medium" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <x-button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium py-2.5 rounded-lg transition-colors">
            Masuk
        </x-button>

        <!-- Sign Up Link -->
        <p class="text-center text-sm text-gray-600 mt-6">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-orange-600 hover:text-orange-700 font-medium">
                Daftar di sini
            </a>
        </p>
    </form>
</x-guest-layout>

