<?php

namespace App\Http\Controllers;

use App\Models\PlacementClearance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $pendingClearances = null;
        $latestClearance = null;

        if ($user->isMentor()) {
            $pendingClearances = PlacementClearance::where('status', 'pending')->count();
        }

        if ($user->isStudent()) {
            $latestClearance = PlacementClearance::where('student_id', $user->id)
                ->latest()
                ->first();
        }

        return view('dashboard', compact('pendingClearances', 'latestClearance'));
    }
}
