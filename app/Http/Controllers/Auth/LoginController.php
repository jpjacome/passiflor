<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect based on user role
            if ($user->isAdmin() || $user->isTherapist()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('admin.dashboard'),
                    'message' => '¡Bienvenido, ' . $user->name . '!'
                ]);
            }

            // Regular users go to home
            return response()->json([
                'success' => true,
                'redirect' => url('/'),
                'message' => '¡Bienvenido, ' . $user->name . '!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Las credenciales proporcionadas no coinciden con nuestros registros.'
        ], 422);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
