<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">User management</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900">Edit {{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-10">
        <div class="mx-auto max-w-2xl px-4 sm:px-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                @csrf
                @method('PUT')

                <div class="grid gap-6 sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">Full name</span>
                        <input name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="sm:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">Email address</span>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="sm:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">Role</span>
                        <select name="role" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (['student' => 'Student', 'mentor' => 'Academic Mentor', 'supervisor' => 'Industrial Supervisor', 'admin' => 'Administrator'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">New password</span>
                        <input type="password" name="password" autocomplete="new-password" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Confirm password</span>
                        <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                </div>

                <p class="mt-3 text-xs text-slate-500">Leave both password fields empty to keep the current password.</p>

                <div class="mt-8 flex justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
