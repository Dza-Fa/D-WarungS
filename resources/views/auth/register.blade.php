<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Buat Akun Anda</h1>
        <p class="text-gray-600 text-sm mt-1">Bergabung dengan D-WarungS dan mulai pesan makanan lezat</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" class="font-medium text-gray-900" />
            <x-text-input id="name" class="block mt-2 w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-600 text-sm" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="font-medium text-gray-900" />
            <x-text-input id="email" class="block mt-2 w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="anda@contoh.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-sm" />
        </div>

        <!-- reCAPTCHA -->
        <div class="flex justify-center pt-3 mb-0">
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
        </div>
        <x-input-error :messages="$errors->get('recaptcha')" class="mt-2 text-red-600 text-sm" />

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi')" class="font-medium text-gray-900" />
            <x-text-input id="password" class="block mt-2 w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 text-sm" />
            <p class="text-xs text-gray-500 mt-2">Minimal 8 karakter</p>
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" class="font-medium text-gray-900" />
            <x-text-input id="password_confirmation" class="block mt-2 w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-600 text-sm" />
        </div>

        <!-- Terms Agreement -->
        <div class="pt-2">
            <label for="terms" class="inline-flex items-start">
                <input id="terms" type="checkbox" class="mt-1 rounded border-gray-300 text-orange-500 shadow-sm focus:ring-orange-500" name="terms" required>
                <span class="ms-2 text-sm text-gray-600">
                    Saya setuju dengan <a href="#" class="text-orange-600 hover:text-orange-700 font-medium">Ketentuan Layanan</a> dan <a href="#" class="text-orange-600 hover:text-orange-700 font-medium">Kebijakan Privasi</a>
                </span>
            </label>
        </div>

        <!-- Submit Button -->
        <x-button type="submit" variant="primary" size="md" :fullWidth="true" class="mt-6">
            Buat Akun
        </x-button>

        <!-- Login Link -->
        <p class="text-center text-sm text-gray-600 pt-4">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-orange-600 hover:text-orange-700 font-medium">
                Masuk di sini
            </a>
        </p>
    </form>
</x-guest-layout>

