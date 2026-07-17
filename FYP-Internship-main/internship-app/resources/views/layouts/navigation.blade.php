@php
    $user = Auth::user();
    $homeRoute = $user->isAdmin()
        ? route('admin.dashboard')
        : ($user->isSupervisor()
            ? route('supervisor.dashboard')
            : ($user->isMentor() ? route('mentor.dashboard') : route('dashboard')));
    $roleLabel = match ($user->role) {
        'admin' => 'Administrator',
        'mentor' => 'Academic Mentor',
        'supervisor' => 'Industrial Supervisor',
        default => 'Student',
    };
    $initials = collect(explode(' ', $user->name))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $unreadNotificationCount = $user->unreadNotifications()->count();
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur-xl">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-[72px] items-center justify-between gap-5">
            <div class="flex min-w-0 items-center gap-7">
                <a href="{{ $homeRoute }}" class="flex shrink-0 items-center gap-3" aria-label="InternTrack dashboard">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#17233f] shadow-lg shadow-slate-900/10">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-9 4h14M5 7h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                        </svg>
                    </span>
                    <span class="hidden sm:block">
                        <span class="block text-base font-bold tracking-tight text-slate-900">InternTrack</span>
                        <span class="block text-[9px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $roleLabel }}</span>
                    </span>
                </a>

                <div class="hidden items-center gap-1 xl:flex">
                    @if($user->isAdmin())
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-nav-link>
                        <x-nav-link :href="route('admin.semesters.index')" :active="request()->routeIs('admin.semesters.*')">Semesters</x-nav-link>
                        <x-nav-link :href="route('admin.evaluation-forms.index')" :active="request()->routeIs('admin.evaluation-forms.*')">Evaluation Forms</x-nav-link>
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Users</x-nav-link>
                        <x-nav-link :href="route('admin.clearances.index')" :active="request()->routeIs('admin.clearances.*')">Placement & Accounts</x-nav-link>
                    @elseif($user->isSupervisor())
                        <x-nav-link :href="route('supervisor.dashboard')" :active="request()->routeIs('supervisor.dashboard')">Dashboard</x-nav-link>
                        <x-nav-link :href="route('supervisor.logbooks.index')" :active="request()->routeIs('supervisor.logbooks.*')">Logbooks</x-nav-link>
                        <x-nav-link :href="route('supervisor.evaluations.index')" :active="request()->routeIs('supervisor.evaluations.*')">Evaluations</x-nav-link>
                        <x-nav-link :href="route('supervisor.final-clearances.index')" :active="request()->routeIs('supervisor.final-clearances.*')">Final Clearance</x-nav-link>
                    @elseif($user->isMentor())
                        <x-nav-link :href="route('mentor.dashboard')" :active="request()->routeIs('mentor.dashboard')">Dashboard</x-nav-link>
                        <x-nav-link :href="route('mentor.clearances.index')" :active="request()->routeIs('mentor.clearances.*')">Placement Approvals</x-nav-link>
                        <x-nav-link :href="route('mentor.logbooks.index')" :active="request()->routeIs('mentor.logbooks.*')">Student Logbooks</x-nav-link>
                        <x-nav-link :href="route('mentor.evaluations.index')" :active="request()->routeIs('mentor.evaluations.*')">Supervisor Evaluations</x-nav-link>
                        <x-nav-link :href="route('mentor.final-clearances.index')" :active="request()->routeIs('mentor.final-clearances.*')">Completion Approvals</x-nav-link>
                    @elseif($user->isStudent())
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                        <x-nav-link :href="route('student.companies.index')" :active="request()->routeIs('student.companies.*', 'student.company-tracker.*')">Companies</x-nav-link>
                        <x-nav-link :href="route('student.resume.builder')" :active="request()->routeIs('student.resume.*')">Resume</x-nav-link>
                        <x-nav-link :href="route('student.cover-letter.create')" :active="request()->routeIs('student.cover-letter.*')">Cover Letter</x-nav-link>
                        <x-nav-link :href="route('student.logbook.index')" :active="request()->routeIs('student.logbook.*')">Logbooks</x-nav-link>
                        <x-nav-link :href="route('student.clearance.create')" :active="request()->routeIs('student.clearance.*')">Placement</x-nav-link>
                        <x-nav-link :href="route('student.final-clearance.create')" :active="request()->routeIs('student.final-clearance.*')">Final Clearance</x-nav-link>
                        <x-nav-link :href="route('student.evaluations.index')" :active="request()->routeIs('student.evaluations.*')">Evaluations</x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden shrink-0 items-center gap-3 xl:flex">
                <a href="{{ route('notifications.index') }}" class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700" aria-label="Notifications">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 01-6 0h6z"/></svg>
                    @if ($unreadNotificationCount > 0)
                        <span class="absolute -right-1 -top-1 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white">{{ min(99, $unreadNotificationCount) }}</span>
                    @endif
                </a>
                <x-dropdown align="right" width="64" contentClasses="py-2 bg-white">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white py-2 pl-2 pr-3 text-left transition hover:border-indigo-200 hover:bg-indigo-50/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-xs font-bold text-indigo-700">{{ $initials }}</span>
                            <span class="max-w-[150px]">
                                <span class="block truncate text-sm font-bold text-slate-800">{{ $user->name }}</span>
                                <span class="block truncate text-[11px] text-slate-500">{{ $roleLabel }}</span>
                            </span>
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-slate-100 px-4 pb-3 pt-1">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $user->name }}</p>
                            <p class="mt-1 truncate text-xs text-slate-500">{{ $user->email }}</p>
                        </div>
                        <div class="py-1">
                            <x-dropdown-link :href="route('profile.edit')">Account settings</x-dropdown-link>
                            @if($user->isStudent())
                                <x-dropdown-link :href="route('student.profile.edit')">Student profile</x-dropdown-link>
                            @elseif($user->isSupervisor())
                                <x-dropdown-link :href="route('supervisor.profile.edit')">Supervisor profile</x-dropdown-link>
                            @elseif($user->isMentor())
                                <x-dropdown-link :href="route('mentor.profile.edit')">Academic Mentor profile</x-dropdown-link>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 pt-1">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log out</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center gap-2 xl:hidden">
                <a href="{{ route('notifications.index') }}" class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-slate-600" aria-label="Notifications">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 01-6 0h6z"/></svg>
                    @if ($unreadNotificationCount > 0)
                        <span class="absolute -right-1 -top-1 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white">{{ min(99, $unreadNotificationCount) }}</span>
                    @endif
                </a>
                <button @click="open = ! open" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50" :aria-expanded="open.toString()" aria-label="Toggle navigation">
                    <svg x-show="!open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="open" style="display: none" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-transition class="border-t border-slate-200 bg-white xl:hidden" style="display: none">
        <div class="mx-auto max-w-7xl space-y-1 px-4 py-4 sm:px-6 lg:px-8">
            @if($user->isAdmin())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.semesters.index')" :active="request()->routeIs('admin.semesters.*')">Semesters</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.evaluation-forms.index')" :active="request()->routeIs('admin.evaluation-forms.*')">Evaluation Forms</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Users</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.clearances.index')" :active="request()->routeIs('admin.clearances.*')">Placement & Supervisor Accounts</x-responsive-nav-link>
            @elseif($user->isSupervisor())
                <x-responsive-nav-link :href="route('supervisor.dashboard')" :active="request()->routeIs('supervisor.dashboard')">Dashboard</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('supervisor.logbooks.index')" :active="request()->routeIs('supervisor.logbooks.*')">Logbooks</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('supervisor.evaluations.index')" :active="request()->routeIs('supervisor.evaluations.*')">Evaluations</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('supervisor.final-clearances.index')" :active="request()->routeIs('supervisor.final-clearances.*')">Final Clearance</x-responsive-nav-link>
            @elseif($user->isMentor())
                <x-responsive-nav-link :href="route('mentor.dashboard')" :active="request()->routeIs('mentor.dashboard')">Dashboard</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mentor.clearances.index')" :active="request()->routeIs('mentor.clearances.*')">Placement Approvals</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mentor.logbooks.index')" :active="request()->routeIs('mentor.logbooks.*')">Student Logbooks</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mentor.evaluations.index')" :active="request()->routeIs('mentor.evaluations.*')">Supervisor Evaluations</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mentor.final-clearances.index')" :active="request()->routeIs('mentor.final-clearances.*')">Completion Approvals</x-responsive-nav-link>
            @elseif($user->isStudent())
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.companies.index')" :active="request()->routeIs('student.companies.*', 'student.company-tracker.*')">Companies</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.resume.builder')" :active="request()->routeIs('student.resume.*')">Resume Builder</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.cover-letter.create')" :active="request()->routeIs('student.cover-letter.*')">Cover Letter</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.logbook.index')" :active="request()->routeIs('student.logbook.*')">Logbooks</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.clearance.create')" :active="request()->routeIs('student.clearance.*')">Placement Submission</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.final-clearance.create')" :active="request()->routeIs('student.final-clearance.*')">Final Clearance</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.evaluations.index')" :active="request()->routeIs('student.evaluations.*')">Evaluations</x-responsive-nav-link>
            @endif
        </div>

        <div class="border-t border-slate-200 bg-slate-50 px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-xs font-bold text-indigo-700">{{ $initials }}</span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-slate-900">{{ $user->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                </div>
            </div>
            <div class="mt-3 grid gap-1 sm:grid-cols-2">
                <x-responsive-nav-link :href="route('profile.edit')">Account settings</x-responsive-nav-link>
                @if($user->isStudent())
                    <x-responsive-nav-link :href="route('student.profile.edit')">Student profile</x-responsive-nav-link>
                @elseif($user->isSupervisor())
                    <x-responsive-nav-link :href="route('supervisor.profile.edit')">Supervisor profile</x-responsive-nav-link>
                @elseif($user->isMentor())
                    <x-responsive-nav-link :href="route('mentor.profile.edit')">Academic Mentor profile</x-responsive-nav-link>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log out</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
