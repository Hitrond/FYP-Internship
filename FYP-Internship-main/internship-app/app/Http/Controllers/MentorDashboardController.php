<?php

namespace App\Http\Controllers;

use App\Models\PlacementClearance;
use Illuminate\Http\Request;

class MentorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $pendingClearances = PlacementClearance::where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $pendingCount = PlacementClearance::where('status', 'pending')->count();

        return view('mentor.dashboard', compact('pendingClearances', 'pendingCount'));
    }
}
