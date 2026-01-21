<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class CalendarController extends Controller
{
    /**
     * Display the calendar view.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get therapists and patients for dropdowns
        if ($user->actingAsAdmin()) {
            $therapists = User::whereIn('role', ['admin', 'therapist'])->orderBy('name')->get();
            $patients = User::where('role', 'patient')->orderBy('name')->get();
        } else {
            $therapists = User::where('id', $user->id)->get();
            $patients = User::where('role', 'patient')
                ->where('therapist_id', $user->id)
                ->orderBy('name')->get();
        }
        
        return view('admin.calendar', compact('therapists', 'patients'));
    }

    /**
     * Get appointments as JSON for FullCalendar.
     */
    public function getAppointments(Request $request)
    {
        $user = auth()->user();
        $query = Appointment::with(['therapist', 'patient']);
        
        // Filter by therapist if in therapist mode
        if ($user->actingAsTherapist()) {
            $query->where('therapist_id', $user->id);
        }
        
        // Filter by date range (FullCalendar sends start/end params)
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('start_time', [$request->start, $request->end]);
        }
        
        $appointments = $query->get()->map(function ($apt) {
            return [
                'id' => $apt->id,
                'title' => $apt->title,
                'start' => $apt->start_time->toIso8601String(),
                'end' => $apt->end_time->toIso8601String(),
                'backgroundColor' => $apt->color,
                'borderColor' => $apt->color,
                'extendedProps' => [
                    'therapist' => $apt->therapist->name ?? '',
                    'patient' => $apt->patient->name ?? '',
                    'type' => $apt->type,
                    'status' => $apt->status,
                    'notes' => $apt->notes,
                ],
            ];
        });
        
        return response()->json($appointments);
    }

    /**
     * Store a new appointment.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'therapist_id' => 'required|exists:users,id',
            'patient_id' => 'nullable|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'title' => 'required|string|max:255',
            'type' => 'required|in:session,consultation,evaluation,follow-up',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled,no-show',
            'notes' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);
        
        // Calculate duration in minutes
        $start = new \DateTime($validated['start_time']);
        $end = new \DateTime($validated['end_time']);
        $validated['duration'] = ($end->getTimestamp() - $start->getTimestamp()) / 60;
        
        // If therapist mode, force therapist_id to current user
        if ($user->actingAsTherapist()) {
            $validated['therapist_id'] = $user->id;
        }
        
        $appointment = Appointment::create($validated);
        
        return response()->json([
            'success' => true,
            'appointment' => $appointment->load(['therapist', 'patient']),
        ]);
    }

    /**
     * Update an appointment.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $user = auth()->user();
        
        // Check permissions
        if ($user->actingAsTherapist() && $appointment->therapist_id !== $user->id) {
            abort(403, 'No tienes permiso para editar esta cita.');
        }
        
        $validated = $request->validate([
            'therapist_id' => 'required|exists:users,id',
            'patient_id' => 'nullable|exists:users,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'title' => 'required|string|max:255',
            'type' => 'required|in:session,consultation,evaluation,follow-up',
            'status' => 'required|in:scheduled,confirmed,completed,cancelled,no-show',
            'notes' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);
        
        // Calculate duration
        $start = new \DateTime($validated['start_time']);
        $end = new \DateTime($validated['end_time']);
        $validated['duration'] = ($end->getTimestamp() - $start->getTimestamp()) / 60;
        
        // If therapist mode, force therapist_id to current user
        if ($user->actingAsTherapist()) {
            $validated['therapist_id'] = $user->id;
        }
        
        $appointment->update($validated);
        
        return response()->json([
            'success' => true,
            'appointment' => $appointment->load(['therapist', 'patient']),
        ]);
    }

    /**
     * Delete an appointment.
     */
    public function destroy(Appointment $appointment)
    {
        $user = auth()->user();
        
        // Check permissions
        if ($user->actingAsTherapist() && $appointment->therapist_id !== $user->id) {
            abort(403, 'No tienes permiso para eliminar esta cita.');
        }
        
        $appointment->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Generate .ics calendar feed for a therapist.
     */
    public function feed($token)
    {
        $user = User::where('calendar_token', $token)->firstOrFail();
        
        $appointments = Appointment::where('therapist_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->get();
        
        $calendar = Calendar::create('Passiflor - ' . $user->name)
            ->productIdentifier('Passiflor Therapy Calendar');
        
        foreach ($appointments as $apt) {
            $event = Event::create()
                ->name($apt->title)
                ->description($apt->notes ?? '')
                ->startsAt($apt->start_time)
                ->endsAt($apt->end_time);
            
            if ($apt->patient) {
                $event->description($event->description . "\nPaciente: " . $apt->patient->name);
            }
            
            $calendar->event($event);
        }
        
        return response($calendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="passiflor-calendar.ics"');
    }
}
