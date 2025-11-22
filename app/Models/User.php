<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'therapist_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the consultations for the user.
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * If this user is a patient, this returns their assigned therapist (User).
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    /**
     * If this user is a therapist, this returns their patients.
     */
    public function patients()
    {
        return $this->hasMany(User::class, 'therapist_id');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a therapist.
     */
    public function isTherapist(): bool
    {
        return $this->role === 'therapist';
    }

    /**
     * Check if user is a patient.
     */
    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }

    /**
     * Check if user is a guest.
     */
    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    /**
     * Check if user has admin or therapist privileges.
     */
    public function canManageConsultations(): bool
    {
        return $this->isAdmin() || $this->isTherapist();
    }
}
