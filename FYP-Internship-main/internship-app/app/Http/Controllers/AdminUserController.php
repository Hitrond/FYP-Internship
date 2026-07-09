<?php

namespace App\Http\Controllers;

use App\Models\InternshipCycle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $users = User::with(['mentor', 'supervisor', 'profile'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($profile) => $profile->where('tp_number', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($request->input('role'), ['student', 'mentor', 'supervisor', 'admin'], true), fn ($query) => $query->where('role', $request->input('role')))
            ->when($request->input('mentor') === 'unassigned', fn ($query) => $query->where('role', 'student')->whereNull('mentor_id'))
            ->when(is_numeric($request->input('mentor')), fn ($query) => $query->where('role', 'student')->where('mentor_id', (int) $request->input('mentor')))
            ->when($request->input('supervisor') === 'unassigned', fn ($query) => $query->where('role', 'student')->whereNull('supervisor_id'))
            ->when($request->input('supervisor') === 'assigned', fn ($query) => $query->where('role', 'student')->whereNotNull('supervisor_id'))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();
        $mentors = User::where('role', 'mentor')->orderBy('name')->get();
        $supervisors = User::where('role', 'supervisor')->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'mentors', 'supervisors'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:student,mentor,supervisor,admin'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'role' => ['required', 'string', 'in:student,mentor,supervisor,admin'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($validated['role'] !== 'student') {
            $validated['mentor_id'] = null;
            $validated['supervisor_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Assign a mentor to a student.
     */
    public function assignMentor(Request $request, User $user)
    {
        abort_unless($user->isStudent(), 422, 'Academic Mentors can only be assigned to students.');

        $validated = $request->validate([
            'mentor_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'mentor')),
            ],
        ]);

        $user->update([
            'mentor_id' => $validated['mentor_id'] ?? null,
        ]);

        $activeCycle = InternshipCycle::active();
        if ($activeCycle) {
            $activeCycle->assignments()
                ->where('student_id', $user->id)
                ->update(['mentor_id' => $validated['mentor_id'] ?? null]);
        }

        $message = isset($validated['mentor_id'])
            ? 'Academic Mentor successfully assigned to '.$user->name.'.'
            : 'Academic Mentor assignment removed from '.$user->name.'.';

        return redirect()->route('admin.users.index')->with('success', $message);
    }
}
