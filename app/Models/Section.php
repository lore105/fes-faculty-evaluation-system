<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id',
        'name',
        'code',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function studentEnrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function studentSections()
    {
        return $this->hasMany(StudentSection::class);
    }

    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class);
    }

    public function facultySections()
    {
        return $this->hasMany(FacultySection::class);
    }
}
