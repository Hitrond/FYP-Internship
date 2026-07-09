<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Workflow updates</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Notifications</h2>
                <p class="mt-1 text-sm text-slate-500">Deadline, review, attendance, and extension alerts.</p>
            </div>
            @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Mark all as read</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-4xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="divide-y divide-slate-100">
                    @forelse ($notifications as $notification)
                        @php
                            $level = $notification->data['level'] ?? 'info';
                            $colour = match ($level) {
                                'danger' => 'bg-rose-100 text-rose-700',
                                'warning' => 'bg-amber-100 text-amber-700',
                                'success' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-indigo-100 text-indigo-700',
                            };
                        @endphp
                        <div class="flex gap-4 p-5 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50/40' }}">
                            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $colour }}">
                                @if ($level === 'danger')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.8L2.6 17a2 2 0 001.7 3h15.4a2 2 0 001.7-3L13.7 3.8a2 2 0 00-3.4 0z"/></svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-5-5.9V4a1 1 0 10-2 0v1.1A6 6 0 006 11v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 01-6 0h6z"/></svg>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                    <h3 class="font-bold text-slate-900">{{ $notification->data['title'] ?? 'Workflow notification' }}</h3>
                                    <span class="shrink-0 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->data['message'] ?? '' }}</p>
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="mt-3">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-sm font-bold text-indigo-600 hover:text-indigo-800">{{ $notification->read_at ? 'Open' : 'Read and open' }} &rarr;</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-16 text-center">
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 01-6 0h6z"/></svg>
                            </span>
                            <h3 class="mt-4 font-bold text-slate-900">No notifications yet</h3>
                            <p class="mt-1 text-sm text-slate-500">Important internship workflow updates will appear here.</p>
                        </div>
                    @endforelse
                </div>
                @if ($notifications->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $notifications->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
