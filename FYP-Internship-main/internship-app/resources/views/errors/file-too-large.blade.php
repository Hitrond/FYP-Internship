<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File too large · InternTrack</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-6 font-sans text-slate-800">
    <main class="w-full max-w-lg rounded-2xl border border-rose-200 bg-white p-8 text-center shadow-xl shadow-slate-900/5">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-2xl font-black text-rose-700" aria-hidden="true">!</span>
        <h1 class="mt-5 text-2xl font-bold text-slate-900">File too large</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">The selected file exceeds the upload limit. Choose a smaller file and try again.</p>
        <p class="mt-3 text-xs text-slate-500">Each uploaded file can be up to 100 MB.</p>
        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
            <a href="{{ url()->previous() }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Go back</a>
            @auth
                <a href="{{ Auth::user()->isStudent() ? route('dashboard') : url('/') }}" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Return to dashboard</a>
            @endauth
        </div>
    </main>
</body>
</html>
