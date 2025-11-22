<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users organized by role with pagination.
     */
    public function index()
    {
        $perPage = 12; // Users per page per section

        $users = [
            'admins' => User::where('role', 'admin')->orderBy('name')->paginate($perPage, ['*'], 'admins'),
            'therapists' => User::where('role', 'therapist')->orderBy('name')->paginate($perPage, ['*'], 'therapists'),
            'patients' => User::where('role', 'patient')->with('therapist')->orderBy('name')->paginate($perPage, ['*'], 'patients'),
            'guests' => User::where('role', 'guest')->orderBy('name')->paginate($perPage, ['*'], 'guests'),
        ];

        $counts = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'therapists' => User::where('role', 'therapist')->count(),
            'patients' => User::where('role', 'patient')->count(),
            'guests' => User::where('role', 'guest')->count(),
        ];

        return view('admin.users', compact('users', 'counts'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $therapists = User::where('role', 'therapist')->orderBy('name')->get();
        return view('admin.users-create', compact('therapists'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,therapist,patient,guest'],
            'phone' => ['nullable', 'string', 'max:20'],
            // therapist must be provided when creating a patient
            'therapist_id' => ['required_if:role,patient', 'nullable', 'exists:users,id'],
        ]);

        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('admin.users-show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $therapists = User::where('role', 'therapist')->orderBy('name')->get();
        return view('admin.users-edit', compact('user', 'therapists'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,therapist,patient,guest'],
            'phone' => ['nullable', 'string', 'max:20'],
            // therapist required when role is patient
            'therapist_id' => ['required_if:role,patient', 'nullable', 'exists:users,id'],
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
            $validated['password'] = bcrypt($request->password);
        }

        $user->update($validated);

        return redirect()->route('admin.users')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Usuario eliminado exitosamente.');
    }
}
