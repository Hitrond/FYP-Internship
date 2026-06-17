@php
    $profile = $user->profile ?? null;
    $username = $profile?->user?->user_name ?? $user->user_name ?? $user->name ?? 'Your Name';
    $email = $profile?->personal_email ?? $user->email ?? 'email@example.com';
    $phone = $profile?->contact_number ?? $user->phone ?? '+60 12-345 6789';
    $date = now()->format('F j, Y');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cover Letter Builder | IMS Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-grid-pattern {
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .a4-preview {
            aspect-ratio: 1 / 1.414;
            background-color: white;
            color: black;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="antialiased bg-slate-950 text-slate-100 min-h-screen flex flex-col bg-grid-pattern">

    <nav class="w-full bg-slate-900/80 backdrop-blur-md border-b border-slate-800 px-6 py-4 flex justify-between items-center z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <span class="text-xl font-bold tracking-tight text-white">Cover Letter Builder</span>
        </div>
        
        <form action="{{ route('student.cover-letter.download') }}" method="GET">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors shadow-lg shadow-indigo-500/30">
                Download PDF
            </button>
        </form>
    </nav>

    <main class="flex-grow p-6 grid grid-cols-1 lg:grid-cols-12 gap-8 max-w-[1600px] mx-auto w-full">
        
        <div class="lg:col-span-4 space-y-6 flex flex-col h-full">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex-grow">
                <h2 class="text-lg font-bold text-white mb-6 border-b border-slate-800 pb-3">Letter Details</h2>
                
                <form id="cover-letter-form" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Company Name</label>
                        <input id="input-company" type="text" placeholder="e.g. TechCorp Malaysia" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Hiring Manager (Optional)</label>
                        <input id="input-manager" type="text" placeholder="e.g. Mr. Ahmad" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Internship Role</label>
                        <input id="input-role" type="text" placeholder="e.g. Software Engineering Intern" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all">
                    </div>

                    <div class="pt-2">
                        <label class="block text-sm font-medium text-slate-400 mb-1">Letter Body</label>
                        <textarea id="input-body" rows="10" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all resize-none font-mono text-sm" placeholder="Write your cover letter content here..."></textarea>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8 flex justify-center items-start overflow-y-auto pb-10">
            <div class="a4-preview w-full max-w-[800px] p-12 sm:p-16 flex flex-col rounded-sm">
                
                <div class="border-b-2 border-slate-200 pb-6 mb-8">
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $username }}</h1>
                    <div class="text-sm text-slate-600 mt-2 flex gap-4">
                        <span>{{ $email }}</span>
                        <span>|</span>
                        <span>{{ $phone }}</span>
                    </div>
                </div>

                <div class="text-sm text-slate-800 space-y-4 mb-8">
                    <p>{{ $date }}</p>
                    <div>
                        <p class="font-semibold" id="preview-manager-top">[Hiring Manager Name]</p>
                        <p id="preview-company">[Company Name]</p>
                        <p>Malaysia</p>
                    </div>
                </div>

                <div class="text-sm text-slate-800 leading-relaxed flex-grow">
                    <p class="mb-4">Dear <span id="preview-manager-salutation">[Hiring Manager Name]</span>,</p>
                    
                    <div id="preview-body" class="mb-4 text-slate-400 italic whitespace-pre-wrap">Your cover letter content will appear here as you type. This is a live preview of how your final PDF will look.</div>

                    <p class="mt-8">Sincerely,</p>
                    <p class="mt-8 font-bold">{{ $username }}</p>
                </div>
                
            </div>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = {
                company: document.getElementById('input-company'),
                manager: document.getElementById('input-manager'),
                body: document.getElementById('input-body')
            };

            const previews = {
                company: document.getElementById('preview-company'),
                managerTop: document.getElementById('preview-manager-top'),
                managerSalutation: document.getElementById('preview-manager-salutation'),
                body: document.getElementById('preview-body')
            };

            // Link Company Name
            inputs.company.addEventListener('input', (e) => {
                previews.company.innerText = e.target.value || '[Company Name]';
            });

            // Link Manager Name
            inputs.manager.addEventListener('input', (e) => {
                const val = e.target.value || '[Hiring Manager Name]';
                previews.managerTop.innerText = val;
                previews.managerSalutation.innerText = val;
            });

            // Link Letter Body
            inputs.body.addEventListener('input', (e) => {
                if (e.target.value.trim() !== '') {
                    previews.body.className = 'mb-4 text-slate-800 whitespace-pre-wrap';
                    previews.body.innerText = e.target.value;
                } else {
                    previews.body.className = 'mb-4 text-slate-400 italic whitespace-pre-wrap';
                    previews.body.innerText = 'Your cover letter content will appear here as you type. This is a live preview of how your final PDF will look.';
                }
            });
        });
    </script>
</body>
</html>