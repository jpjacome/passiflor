<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TherapyPage extends Model
{
    use HasFactory;

    protected $table = 'therapy_pages';

    protected $fillable = [
        'therapy_id',
        'position',
        'type',
        'number',
        'title',
        'subtitle',
        'body',
        'list_items',
        'note',
        'image',
    ];

    protected $casts = [
        'list_items' => 'array',
    ];

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }
}
