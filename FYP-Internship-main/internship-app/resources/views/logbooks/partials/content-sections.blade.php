<div class="grid gap-6 lg:grid-cols-2">
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-6">
            <h3 class="font-bold text-slate-900">Weekly objectives</h3>
        </div>
        <div class="whitespace-pre-wrap p-6 text-sm leading-relaxed text-slate-700">{{ $logbook->objectivesText() ?: 'No weekly objectives recorded.' }}</div>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-6">
            <h3 class="font-bold text-slate-900">Content, activities and skills applied</h3>
        </div>
        <div class="whitespace-pre-wrap p-6 text-sm leading-relaxed text-slate-700">{{ $logbook->contentSkillsText() ?: 'No separate content and skills details recorded.' }}</div>
    </section>
</div>
