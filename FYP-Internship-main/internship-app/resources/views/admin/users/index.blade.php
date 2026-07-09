<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Administration</p>
                <h2 class="mt-1 font-bold text-2xl text-slate-900 leading-tight">{{ __('User Management') }}</h2>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.clearances.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Admin Clearance
                </a>

                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    + Add New User
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 shadow-sm border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 shadow-sm border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6 text-slate-900">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2 xl:grid-cols-[1fr_180px_220px_200px_auto_auto]">
                        <label>
                            <span class="sr-only">Search users</span>
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email, or TP number..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label>
                            <span class="sr-only">Role</span>
                            <select name="role" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All roles</option>
                                <option value="student" @selected(request('role') === 'student')>Students</option>
                                <option value="mentor" @selected(request('role') === 'mentor')>Academic Mentors</option>
                                <option value="supervisor" @selected(request('role') === 'supervisor')>Industrial Supervisors</option>
                                <option value="admin" @selected(request('role') === 'admin')>Admins</option>
                            </select>
                        </label>
                        <label>
                            <span class="sr-only">Academic Mentor</span>
                            <select name="mentor" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Any mentor assignment</option>
                                <option value="unassigned" @selected(request('mentor') === 'unassigned')>Mentor unassigned</option>
                                @foreach($mentors as $mentor)
                                    <option value="{{ $mentor->id }}" @selected((string) request('mentor') === (string) $mentor->id)>{{ $mentor->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="sr-only">Supervisor</span>
                            <select name="supervisor" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Any supervisor assignment</option>
                                <option value="unassigned" @selected(request('supervisor') === 'unassigned')>Supervisor unassigned</option>
                                <option value="assigned" @selected(request('supervisor') === 'assigned')>Supervisor assigned</option>
                            </select>
                        </label>
                        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Filter</button>
                        <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
                    </form>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-sm uppercase tracking-wider text-slate-500">
                                    <th class="py-4 px-6 font-semibold">Name</th>
                                    <th class="py-4 px-6 font-semibold">Email</th>
                                    <th class="py-4 px-6 font-semibold">Role</th>
                                    <th class="py-4 px-6 font-semibold">Assigned Mentor</th>
                                    <th class="py-4 px-6 font-semibold">Registered</th>
                                    <th class="py-4 px-6 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($users as $user)
                                <tr id="user-{{ $user->id }}" class="hover:bg-slate-50 transition-colors group">
                                        <td class="py-4 px-6 font-medium text-slate-900">{{ $user->name }}</td>
                                        <td class="py-4 px-6 text-slate-600">{{ $user->email }}</td>
                                        <td class="py-4 px-6">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                                @if($user->role === 'admin') bg-indigo-100 text-indigo-800
                                                @elseif($user->role === 'mentor') bg-emerald-100 text-emerald-800
                                                @elseif($user->role === 'supervisor') bg-amber-100 text-amber-800
                                                @else bg-slate-100 text-slate-800 @endif
                                            ">
                                                {{ $user->role === 'mentor' ? 'Academic Mentor' : ($user->role === 'supervisor' ? 'Industrial Supervisor' : $user->role) }}
                                            </span>
                                        </td>

                                        <td class="py-4 px-6">
                                            @if($user->role === 'student')
                                                <form action="{{ route('admin.users.assign-mentor', $user) }}" method="POST" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="mentor_id" class="text-sm border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white py-1.5 pl-3 pr-8 min-w-[140px]">
                                                    <option value="">Unassigned</option>
                                                        @foreach($mentors as $mentor)
                                                            <option value="{{ $mentor->id }}" {{ $user->mentor_id == $mentor->id ? 'selected' : '' }}>
                                                                {{ $mentor->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded transition">
                                                        Save
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-slate-400 text-sm italic">-</span>
                                            @endif
                                        </td>

                                        <td class="py-4 px-6 text-slate-500 text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm mr-4">
                                                Edit
                                            </a>
                                            @if ($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm focus:outline-none">
                                                        Remove
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-slate-400 text-sm italic">You</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-500">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($users->hasPages())
                        <div class="mt-6 border-t border-slate-200 pt-4">{{ $users->links() }}</div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
