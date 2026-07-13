<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Document studio</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Cover Letter Builder') }}
                </h2>
                <p class="text-sm text-slate-500">Draft, preview, and export your tailored cover letter.</p>
            </div>

            @if ($readiness['complete'])
                <div class="flex flex-wrap gap-3">
                    <form class="download-cover-letter" action="{{ route('student.cover-letter.download') }}" method="GET">
                        <button type="submit" class="flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download PDF
                        </button>
                    </form>
                    <form class="download-cover-letter" action="{{ route('student.cover-letter.download-doc') }}" method="GET">
                        <button type="submit" class="flex items-center gap-2 rounded-xl bg-white px-6 py-2.5 text-sm font-bold text-indigo-700 ring-1 ring-indigo-200 transition hover:bg-indigo-50">
                            Download DOC
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('student.profile.edit') }}#document-profile" class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-700/15 hover:bg-amber-600">
                    Complete Profile First
                </a>
            @endif
        </div>
    </x-slot>

    @php
        // Securely fetch the logged-in user and their profile
        $user = Auth::user();
        $profile = $user->profile;
        
        // Prioritize Profile Data > User Table Data > Fallback Defaults
        $username = $user->name ?? 'Your Name';
        $email = $profile?->personal_email ?: ($user->email ?? 'email@example.com');
        $phone = $profile?->contact_number ?: 'Contact number required';
        $date = now()->format('F j, Y');

        // Fetch skills from profile if they exist
        $skills = $user->skills && $user->skills->count() > 0
            ? $user->skills->pluck('name')->implode(', ')
            : 'Add skills in your Student Profile';
    @endphp

    <div class="bg-slate-50 py-6">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('document-error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">{{ session('document-error') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif

            @include('student.documents.partials.profile-readiness', ['readiness' => $readiness])

            @include('student.documents.partials.library', [
                'documentType' => 'cover_letter',
                'documentTypeLabel' => 'Cover Letter',
                'uploadRoute' => route('student.cover-letter.upload'),
                'documents' => $documents,
            ])
        </div>
    </div>

    <div class="bg-slate-950 min-h-screen text-slate-100 py-8">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-4 space-y-6 flex flex-col">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-lg font-bold text-white mb-6 border-b border-slate-800 pb-3">Letter Details</h2>

                    <form id="cover-letter-form" action="{{ route('student.cover-letter.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Company Name</label>
                            <input id="input-company" name="company_name" type="text" value="{{ old('company_name', $draft?->company_name) }}" placeholder="e.g. TechCorp Malaysia" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-1 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Hiring Manager (Optional)</label>
                            <input id="input-manager" name="hiring_manager" type="text" value="{{ old('hiring_manager', $draft?->hiring_manager) }}" placeholder="e.g. Mr. Ahmad" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-1 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Internship Role</label>
                            <input id="input-role" name="role" type="text" value="{{ old('role', $draft?->role) }}" placeholder="e.g. Software Engineering Intern" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-1 outline-none transition-all">
                        </div>

                        <div class="pt-2 mb-4 border-t border-slate-800 mt-4">
                            <label class="block text-sm font-medium text-indigo-400 mb-1 mt-4">Load APU Degree Template</label>
                            <select id="template-selector" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:border-indigo-500 focus:ring-1 outline-none transition-all">
                                <option value="" data-domain="">-- Start from scratch --</option>
                                <optgroup label="Computing, Technology & Games">
                                    <option value="Bachelor of Science (Honours) in Software Engineering" data-domain="tech">BSc (Hons) in Software Engineering</option>
                                    <option value="Bachelor of Science (Honours) in Computer Science" data-domain="tech">BSc (Hons) in Computer Science</option>
                                    <option value="Bachelor of Science (Honours) in Computer Science (Cyber Security)" data-domain="tech">BSc (Hons) in Computer Security</option>
                                    <option value="Diploma in Information & Communication Technology" data-domain="tech">Diploma in ICT</option>
                                </optgroup>
                                <optgroup label="Accounting, Banking & Business">
                                    <option value="Bachelor in Accounting and Finance (Honours)" data-domain="finance">BA in Accounting and Finance (Hons)</option>
                                    <option value="Bachelor of Arts (Honours) in Business Management" data-domain="business">BA (Hons) in Business Management</option>
                                    <option value="Diploma in Business Administration" data-domain="business">Diploma in Business Administration</option>
                                </optgroup>
                                <optgroup label="Engineering & Design">
                                    <option value="Bachelor of Mechatronic Engineering with Honours" data-domain="engineering">Bachelor of Mechatronic Engineering</option>
                                    <option value="Bachelor of Arts (Honours) in Industrial Design" data-domain="creative">BA (Hons) in Industrial Design</option>
                                </optgroup>
                            </select>
                            <p class="text-xs text-slate-500 mt-2">Selecting a template will auto-fill the letter with your saved skills.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Letter Body</label>
                            <textarea id="input-body" name="body_text" rows="12" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 outline-none transition-all resize-y font-mono text-sm" placeholder="Write your cover letter content here...">{{ old('body_text', $draft?->body_text) }}</textarea>
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">
                            Save Draft
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-8 flex justify-center items-start overflow-y-auto pb-10">
                <div class="aspect-[1/1.414] w-full max-w-[800px] rounded-sm bg-white p-5 text-black shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] sm:p-10 lg:p-16 flex flex-col">

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
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = {
                company: document.getElementById('input-company'),
                manager: document.getElementById('input-manager'),
                role: document.getElementById('input-role'),
                body: document.getElementById('input-body')
            };

            const previews = {
                company: document.getElementById('preview-company'),
                managerTop: document.getElementById('preview-manager-top'),
                managerSalutation: document.getElementById('preview-manager-salutation'),
                body: document.getElementById('preview-body')
            };

            // Inject PHP data directly into JS Variables
            const studentInfo = {
                university: "Asia Pacific University (APU)",
                skills: "{!! addslashes($skills) !!}",
                phone: "{{ $phone }}",
                email: "{{ $email }}"
            };

            const domainTemplates = {
                tech: (degree, company, role) => `I am writing to express my keen interest in the ${role} position at ${company}. As a student pursuing my ${degree} at ${studentInfo.university}, I am passionate about digital innovation and modern technology infrastructure.

Throughout my academic journey, I have gained practical experience utilizing ${studentInfo.skills}. My project portfolio includes hands-on development, systematic problem-solving, and collaborative agile workflows. I am highly interested in helping ${company} optimize and secure its digital footprint.

I am confident that my technical foundation and enthusiasm for technology will make me a valuable addition to your team. I am eager to apply my knowledge in a real-world setting under the guidance of your experienced professionals.

Thank you for considering my application. I can be reached at ${studentInfo.phone} or via email at ${studentInfo.email} to discuss how I can support your team.`,

                finance: (degree, company, role) => `I am writing to apply for the ${role} position at ${company}. Currently pursuing my ${degree} at ${studentInfo.university}, I have developed a strong foundation in quantitative analysis, financial principles, and risk assessment.

With hands-on academic experience utilizing concepts and tools like ${studentInfo.skills}, I am highly interested in supporting the financial operations at ${company}. My coursework has heavily emphasized data accuracy, market trends, and analytical problem-solving.

I look forward to the opportunity to bring my meticulous attention to detail to your organization. Thank you for your time, and I can be contacted at ${studentInfo.phone} or ${studentInfo.email} for an interview.`,

                business: (degree, company, role) => `Please accept this letter as an expression of my strong interest in the ${role} position at ${company}. As a student of ${degree} at ${studentInfo.university}, I am driven by strategic planning, organizational efficiency, and market dynamics.

My academic projects have equipped me with strong analytical and communicative skills, specifically utilizing ${studentInfo.skills}. I am well-prepared to assist your team in driving informed business decisions and optimizing operational workflows.

I am very excited about the prospect of bringing my passion for business management to ${company}. Please feel free to contact me at ${studentInfo.phone} or ${studentInfo.email}.`,

                engineering: (degree, company, role) => `I am writing to express my strong interest in the ${role} position at ${company}. Currently pursuing my ${degree} at ${studentInfo.university}, I am deeply passionate about systems design, sustainable solutions, and practical engineering methodologies.

Through my rigorous coursework, I have developed competencies in ${studentInfo.skills}. I am adept at utilizing mathematical and scientific principles to troubleshoot complex issues and design efficient workflows. I am eager to contribute to the innovative projects at ${company}.

Thank you for reviewing my application. I welcome the opportunity to discuss my qualifications further and can be reached at ${studentInfo.phone} or ${studentInfo.email}.`,

                creative: (degree, company, role) => `I am excited to submit my application for the ${role} position at ${company}. As a creative professional pursuing my ${degree} at ${studentInfo.university}, I am dedicated to producing engaging, user-centric designs and multimedia experiences.

My academic portfolio showcases my proficiency in conceptualizing and executing projects using ${studentInfo.skills}. I am passionate about visual communication and am eager to bring my creative perspective and technical abilities to the dynamic team at ${company}.

I would welcome the opportunity to discuss how my design background aligns with your upcoming projects. I can be contacted at ${studentInfo.phone} or ${studentInfo.email}.`
            };

            // Update Functions
            const updatePreview = () => {
                previews.company.innerText = inputs.company.value || '[Company Name]';

                const managerName = inputs.manager.value || '[Hiring Manager Name]';
                previews.managerTop.innerText = managerName;
                previews.managerSalutation.innerText = managerName;

                if (inputs.body.value.trim() !== '') {
                    previews.body.className = 'mb-4 text-slate-800 whitespace-pre-wrap';
                    previews.body.innerText = inputs.body.value;
                } else {
                    previews.body.className = 'mb-4 text-slate-400 italic whitespace-pre-wrap';
                    previews.body.innerText = 'Your cover letter content will appear here as you type. This is a live preview of how your final PDF will look.';
                }
            };

            let saveTimer;
            const saveDraft = async () => {
                const response = await fetch(@json(route('student.cover-letter.store')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    body: JSON.stringify({
                        company_name: inputs.company.value,
                        hiring_manager: inputs.manager.value,
                        role: inputs.role.value,
                        body_text: inputs.body.value
                    })
                });

                if (!response.ok) {
                    throw new Error('Unable to save the cover letter draft.');
                }
            };

            const scheduleSave = () => {
                clearTimeout(saveTimer);
                saveTimer = setTimeout(() => saveDraft().catch(console.error), 500);
            };

            Object.values(inputs).forEach((input) => {
                input.addEventListener('input', () => {
                    updatePreview();
                    scheduleSave();
                });
            });

            // Handle Template Injection
            const templateSelector = document.getElementById('template-selector');
            templateSelector.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const domain = selectedOption.getAttribute('data-domain');
                const degreeName = selectedOption.value;

                if (domain && domainTemplates[domain]) {
                    const companyName = inputs.company.value || '[Company Name]';
                    const roleName = inputs.role.value || '[Internship Role]';

                    // Inject template
                    inputs.body.value = domainTemplates[domain](degreeName, companyName, roleName);
                } else {
                    inputs.body.value = '';
                }

                updatePreview();
                scheduleSave();
            });

            updatePreview();
        });
    </script>
</x-app-layout>
