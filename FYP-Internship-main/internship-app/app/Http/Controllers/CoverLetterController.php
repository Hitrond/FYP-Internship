<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Ensure you have installed: composer require barryvdh/laravel-dompdf
use Illuminate\Support\Facades\Auth;

class CoverLetterController extends Controller
{
    // 1. Load the Builder Interface
    public function create()
    {
        $user = Auth::user();
        // Load existing draft if they have one
        $draft = CoverLetter::where('user_id', $user->id)->first(); 
        
        return view('student.documents.cover-letter', compact('user', 'draft'));
    }

    // 2. Auto-Save the Draft (via AJAX)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'hiring_manager' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'body_text' => 'nullable|string',
        ]);

        CoverLetter::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json(['status' => 'success']);
    }

    // 3. Generate and Download the PDF
    public function download()
    {
        $user = Auth::user();
        $coverLetter = CoverLetter::where('user_id', $user->id)->first();
        
        if (!$coverLetter) {
            return back()->with('error', 'Please write a cover letter first.');
        }

        $data = [
            'user' => $user,
            'profile' => $user->profile,
            'coverLetter' => $coverLetter,
            'date' => now()->format('F j, Y')
        ];

        // Compiles the hidden PDF blade file
        $pdf = Pdf::loadView('student.documents.pdf.cover-letter-pdf', $data);
        
        // Formats the file name beautifully
        $fileName = str_replace(' ', '_', $user->name) . '_Cover_Letter.pdf';
        
        return $pdf->download($fileName);
    }
}