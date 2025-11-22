<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Therapy;

class TherapyController extends Controller
{
    /**
     * Display a therapy by slug or the first published therapy when slug is null.
     */
    public function show(Request $request, $slug = null)
    {
        // eager load related models used by the view to avoid N+1 queries
        $with = ['pages', 'therapist', 'author', 'assignedPatient'];

        if ($slug) {
            // try to load by slug regardless of published status so admins can preview
            $therapy = Therapy::with($with)->where('slug', $slug)->firstOrFail();

            // if not published, only allow viewing for authenticated admins or therapists
            if (!$therapy->published) {
                $user = $request->user();
                if (! $user || ! in_array($user->role ?? '', ['admin','therapist'])) {
                    abort(404);
                }
            }
        } else {
            // public index: show first published therapy
            $therapy = Therapy::with($with)->where('published', true)->firstOrFail();
        }

        return view('therapy', compact('therapy'));
    }
}
