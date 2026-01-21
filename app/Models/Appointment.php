<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'therapist_id',
        'patient_id',
        'start_time',
        'end_time',
        'duration',
        'title',
        'type',
        'status',
        'notes',
        'color',
        'consultation_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'integer',
    ];

    /**
     * Get the therapist for this appointment.
     */
    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    /**
     * Get the patient for this appointment.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Get the consultation linked to this appointment.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Scope appointments to a specific therapist.
     */
    public function scopeForTherapist($query, $therapistId)
    {
        return $query->where('therapist_id', $therapistId);
    }

    /**
     * Scope appointments by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
