<?php

namespace App\Http\Controllers;

use App\Models\EvaluationForm;
use App\Models\InternshipCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminEvaluationFormController extends Controller
{
    public function index()
    {
        $forms = EvaluationForm::with('cycle')
            ->latest()
            ->get();
        $cycles = InternshipCycle::latest('placement_window_start')->get();

        return view('admin.evaluation-forms.index', compact('forms', 'cycles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'internship_cycle_id' => ['nullable', 'exists:internship_cycles,id'],
            'type' => ['required', Rule::in([EvaluationForm::TYPE_MIDTERM, EvaluationForm::TYPE_FINAL])],
            'title' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:50'],
            'criteria_text' => ['required', 'string'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'form_file' => ['nullable', 'file', 'extensions:pdf,doc,docx,xlsx,xls', 'max:102400'],
            'activate' => ['nullable', 'boolean'],
        ]);

        $criteria = collect(preg_split('/\R+/', $validated['criteria_text']))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->mapWithKeys(fn ($label, $index) => [
                'criterion_'.($index + 1) => $label,
            ])
            ->all();

        $path = $request->hasFile('form_file')
            ? $request->file('form_file')->store('evaluation-forms', 'local')
            : null;

        $form = DB::transaction(function () use ($validated, $criteria, $path, $request): EvaluationForm {
            $form = EvaluationForm::create([
                'internship_cycle_id' => $validated['internship_cycle_id'] ?? null,
                'type' => $validated['type'],
                'title' => $validated['title'],
                'version' => $validated['version'] ?? null,
                'criteria' => $criteria,
                'instructions' => $validated['instructions'] ?? null,
                'uploaded_file_path' => $path,
                'uploaded_file_name' => $request->file('form_file')?->getClientOriginalName(),
                'is_active' => (bool) ($validated['activate'] ?? false),
            ]);

            if ($form->is_active) {
                $this->deactivateCompetingForms($form);
            }

            return $form;
        });

        return back()->with(
            'success',
            $form->title.' uploaded'.($form->is_active ? ' and activated.' : '.')
        );
    }

    public function activate(EvaluationForm $evaluationForm)
    {
        DB::transaction(function () use ($evaluationForm): void {
            $evaluationForm->update(['is_active' => true]);
            $this->deactivateCompetingForms($evaluationForm);
        });

        return back()->with('success', $evaluationForm->title.' is now the active evaluation form.');
    }

    public function download(EvaluationForm $evaluationForm)
    {
        abort_unless($evaluationForm->uploaded_file_path, 404);

        return Storage::disk('local')->download(
            $evaluationForm->uploaded_file_path,
            $evaluationForm->uploaded_file_name
        );
    }

    private function deactivateCompetingForms(EvaluationForm $form): void
    {
        EvaluationForm::query()
            ->whereKeyNot($form->id)
            ->where('type', $form->type)
            ->where('internship_cycle_id', $form->internship_cycle_id)
            ->update(['is_active' => false]);
    }
}
