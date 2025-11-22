<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\ConsultationConfirmation;
use App\Mail\NewConsultationNotification;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ConsultationController extends Controller
{
    /**
     * Store a new consultation request.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'sessionType' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
            'consent' => 'required|in:1,true',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find or create a guest user by email
            $user = User::where('email', $request->email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $request->fullName,
                    'email' => $request->email,
                    'role' => 'guest',
                    'password' => Hash::make(Str::random(40)),
                ]);
            } else {
                // If the existing user doesn't have a name, set it from the booking
                if (empty($user->name) && ! empty($request->fullName)) {
                    $user->update(['name' => $request->fullName]);
                }
            }

            // Create the consultation and link to the user
            $consultation = Consultation::create([
                'user_id' => $user->id,
                'full_name' => $request->fullName,
                'email' => $request->email,
                'phone' => $request->phone,
                'session_type' => $request->sessionType,
                'message' => $request->message,
                'status' => 'pending',
            ]);

            // Send confirmation email to user
            Mail::to($consultation->email)->send(new ConsultationConfirmation($consultation));

            // Send notification email to admin
            $adminEmail = env('ADMIN_EMAIL', 'info@passiflor.org');
            Mail::to($adminEmail)->send(new NewConsultationNotification($consultation));

            return response()->json([
                'success' => true,
                'message' => '¡Gracias! Hemos recibido tu solicitud. Te contactaremos pronto.',
                'consultation' => $consultation
            ], 201);

        } catch (\Exception $e) {
            // Log the full error
            \Log::error('Consultation form error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar tu solicitud. Por favor, inténtalo de nuevo.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display a listing of consultations (Admin/Therapist only).
     */
    public function index()
    {
        $this->authorize('viewAny', Consultation::class);
        
        $consultations = Consultation::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.consultations.index', compact('consultations'));
    }

    /**
     * Display a specific consultation (Admin/Therapist only).
     */
    public function show(Consultation $consultation)
    {
        $this->authorize('view', $consultation);
        
        return view('admin.consultations.show', compact('consultation'));
    }

    /**
     * Update consultation status (Admin/Therapist only).
     */
    public function updateStatus(Request $request, Consultation $consultation)
    {
        $this->authorize('update', $consultation);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $consultation->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'consultation' => $consultation
        ]);
    }

    /**
     * Delete a consultation (Admin only).
     */
    public function destroy(Consultation $consultation)
    {
        $this->authorize('delete', $consultation);
        
        $consultation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Consulta eliminada correctamente.'
        ]);
    }
}
