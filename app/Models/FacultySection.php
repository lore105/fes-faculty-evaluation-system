<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacultySection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'section_id',
        'semester_id',
        'status',
    ];

    public function faculty()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
