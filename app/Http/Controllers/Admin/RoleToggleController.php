<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleToggleController extends Controller
{
    /**
     * Toggle between admin and therapist mode for admin users.
     */
    public function toggle(Request $request)
    {
        $user = $request->user();
        
        // Only admins can toggle
        if (!$user->isAdmin()) {
            return redirect()->back()->with('error', 'No tienes permiso para cambiar de modo.');
        }
        
        // Toggle between admin and therapist
        $currentActive = session('active_role', 'admin');
        $newRole = ($currentActive === 'admin') ? 'therapist' : 'admin';
        
        session(['active_role' => $newRole]);
        
        $message = $newRole === 'therapist' 
            ? 'Cambiado a modo Terapeuta. Solo verás tus terapias y pacientes asignados.' 
            : 'Cambiado a modo Admin. Tienes acceso completo al sistema.';
        
        return redirect()->back()->with('success', $message);
    }
}
