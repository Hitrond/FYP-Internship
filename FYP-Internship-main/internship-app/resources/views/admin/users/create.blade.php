<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">User management</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Add a new user</h2>
            <p class="mt-1 text-sm text-slate-500">Create controlled access for a system participant.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-10">
        <div class="mx-auto max-w-2xl px-4 sm:px-6">
            <form method="POST" action="{{ route('admin.users.store') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @csrf

                <div class="grid gap-6 p-7 sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">Full name</span>
                        <input name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="sm:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">Email address</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="sm:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">System role</span>
                        <select name="role" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="student" @selected(old('role') === 'student')>Student</option>
                            <option value="mentor" @selected(old('role') === 'mentor')>Academic Mentor</option>
                            <option value="supervisor" @selected(old('role') === 'supervisor')>Industrial Supervisor</option>
                            <option value="admin" @selected(old('role') === 'admin')>Administrator</option>
                        </select>
                        @error('role')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Temporary password</span>
                        <input type="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Confirm password</span>
                        <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </label>

                    <div class="sm:col-span-2 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm leading-6 text-blue-800">
                        Academic Mentors are assigned from the semester cohort screen. Industrial Supervisor accounts are provisioned by admin after Academic Mentor placement approval.
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-7 py-5">
                    <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Create user</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
