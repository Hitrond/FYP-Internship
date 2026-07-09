<x-guest-layout>
    <div class="mb-7">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Welcome back</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Sign in to your portal</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Use the account issued by your university or organisation.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
        @csrf

        <label class="block">
            <span class="mb-2 block text-sm font-bold text-slate-700">Email address</span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com" class="w-full rounded-xl border-slate-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </label>

        <label class="block">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-sm font-bold text-slate-700">Password</span>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Forgot password?</a>
                @endif
            </div>
            <div class="relative">
                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" class="w-full rounded-xl border-slate-300 px-4 py-3 pr-16 focus:border-indigo-500 focus:ring-indigo-500">
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-4 text-xs font-bold text-slate-500 hover:text-indigo-700" x-text="showPassword ? 'Hide' : 'Show'"></button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </label>

        <label for="remember_me" class="flex items-center gap-3 text-sm text-slate-600">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            Keep me signed in on this device
        </label>

        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:-translate-y-0.5 hover:bg-indigo-700">Sign in</button>

        <a href="{{ url('/') }}" class="block text-center text-sm font-semibold text-slate-500 hover:text-slate-800">&larr; Back to homepage</a>
    </form>
</x-guest-layout>
