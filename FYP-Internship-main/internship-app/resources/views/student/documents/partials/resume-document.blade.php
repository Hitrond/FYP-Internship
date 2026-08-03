@php
    $resume = $resume ?? app(\App\Services\StudentResumeDataService::class)->for($user);
    $preview = $preview ?? false;
    $template = in_array($template ?? null, ['classic', 'prime-ats', 'traditional'], true)
        ? $template
        : 'prime-ats';
@endphp

<style>
    .ats-resume {
        box-sizing: border-box;
        width: 100%;
        margin: 0 auto;
        color: #1f2937;
        background: #ffffff;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10pt;
        line-height: 1.35;
    }
    .ats-resume.preview {
        max-width: 210mm;
        min-height: 297mm;
        padding: 14mm 16mm;
    }
    .ats-resume * { box-sizing: border-box; }
    .ats-resume h1,
    .ats-resume h2,
    .ats-resume p,
    .ats-resume ul { margin-top: 0; }
    .ats-resume .header { text-align: center; margin-bottom: 14px; }
    .ats-resume h1 { margin-bottom: 2px; color: #1d4ed8; font-size: 22pt; line-height: 1.1; }
    .ats-resume .role { margin-bottom: 4px; color: #334155; font-size: 10pt; font-weight: bold; }
    .ats-resume .contact { margin-bottom: 0; color: #475569; font-size: 8.5pt; overflow-wrap: anywhere; }
    .ats-resume .separator { color: #94a3b8; }
    .ats-resume .section { margin-top: 12px; }
    .ats-resume .section-title {
        margin-bottom: 6px;
        border-bottom: 1px solid #cbd5e1;
        color: #1d4ed8;
        font-size: 11pt;
        font-weight: bold;
        letter-spacing: .5px;
    }
    .ats-resume .summary { margin-bottom: 0; }
    .ats-resume .plain-list { margin: 0; padding-left: 18px; }
    .ats-resume .plain-list li { margin-bottom: 4px; }
    .ats-resume .entry { margin-bottom: 8px; }
    .ats-resume .entry-heading { margin-bottom: 1px; font-weight: bold; }
    .ats-resume .institution { margin-bottom: 3px; color: #334155; font-size: 9.5pt; font-style: italic; }
    .ats-resume .inline-content { margin-bottom: 0; }

    .ats-resume.template-classic {
        color: #111827;
        font-family: "Times New Roman", Times, serif;
        font-size: 10.5pt;
        line-height: 1.35;
    }
    .ats-resume.preview.template-classic { padding: 15mm 17mm; }
    .ats-resume.template-classic .header { text-align: left; margin-bottom: 13px; }
    .ats-resume.template-classic h1 { color: #111827; font-size: 23pt; }
    .ats-resume.template-classic .role { font-size: 10.5pt; }
    .ats-resume.template-classic .contact { font-size: 9pt; }
    .ats-resume.template-classic .section-title {
        border-bottom-color: #111827;
        color: #111827;
        font-size: 11pt;
        letter-spacing: .3px;
    }

    .ats-resume.template-traditional {
        color: #0f172a;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9.5pt;
        line-height: 1.25;
    }
    .ats-resume.preview.template-traditional { padding: 12mm 14.5mm; }
    .ats-resume.template-traditional .header {
        margin-bottom: 9px;
        padding-bottom: 6px;
        border-bottom: 1px solid #64748b;
        text-align: left;
    }
    .ats-resume.template-traditional h1 {
        margin-bottom: 1px;
        color: #0f172a;
        font-size: 20pt;
        text-transform: uppercase;
    }
    .ats-resume.template-traditional .role { margin-bottom: 2px; font-size: 9.5pt; }
    .ats-resume.template-traditional .contact { font-size: 8pt; }
    .ats-resume.template-traditional .section { margin-top: 8px; }
    .ats-resume.template-traditional .section-title {
        margin-bottom: 4px;
        border-bottom-color: #64748b;
        color: #0f172a;
        font-size: 10pt;
        letter-spacing: .7px;
    }
    .ats-resume.template-traditional .plain-list li { margin-bottom: 2px; }
    .ats-resume.template-traditional .entry { margin-bottom: 5px; }
    .ats-resume.template-traditional .institution { margin-bottom: 2px; font-size: 9pt; }
    .ats-resume.template-traditional .two-column-body { width: 100%; }
    .ats-resume.template-traditional .column-left {
        float: left;
        width: 34%;
        padding-right: 16px;
        border-right: 1px solid #cbd5e1;
    }
    .ats-resume.template-traditional .column-right {
        float: right;
        width: 63%;
    }
    .ats-resume.template-traditional .column-left > .section:first-child,
    .ats-resume.template-traditional .column-right > .section:first-child { margin-top: 0; }
    .ats-resume.template-traditional .column-clear { clear: both; }
</style>

<article class="ats-resume template-{{ $template }} {{ $preview ? 'preview' : '' }}" data-resume-template="{{ $template }}" data-layout="{{ $template === 'traditional' ? 'two-column' : 'single-column' }}">
    <header class="header">
        <h1>{{ $resume['name'] }}</h1>
        <p class="role">{{ $resume['title'] }}</p>
        <p class="contact">
            @foreach ($resume['contact'] as $item)
                <span>{{ $item }}</span>@unless($loop->last)<span class="separator"> | </span>@endunless
            @endforeach
        </p>
    </header>

    @if ($template === 'traditional')
        <div class="two-column-body">
            <div class="column-left">
                <section class="section">
                    <h2 class="section-title">PROFESSIONAL SUMMARY</h2>
                    <p class="summary">{{ $resume['summary'] }}</p>
                </section>

                <section class="section">
                    <h2 class="section-title">SKILLS</h2>
                    <p class="inline-content">{{ implode(', ', $resume['skills']) }}</p>
                </section>

                @if ($resume['languages'] !== [])
                    <section class="section">
                        <h2 class="section-title">LANGUAGES</h2>
                        <p class="inline-content">{{ implode(', ', $resume['languages']) }}</p>
                    </section>
                @endif

                @if ($resume['references'] !== [])
                    <section class="section">
                        <h2 class="section-title">REFERENCES</h2>
                        @foreach ($resume['references'] as $reference)
                            <p class="inline-content">{{ $reference }}</p>
                        @endforeach
                    </section>
                @endif
            </div>

            <div class="column-right">
                @if ($resume['projects'] !== [])
                    <section class="section">
                        <h2 class="section-title">PROJECTS</h2>
                        <ul class="plain-list">
                            @foreach ($resume['projects'] as $project)
                                <li>{{ $project }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="section">
                    <h2 class="section-title">EDUCATION</h2>
                    @foreach ($resume['education'] as $education)
                        <div class="entry">
                            <p class="entry-heading">
                                {{ $education['qualification'] }}@if($education['dates']) | {{ $education['dates'] }}@endif
                            </p>
                            <p class="institution">{{ $education['institution'] }}</p>
                            @if ($education['details'] !== [])
                                <ul class="plain-list">
                                    @foreach ($education['details'] as $detail)
                                        <li>{{ $detail }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </section>
            </div>
            <div class="column-clear"></div>
        </div>
    @else
        <section class="section">
            <h2 class="section-title">PROFESSIONAL SUMMARY</h2>
            <p class="summary">{{ $resume['summary'] }}</p>
        </section>

        @if ($resume['projects'] !== [])
            <section class="section">
                <h2 class="section-title">PROJECTS</h2>
                <ul class="plain-list">
                    @foreach ($resume['projects'] as $project)
                        <li>{{ $project }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="section">
            <h2 class="section-title">EDUCATION</h2>
            @foreach ($resume['education'] as $education)
                <div class="entry">
                    <p class="entry-heading">
                        {{ $education['qualification'] }}@if($education['dates']) | {{ $education['dates'] }}@endif
                    </p>
                    <p class="institution">{{ $education['institution'] }}</p>
                    @if ($education['details'] !== [])
                        <ul class="plain-list">
                            @foreach ($education['details'] as $detail)
                                <li>{{ $detail }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </section>

        <section class="section">
            <h2 class="section-title">SKILLS</h2>
            <p class="inline-content">{{ implode(', ', $resume['skills']) }}</p>
        </section>

        @if ($resume['languages'] !== [])
            <section class="section">
                <h2 class="section-title">LANGUAGES</h2>
                <p class="inline-content">{{ implode(', ', $resume['languages']) }}</p>
            </section>
        @endif

        @if ($resume['references'] !== [])
            <section class="section">
                <h2 class="section-title">REFERENCES</h2>
                @foreach ($resume['references'] as $reference)
                    <p class="inline-content">{{ $reference }}</p>
                @endforeach
            </section>
        @endif
    @endif
</article>
