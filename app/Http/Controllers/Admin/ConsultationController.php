<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Display a listing of consultations grouped by status.
     */
    public function index()
    {
        // Get counts for each status
        $counts = [
            'total' => Consultation::count(),
            'pending' => Consultation::where('status', 'pending')->count(),
            'confirmed' => Consultation::where('status', 'confirmed')->count(),
            'completed' => Consultation::where('status', 'completed')->count(),
            'cancelled' => Consultation::where('status', 'cancelled')->count(),
        ];

        // Get consultations grouped by status with pagination
        $consultations = [
            'pending' => Consultation::where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->paginate(12, ['*'], 'pending_page'),
            'confirmed' => Consultation::where('status', 'confirmed')
                ->orderBy('created_at', 'desc')
                ->paginate(12, ['*'], 'confirmed_page'),
            'completed' => Consultation::where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->paginate(12, ['*'], 'completed_page'),
            'cancelled' => Consultation::where('status', 'cancelled')
                ->orderBy('created_at', 'desc')
                ->paginate(12, ['*'], 'cancelled_page'),
        ];

        return view('admin.consultations', compact('consultations', 'counts'));
    }

    /**
     * Update the status of a consultation.
     */
    public function updateStatus(Request $request, Consultation $consultation)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $consultation->update([
            'status' => $request->status,
        ]);

        return redirect()->route('admin.consultations.index')
            ->with('success', 'Estado de la consulta actualizado exitosamente.');
    }

    /**
     * Remove the specified consultation from storage.
     */
    public function destroy(Consultation $consultation)
    {
        $consultation->delete();

        return redirect()->route('admin.consultations.index')
            ->with('success', 'Consulta eliminada exitosamente.');
    }
}
