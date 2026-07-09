<x-guest-layout>
    <div class="mb-7">
        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm2-10V7a4 4 0 118 0v4"/></svg>
        </span>
        <h1 class="mt-5 text-3xl font-bold tracking-tight text-slate-950">Reset your password</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Enter your registered email address. We will send a secure, single-use reset link.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <label class="block">
            <span class="mb-2 block text-sm font-bold text-slate-700">Email address</span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="name@example.com" class="w-full rounded-xl border-slate-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </label>

        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">Send reset link</button>
        <a href="{{ route('login') }}" class="block text-center text-sm font-semibold text-slate-500 hover:text-slate-800">&larr; Back to sign in</a>
    </form>
</x-guest-layout>
