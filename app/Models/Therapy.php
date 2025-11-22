<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Therapy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'short_description',
        'cover_image',
        'duration_minutes',
        'age_from',
        'age_to',
        'assigned_patient_id',
        'author_id',
        'therapist_id',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function pages()
    {
        return $this->hasMany(TherapyPage::class)->orderBy('position');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    public function assignedPatient()
    {
        return $this->belongsTo(User::class, 'assigned_patient_id');
    }
}
