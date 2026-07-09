<x-guest-layout>
    <div class="mb-7">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-indigo-600">Secure account recovery</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Choose a new password</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Use a strong password that you do not use for another account.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" x-data="{ showPassword: false }">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <label class="block">
            <span class="mb-2 block text-sm font-bold text-slate-700">Email address</span>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-bold text-slate-700">New password</span>
            <div class="relative">
                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password" class="w-full rounded-xl border-slate-300 px-4 py-3 pr-16 focus:border-indigo-500 focus:ring-indigo-500">
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-4 text-xs font-bold text-slate-500" x-text="showPassword ? 'Hide' : 'Show'"></button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-bold text-slate-700">Confirm new password</span>
            <input id="password_confirmation" :type="showPassword ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-xl border-slate-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </label>

        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">Update password</button>
    </form>
</x-guest-layout>
